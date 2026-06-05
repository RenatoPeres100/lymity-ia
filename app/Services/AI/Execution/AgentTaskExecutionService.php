<?php

namespace App\Services\AI\Execution;

use App\Models\AgentTask;
use App\Models\AgentTaskRun;
use App\Models\AiEmployee;
use App\Models\AiExecutionContext;
use App\Models\AiUsageRecord;
use App\Models\BlogPost;
use App\Models\GeneratedContentPackage;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\AI\AICostGuardService;
use App\Services\AI\AIProviderManager;
use App\Services\AI\Context\AgentTaskContextService;
use App\Services\AI\Context\BrandContextSnapshotService;
use App\Services\AI\Images\ContentPackageImageGenerationService;
use App\Services\AI\Memory\AIMemoryService;
use App\Services\AI\Prompt\StructuredPromptBuilderService;
use App\Services\Approval\ApprovalService;
use App\Services\Logs\ActivityLogService;
use App\Services\Research\DisabledResearchProvider;
use App\Services\Research\ExternalResearchProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class AgentTaskExecutionService
{
    public function __construct(
        private AIProviderManager                      $providerManager,
        private AICostGuardService                     $costGuard,
        private BrandContextSnapshotService            $brandContextService,
        private AgentTaskContextService                $taskContextService,
        private AIMemoryService                        $memoryService,
        private StructuredPromptBuilderService         $promptBuilder,
        private ContentPackageImageGenerationService   $imageService,
        private ApprovalService                        $approvalService,
        private ActivityLogService                     $activityLog,
    ) {}

    // ── Public API ─────────────────────────────────────────────────────────────

    public function runDueTasks(?Carbon $now = null, bool $dryRun = false): array
    {
        $now     = $now ?? now();
        $results = [];

        $tasks = AgentTask::active()
            ->where(function ($q) use ($now) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            })
            ->with('aiEmployee')
            ->get();

        foreach ($tasks as $task) {
            if ($task->frequency === 'manual') continue;
            if ($task->hasReachedDailyLimit()) {
                $results[] = ['task_id' => $task->id, 'status' => 'skipped_daily_limit'];
                continue;
            }

            if ($dryRun) {
                $results[] = ['task_id' => $task->id, 'title' => $task->title, 'status' => 'would_run'];
                continue;
            }

            try {
                $run = $this->runTaskNow($task);
                $results[] = [
                    'task_id' => $task->id,
                    'title'   => $task->title,
                    'run_id'  => $run->id,
                    'status'  => $run->status,
                ];
            } catch (Throwable $e) {
                $results[] = [
                    'task_id' => $task->id,
                    'title'   => $task->title,
                    'status'  => 'failed',
                    'error'   => $e->getMessage(),
                ];
            }

            // Recalculate next_run_at after execution
            $this->calculateAndSaveNextRun($task);
        }

        return $results;
    }

    public function runTaskNow(AgentTask $task, ?User $user = null): AgentTaskRun
    {
        $employee = $task->aiEmployee;

        // Guard: must have an active AI employee
        if (!$employee || $employee->status !== 'active') {
            throw new \RuntimeException("Tarefa '{$task->title}' não possui funcionário IA ativo vinculado.");
        }

        // Guard: cost/provider check
        $this->costGuard->assertCanGenerate($task->task_type, $employee);

        // Create run record
        $run = AgentTaskRun::create([
            'company_id'     => $task->company_id,
            'client_id'      => $task->client_id,
            'agent_task_id'  => $task->id,
            'ai_employee_id' => $employee->id,
            'status'         => 'queued',
            'started_at'     => now(),
        ]);

        $task->update(['last_run_at' => now()]);

        $this->activityLog->info('agent_task.run_started', "Execução iniciada: {$task->title}", [
            'agent_task_id' => $task->id,
            'run_id'        => $run->id,
            'module'        => 'ai_tasks',
        ]);

        try {
            // 1. Prepare context
            $run->update(['status' => 'preparing_context']);
            $context = $this->prepareExecutionContext($task, $employee);
            $run->update(['execution_context_id' => $context->id]);

            // 2. Research (if applicable)
            if ($task->requires_external_research) {
                $run->update(['status' => 'researching']);
                $research = $this->executeResearchIfEnabled($task, $context);
                if (!empty($research)) {
                    $snapshot = $context->external_research_snapshot ?? [];
                    $snapshot = array_merge($snapshot, $research);
                    $context->update(['external_research_snapshot' => $snapshot]);
                }
            }

            // 3. Generate text
            $run->update(['status' => 'generating_text']);
            $this->activityLog->info('ai.generation.started', "Geração de texto iniciada para '{$task->title}'", [
                'run_id' => $run->id, 'module' => 'ai_tasks',
            ]);

            $textPayload = $this->executeTextGeneration($run, $context);

            $run->update([
                'input_tokens'  => $textPayload['_meta']['input_tokens'] ?? null,
                'output_tokens' => $textPayload['_meta']['output_tokens'] ?? null,
            ]);

            $this->activityLog->info('ai.generation.completed', "Texto gerado com sucesso para '{$task->title}'", [
                'run_id' => $run->id, 'module' => 'ai_tasks',
            ]);

            // 4. Create entity (blog post, social post, etc.)
            $entity = $this->createGeneratedEntity($run, $task, $textPayload);

            // 5. Create content package
            $run->update(['status' => 'creating_approval']);
            $package = $this->createApprovalPackage($run, $task, $entity, $textPayload, $context);

            // 6. Generate images if required
            if ($task->requiresImage()) {
                $run->update(['status' => 'generating_images']);
                $this->executeImageGenerationIfNeeded($run, $task, $package, $textPayload, $context);
            }

            // 7. Create ApprovalRequest
            $approval = $this->createApprovalRequest($package, $task, $entity, $employee);

            // 8. Update entity with approval_request_id
            $run->update([
                'status'                 => 'waiting_approval',
                'finished_at'            => now(),
                'generated_entity_type'  => get_class($entity),
                'generated_entity_id'    => $entity->id,
                'approval_request_id'    => $approval->id,
            ]);

            $package->update([
                'approval_request_id'    => $approval->id,
                'generated_entity_type'  => get_class($entity),
                'generated_entity_id'    => $entity->id,
            ]);

            // 9. Record cost
            $this->recordUsage($run, $task, $context);

            // 10. Update task
            $task->update([
                'last_success_at' => now(),
                'failure_count'   => 0,
            ]);

            $this->activityLog->info('agent_task.run_completed', "Execução concluída: {$task->title}", [
                'run_id' => $run->id, 'package_id' => $package->id, 'module' => 'ai_tasks',
            ]);

        } catch (Throwable $e) {
            $this->markRunFailed($run, $e);
            $task->update([
                'last_failure_at' => now(),
                'failure_count'   => $task->failure_count + 1,
            ]);
            throw $e;
        }

        return $run->fresh();
    }

    public function prepareExecutionContext(AgentTask $task, AiEmployee $employee): AiExecutionContext
    {
        $brandContext = $this->brandContextService->getActiveBrandContextForScope(
            $task->company_id,
            $task->client_id
        );

        $compactBrand = $brandContext
            ? $this->brandContextService->getCompactContext($brandContext)
            : 'Contexto de marca não configurado. Use posicionamento profissional padrão.';

        $compactTask = $this->taskContextService->getCompactTaskContext($task);

        // Get relevant memories (max 5)
        $memories = $this->memoryService->getRelevantMemoriesForTask($task, $employee, 5);
        $memoriesData = $memories->map(fn($m) => [
            'id'      => $m->id,
            'type'    => $m->memory_type,
            'title'   => $m->title,
            'content' => $m->getEffectiveContent(),
        ])->toArray();

        $context = AiExecutionContext::create([
            'company_id'            => $task->company_id,
            'client_id'             => $task->client_id,
            'ai_employee_id'        => $employee->id,
            'agent_task_id'         => $task->id,
            'brand_context_id'      => $brandContext?->id,
            'brand_context_version' => $brandContext?->version,
            'brand_context_hash'    => $brandContext?->content_hash,
            'task_version'          => $task->version,
            'task_context_hash'     => $task->task_context_hash,
            'compact_brand_context' => $compactBrand,
            'compact_task_context'  => $compactTask,
            'selected_memories'     => $memoriesData,
            'model'                 => config('ai.gemini_text_model', 'gemini-2.5-flash'),
            'provider'              => config('ai.provider', 'google'),
            'status'                => 'prepared',
        ]);

        $this->activityLog->info('context.snapshot.created', "Snapshot de contexto criado para '{$task->title}'", [
            'context_id' => $context->id, 'memories_count' => count($memoriesData), 'module' => 'ai_tasks',
        ]);

        return $context;
    }

    public function executeTextGeneration(AgentTaskRun $run, AiExecutionContext $context): array
    {
        $task     = $run->agentTask;
        $employee = $run->aiEmployee;

        $prompt = $this->promptBuilder->buildForTaskExecution($task, $employee, $context);

        $previewHash = hash('sha256', $prompt);
        $preview     = $this->promptBuilder->sanitizePromptPreview($prompt);
        $context->update([
            'prompt_hash'    => $previewHash,
            'prompt_preview' => $preview,
            'status'         => 'running',
        ]);

        $provider = $this->providerManager->provider();
        $response = $provider->generateText([
            'system_prompt' => "Você é um assistente de conteúdo profissional. Responda APENAS com JSON válido.",
            'user_message'  => $prompt,
            'json_mode'     => true,
            'max_tokens'    => config('ai.max_output_tokens', 4096),
        ]);

        if (!$response->success) {
            $context->update(['status' => 'failed', 'error_message' => $response->error_message]);
            throw new \RuntimeException("Geração de texto falhou: " . $response->error_message);
        }

        $raw  = trim($response->text);
        $raw  = preg_replace('/^```json\s*/i', '', $raw);
        $raw  = preg_replace('/\s*```$/i', '', $raw);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $context->update(['status' => 'failed', 'error_message' => "Resposta não é JSON válido."]);
            throw new \RuntimeException("A IA retornou conteúdo inválido (não é JSON). Tente novamente.");
        }

        $context->update([
            'status'                  => 'completed',
            'input_tokens_estimated'  => $response->input_tokens ?? null,
            'output_tokens_estimated' => $response->output_tokens ?? null,
            'estimated_cost_usd'      => $response->estimated_cost ?? null,
        ]);

        $data['_meta'] = [
            'input_tokens'  => $response->input_tokens ?? null,
            'output_tokens' => $response->output_tokens ?? null,
            'cost'          => $response->estimated_cost ?? null,
            'model'         => $response->model ?? null,
        ];

        return $data;
    }

    public function executeImageGenerationIfNeeded(
        AgentTaskRun $run,
        AgentTask $task,
        GeneratedContentPackage $package,
        array $textPayload,
        AiExecutionContext $context
    ): void {
        $imagePrompt = $textPayload['image_prompt']
            ?? ($textPayload['slides'][0]['image_prompt'] ?? null);

        if (!$imagePrompt) {
            $imagePrompt = $this->promptBuilder->buildImagePrompt($task, $textPayload, $context);
        }

        if (!$this->imageService->canGenerateImages()) {
            $visual = $package->visual_payload ?? [];
            $visual['visual_status']  = 'pending_configuration';
            $visual['image_prompt']   = $imagePrompt;
            $visual['image_note']     = 'Imagem não gerada. IMAGE_GENERATION_ENABLED=false ou provider não configurado.';
            $package->update(['visual_payload' => $visual]);

            $this->activityLog->info('image.generation.skipped_configuration_missing',
                "Imagem não gerada para '{$task->title}' — IMAGE_GENERATION_ENABLED=false",
                ['run_id' => $run->id, 'module' => 'ai_tasks']
            );
            return;
        }

        $this->activityLog->info('image.generation.started', "Gerando imagem para '{$task->title}'", [
            'run_id' => $run->id, 'module' => 'ai_tasks',
        ]);

        try {
            if ($task->isBlogType()) {
                $asset = $this->imageService->generateFeaturedImage($package, $imagePrompt);
            } elseif ($task->isCarouselType()) {
                $slides = $textPayload['slides'] ?? [];
                $this->imageService->generateCarouselSlides($package, $slides);
                $asset = true;
            } else {
                $asset = $this->imageService->generateFeedImage($package, $imagePrompt);
            }

            if ($asset) {
                $this->activityLog->info('image.generation.completed', "Imagem gerada com sucesso para '{$task->title}'", [
                    'run_id' => $run->id, 'module' => 'ai_tasks',
                ]);
            } else {
                $this->activityLog->warning('image.generation.failed', "Falha ao gerar imagem para '{$task->title}'", [
                    'run_id' => $run->id, 'module' => 'ai_tasks',
                ]);
            }

        } catch (Throwable $e) {
            $this->activityLog->warning('image.generation.failed', "Exceção na geração de imagem: " . $e->getMessage(), [
                'run_id' => $run->id, 'module' => 'ai_tasks',
            ]);
            // Image failure should not block the package — mark it
            $visual = $package->visual_payload ?? [];
            $visual['visual_status'] = 'failed';
            $visual['image_error']   = $e->getMessage();
            $package->update(['visual_payload' => $visual]);
        }
    }

    public function createGeneratedEntity(AgentTaskRun $run, AgentTask $task, array $payload): BlogPost|SocialPost
    {
        if ($task->isBlogType()) {
            return $this->createBlogPost($run, $task, $payload);
        }
        return $this->createSocialPost($run, $task, $payload);
    }

    public function createApprovalPackage(
        AgentTaskRun $run,
        AgentTask $task,
        $entity,
        array $payload,
        AiExecutionContext $context
    ): GeneratedContentPackage {
        $visualPayload = [
            'visual_status'  => 'pending',
            'image_prompt'   => $payload['image_prompt'] ?? null,
            'image_type'     => $task->image_type,
        ];

        $sourcesPayload = !empty($payload['sources_used']) ? ['sources' => $payload['sources_used']] : null;

        $package = GeneratedContentPackage::create([
            'company_id'           => $task->company_id,
            'client_id'            => $task->client_id,
            'agent_task_id'        => $task->id,
            'agent_task_run_id'    => $run->id,
            'ai_employee_id'       => $run->ai_employee_id,
            'package_type'         => $this->resolvePackageType($task),
            'title'                => $payload['title'] ?? $task->title,
            'summary'              => $payload['excerpt'] ?? $payload['caption'] ?? null,
            'content_payload'      => $payload,
            'visual_payload'       => $visualPayload,
            'sources_payload'      => $sourcesPayload,
            'status'               => 'pending_approval',
            'generated_entity_type'=> get_class($entity),
            'generated_entity_id'  => $entity->id,
            'metadata'             => [
                'brand_context_version'   => $context->brand_context_version,
                'task_version'            => $context->task_version,
                'execution_context_id'    => $context->id,
                'input_tokens'            => $run->input_tokens,
                'output_tokens'           => $run->output_tokens,
            ],
        ]);

        $this->activityLog->info('content.package.created', "Pacote criado para '{$task->title}'", [
            'package_id' => $package->id, 'entity_type' => get_class($entity), 'entity_id' => $entity->id, 'module' => 'ai_tasks',
        ]);

        return $package;
    }

    public function createApprovalRequest(
        GeneratedContentPackage $package,
        AgentTask $task,
        $entity,
        AiEmployee $employee
    ): \App\Models\ApprovalRequest {
        $title       = "Conteúdo gerado: {$package->title}";
        $description = "Gerado por {$employee->name} via tarefa '{$task->title}'. Verifique texto, imagem e SEO antes de aprovar.";

        $payload = [
            'package_id'          => $package->id,
            'task_id'             => $task->id,
            'task_title'          => $task->title,
            'task_type'           => $task->task_type,
            'employee_name'       => $employee->name,
            'entity_type'         => get_class($entity),
            'entity_id'           => $entity->id,
            'content_summary'     => $package->summary,
            'visual_status'       => $package->getVisualStatus(),
            'metadata'            => $package->metadata ?? [],
        ];

        $approval = $this->approvalService->createApproval([
            'client_id'       => $task->client_id,
            'ai_employee_id'  => $employee->id,
            'approvable_type' => get_class($package),
            'approvable_id'   => $package->id,
            'title'           => $title,
            'description'     => $description,
            'approval_type'   => 'ai_task',
            'sensitive_level' => 'medium',
            'payload'         => $payload,
        ]);

        $package->update(['approval_request_id' => $approval->id]);

        $this->activityLog->info('approval.package.created', "Aprovação criada para '{$task->title}'", [
            'approval_id' => $approval->id, 'package_id' => $package->id, 'module' => 'approvals',
        ]);

        return $approval;
    }

    public function calculateNextRun(AgentTask $task): ?Carbon
    {
        return match ($task->frequency) {
            'manual'  => null,
            'daily'   => $this->nextDaily($task),
            'weekly'  => $this->nextWeekly($task),
            'monthly' => $this->nextMonthly($task),
            default   => null,
        };
    }

    public function calculateAndSaveNextRun(AgentTask $task): void
    {
        $next = $this->calculateNextRun($task);
        $task->update(['next_run_at' => $next]);
    }

    public function markRunFailed(AgentTaskRun $run, Throwable|string $error): void
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;

        $run->update([
            'status'        => 'failed',
            'finished_at'   => now(),
            'error_message' => $message,
        ]);

        $this->activityLog->error('agent_task.run_failed', "Execução falhou: {$message}", [
            'run_id'  => $run->id,
            'task_id' => $run->agent_task_id,
            'module'  => 'ai_tasks',
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function createBlogPost(AgentTaskRun $run, AgentTask $task, array $payload): BlogPost
    {
        $slug = $payload['slug'] ?? \Str::slug($payload['title'] ?? $task->title);
        // Ensure slug uniqueness
        $base = $slug;
        $i    = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        // secondary_keywords: text column — store as comma-separated string
        $secondaryKeywords = $payload['secondary_keywords'] ?? [];
        if (is_array($secondaryKeywords)) {
            $secondaryKeywords = implode(',', array_filter($secondaryKeywords)) ?: null;
        }

        // tags: json/array column — store as array
        $tags = $payload['tags'] ?? [];
        if (is_string($tags)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tags))));
        }
        $tags = empty($tags) ? null : $tags;

        // type: enum('agency','client') — tasks created by AI are agency posts
        $postType = $task->client_id ? 'client' : 'agency';

        $post = BlogPost::create([
            'company_id'         => $task->company_id,
            'client_id'          => $task->client_id,
            'ai_employee_id'     => $run->ai_employee_id,
            'agent_task_id'      => $task->id,
            'agent_task_run_id'  => $run->id,
            'title'              => $payload['title'] ?? $task->title,
            'slug'               => $slug,
            'subtitle'           => $payload['subtitle'] ?? null,
            'excerpt'            => $payload['excerpt'] ?? null,
            'content'            => $payload['content_html'] ?? '',
            'seo_title'          => $payload['seo_title'] ?? null,
            'seo_description'    => $payload['seo_description'] ?? null,
            'focus_keyword'      => $payload['focus_keyword'] ?? null,
            'secondary_keywords' => $secondaryKeywords,
            'tags'               => $tags,
            'status'             => 'pending_approval',
            'type'               => $postType,
            'ai_metadata'        => ['generated_by_task' => $task->id, 'run_id' => $run->id],
            'scheduled_at'       => $task->auto_schedule_after_approval ? $this->calculateScheduledAt($task) : null,
        ]);

        return $post;
    }

    private function createSocialPost(AgentTaskRun $run, AgentTask $task, array $payload): SocialPost
    {
        $isCarousel = $task->isCarouselType();

        $post = SocialPost::create([
            'company_id'         => $task->company_id,
            'client_id'          => $task->client_id,
            'ai_employee_id'     => $run->ai_employee_id,
            'agent_task_id'      => $task->id,
            'agent_task_run_id'  => $run->id,
            'title'              => $payload['title'] ?? $task->title,
            'main_caption'       => $payload['caption'] ?? '',
            'hashtags'           => implode(' ', $payload['hashtags'] ?? []),
            'cta'                => $payload['cta'] ?? null,
            'content_type'       => $isCarousel ? 'carousel' : 'feed',
            'creative_format'    => $isCarousel ? 'carousel' : 'feed',
            'creative_brief'     => $payload['creative_brief'] ?? null,
            'image_prompt'       => $payload['image_prompt'] ?? null,
            'status'             => 'pending_approval',
            'requires_approval'  => true,
            'carousel_enabled'   => $isCarousel,
            'carousel_slide_count' => $isCarousel ? ($task->carousel_slides_count ?? count($payload['slides'] ?? [])) : null,
            'metadata'           => ['generated_by_task' => $task->id],
            'scheduled_at'       => $task->auto_schedule_after_approval ? $this->calculateScheduledAt($task) : null,
        ]);

        return $post;
    }

    private function resolvePackageType(AgentTask $task): string
    {
        return match ($task->task_type) {
            'blog_post_recurring'          => 'blog_post',
            'instagram_post_recurring'     => 'instagram_post',
            'instagram_carousel_recurring' => 'instagram_carousel',
            default                        => 'custom',
        };
    }

    private function calculateScheduledAt(AgentTask $task): ?Carbon
    {
        if ($task->time_of_day) {
            $next = $this->calculateNextRun($task);
            return $next ?? now()->addDay();
        }
        return now()->addDay();
    }

    private function executeResearchIfEnabled(AgentTask $task, AiExecutionContext $context): array
    {
        if (!config('external-research.enabled', false)) {
            return [];
        }

        try {
            $provider = app(ExternalResearchProviderInterface::class);
            $topics   = $task->external_research_topics ?? 'tendências recentes';
            $days     = $task->external_research_recency_days ?? 7;
            return $provider->searchRecent($topics, $days, 5);
        } catch (Throwable $e) {
            Log::warning("[AgentTaskExecution] Research failed: " . $e->getMessage());
            return [];
        }
    }

    private function recordUsage(AgentTaskRun $run, AgentTask $task, AiExecutionContext $context): void
    {
        try {
            AiUsageRecord::create([
                'company_id'        => $task->company_id,
                'client_id'         => $task->client_id,
                'ai_employee_id'    => $run->ai_employee_id,
                'agent_id'          => $run->ai_employee_id,
                'agent_task_id'     => $task->id,
                'agent_task_run_id' => $run->id,
                'provider'          => $context->provider ?? 'google',
                'model'             => $context->model ?? '',
                'task_type'         => $task->task_type,
                'input_tokens'      => $run->input_tokens,
                'output_tokens'     => $run->output_tokens,
                'estimated_cost_usd'=> $context->estimated_cost_usd,
                'status'            => 'recorded',
                'created_at'        => now(),
            ]);

            $this->activityLog->info('usage.recorded', "Uso registrado para '{$task->title}'", [
                'task_id' => $task->id, 'run_id' => $run->id, 'module' => 'ai_tasks',
            ]);
        } catch (Throwable $e) {
            Log::warning("[AgentTaskExecution] Failed to record usage: " . $e->getMessage());
        }
    }

    private function nextDaily(AgentTask $task): Carbon
    {
        $time = $task->time_of_day ?? '09:00:00';
        $tz   = $task->timezone ?? 'America/Sao_Paulo';
        $next = now($tz)->addDay()->setTimeFromTimeString($time);
        return $next->utc();
    }

    private function nextWeekly(AgentTask $task): ?Carbon
    {
        $days = $task->days_of_week ?? [];
        if (empty($days)) return now()->addWeek();

        $time = $task->time_of_day ?? '09:00:00';
        $tz   = $task->timezone ?? 'America/Sao_Paulo';
        $now  = now($tz);

        $dayMap = [
            'monday'    => Carbon::MONDAY,
            'tuesday'   => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday'  => Carbon::THURSDAY,
            'friday'    => Carbon::FRIDAY,
            'saturday'  => Carbon::SATURDAY,
            'sunday'    => Carbon::SUNDAY,
        ];

        $candidates = [];
        foreach ($days as $day) {
            $num = $dayMap[strtolower($day)] ?? null;
            if (!$num) continue;
            $candidate = $now->copy()->next($num)->setTimeFromTimeString($time);
            if ($candidate->lte($now)) {
                $candidate = $candidate->addWeek();
            }
            $candidates[] = $candidate;
        }

        if (empty($candidates)) return now()->addWeek();
        usort($candidates, fn($a, $b) => $a->timestamp <=> $b->timestamp);

        return $candidates[0]->utc();
    }

    private function nextMonthly(AgentTask $task): Carbon
    {
        $day  = $task->day_of_month ?? 1;
        $time = $task->time_of_day ?? '09:00:00';
        $tz   = $task->timezone ?? 'America/Sao_Paulo';
        $now  = now($tz);

        $next = $now->copy()->startOfMonth()->addDays($day - 1)->setTimeFromTimeString($time);
        if ($next->lte($now)) {
            $next = $next->addMonth();
        }

        return $next->utc();
    }
}
