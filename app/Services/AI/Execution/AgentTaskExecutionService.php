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
use App\Exceptions\AIInvalidJsonResponseException;
use App\Services\AI\AICostGuardService;
use App\Services\AI\AIProviderManager;
use App\Services\AI\Context\AgentTaskContextService;
use App\Services\AI\Context\BrandContextSnapshotService;
use App\Services\AI\Images\ContentPackageImageGenerationService;
use App\Services\AI\Memory\AIMemoryService;
use App\Services\AI\Prompt\StructuredPromptBuilderService;
use App\Services\AI\Response\AIContentPayloadValidatorService;
use App\Services\AI\Response\AIJsonResponseNormalizerService;
use App\Services\Approval\ApprovalService;
use App\Services\Logs\ActivityLogService;
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
        private AIJsonResponseNormalizerService        $jsonNormalizer,
        private AIContentPayloadValidatorService       $payloadValidator,
        private AIExecutionGuardService                $guard,
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

        // Duplicate run guard: skip if a run is already in progress for this task
        $inFlight = AgentTaskRun::where('agent_task_id', $task->id)
            ->whereIn('status', ['queued', 'running', 'preparing_context', 'generating_text', 'generating_image'])
            ->where('started_at', '>=', now()->subMinutes(10))
            ->first();
        if ($inFlight && $user === null) {
            throw new \RuntimeException(
                "Task #{$task->id} já está em execução (run #{$inFlight->id} status={$inFlight->status}). Pulando."
            );
        }

        // Guard: validates task, employee and brand context — throws with clear message if invalid
        $this->guard->assertCanGenerateFromTask($task, $employee, isManual: $user !== null);

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
        // Guard ensures brand context exists — will throw if missing
        $brandContext = $this->guard->assertCanGenerateFromTask($task, $employee, isManual: false);

        $compactBrand = $this->brandContextService->getCompactContext($brandContext);

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

        $prompt      = $this->promptBuilder->buildForTaskExecution($task, $employee, $context);
        $previewHash = hash('sha256', $prompt);
        $preview     = $this->promptBuilder->sanitizePromptPreview($prompt);

        $context->update([
            'prompt_hash'    => $previewHash,
            'prompt_preview' => $preview,
            'status'         => 'running',
        ]);

        $provider   = $this->providerManager->provider();
        $schemaName = $task->task_type;
        $isBlogType = $task->isBlogType();

        // ── Attempt 1 ─────────────────────────────────────────────────────────
        $response = $provider->generateText([
            'system_prompt' => "Você é um assistente especialista em conteúdo. RESPONDA APENAS COM JSON VÁLIDO. Sem markdown, sem texto fora do JSON, sem blocos ```json. Escape todas as quebras de linha dentro de strings como \\n.",
            'user_message'  => $prompt,
            'json_mode'     => true,
            'task_type'     => $task->task_type,
        ]);

        if (!$response->success) {
            $this->recordFailedGeneration($run, $context, $response, 'text_generation_failed');
            throw new \RuntimeException("Geração de texto falhou: " . $response->error_message);
        }

        // Log finish_reason if not STOP
        if ($response->finish_reason && $response->finish_reason !== 'STOP') {
            $this->activityLog->warning('ai.generation.finish_reason', "finish_reason={$response->finish_reason}", [
                'run_id'       => $run->id,
                'finish_reason'=> $response->finish_reason,
                'output_tokens'=> $response->output_tokens,
                'module'       => 'ai_tasks',
            ]);
        }

        // ── Parse with normalizer — retry once if JSON is invalid ─────────────
        $data = null;
        try {
            $data = $this->jsonNormalizer->normalizeToArray($response->text, [], $schemaName);
        } catch (AIInvalidJsonResponseException $e) {
            $this->activityLog->warning('ai.generation.failed_json', "JSON inválido na tentativa 1 — iniciando retry de correção", [
                'run_id'  => $run->id,
                'error'   => $e->getMessage(),
                'preview' => $this->jsonNormalizer->sanitizeForLog($response->text, 300),
                'module'  => 'ai_tasks',
            ]);

            // ── Retry: ask Gemini to fix the broken JSON ───────────────────────
            $badPreview  = $this->jsonNormalizer->sanitizeForLog($response->text, 800);
            $retryPrompt = "O JSON que você retornou é inválido. Corrija-o e retorne APENAS JSON válido, sem nenhum texto adicional.\n\n" .
                           "Erro: {$e->getMessage()}\n\n" .
                           "JSON com problema (início):\n{$badPreview}\n\n" .
                           "Retorne o JSON corrigido e completo. Nenhum texto fora do JSON.";

            $retryResponse = $provider->generateText([
                'system_prompt' => "Você é um especialista em JSON. Retorne APENAS JSON válido, sem markdown, sem texto adicional.",
                'user_message'  => $retryPrompt,
                'json_mode'     => true,
                'task_type'     => $task->task_type,
                'temperature'   => 0.1,
            ]);

            if (!$retryResponse->success) {
                $this->recordFailedGeneration($run, $context, $retryResponse, 'json_retry_failed');
                throw new \RuntimeException("Geração falhou após retry: resposta JSON inválida do Gemini.");
            }

            try {
                $data = $this->jsonNormalizer->normalizeToArray($retryResponse->text, [], $schemaName);
                $this->activityLog->info('ai.generation.completed', "JSON recuperado via retry para '{$task->title}'", [
                    'run_id' => $run->id, 'module' => 'ai_tasks',
                ]);
                $response = $retryResponse;
            } catch (AIInvalidJsonResponseException $e2) {
                $this->recordFailedGeneration($run, $context, $retryResponse, 'json_invalid_after_retry', $e2->getMessage());
                throw new \RuntimeException("Geração falhou: resposta JSON inválida do Gemini após retry. Tente novamente mais tarde.");
            }
        }

        // ── Validate payload by task type ──────────────────────────────────────
        // If blog type fails validation (missing content, too short, incomplete), do 1 content-completion retry
        try {
            $data = $this->validateAndNormalizePayload($task, $data);
        } catch (AIInvalidJsonResponseException $e) {
            if ($isBlogType) {
                // Record partial payload details
                $receivedKeys   = array_keys(array_filter($data, fn($v) => $v !== null && $v !== ''));
                $partialTitle   = $data['title'] ?? '';
                $finishReason   = $response->finish_reason ?? 'UNKNOWN';

                $this->activityLog->warning('ai.generation.payload_incomplete', "Payload de blog incompleto — tentando retry de conteúdo", [
                    'run_id'               => $run->id,
                    'validation_error'     => $e->getMessage(),
                    'payload_keys'         => $receivedKeys,
                    'finish_reason'        => $finishReason,
                    'output_tokens'        => $response->output_tokens,
                    'module'               => 'ai_tasks',
                ]);

                // Persist metadata before retry so it's visible if retry also fails
                $this->updateRunMetadata($run, [
                    'payload_received_keys' => $receivedKeys,
                    'finish_reason'         => $finishReason,
                    'output_tokens'         => $response->output_tokens,
                    'retry_attempted'       => true,
                    'validation_error'      => $e->getMessage(),
                ]);

                // Build content-completion retry prompt
                $compactBrand   = $context->compact_brand_context ?? '';
                $compactTask    = $context->compact_task_context ?? '';
                $receivedKeysStr = implode(', ', $receivedKeys);
                $schema         = $this->promptBuilder->getBlogOutputSchema();
                $retryPrompt2   = "Você gerou um JSON de blog_post INCOMPLETO. Campos recebidos: [{$receivedKeysStr}].\n" .
                                  "O campo content_markdown está ausente ou o artigo está muito curto. Erro: {$e->getMessage()}\n\n" .
                                  ($partialTitle ? "Título já gerado (mantenha): {$partialTitle}\n\n" : '') .
                                  "Brand Context: {$compactBrand}\n\n" .
                                  "Task: {$compactTask}\n\n" .
                                  "Gere novamente o JSON COMPLETO seguindo o schema abaixo.\n" .
                                  "O campo content_markdown DEVE conter o artigo completo com no mínimo 900 palavras.\n" .
                                  "NÃO use markdown fora do JSON. NÃO retorne resposta parcial.\n\n" .
                                  $schema;

                $retryResponse2 = $provider->generateText([
                    'system_prompt' => "Você é um especialista em criação de blog posts. Retorne APENAS JSON válido e completo.",
                    'user_message'  => $retryPrompt2,
                    'json_mode'     => true,
                    'task_type'     => $task->task_type,
                    'temperature'   => 0.4,
                ]);

                if (!$retryResponse2->success) {
                    $this->recordFailedGeneration($run, $context, $retryResponse2, 'content_retry_failed');
                    throw new \RuntimeException(
                        "A IA retornou conteúdo parcial/incompleto mesmo após retry. A execução foi interrompida para evitar aprovação de conteúdo quebrado. " .
                        "Verifique AI_BLOG_MAX_OUTPUT_TOKENS no .env (recomendado: 6000). " .
                        "finish_reason original: {$finishReason}."
                    );
                }

                try {
                    $data2 = $this->jsonNormalizer->normalizeToArray($retryResponse2->text, [], $schemaName);
                    $data2 = $this->validateAndNormalizePayload($task, $data2);
                    $this->activityLog->info('ai.generation.payload_recovered', "Payload completo recuperado via content-retry para '{$task->title}'", [
                        'run_id' => $run->id, 'module' => 'ai_tasks',
                    ]);
                    $data     = $data2;
                    $response = $retryResponse2;
                } catch (\Throwable $e3) {
                    $this->recordFailedGeneration($run, $context, $retryResponse2, 'content_retry_invalid', $e3->getMessage());
                    throw new \RuntimeException(
                        "A IA retornou conteúdo parcial/incompleto mesmo após retry. A execução foi interrompida para evitar aprovação de conteúdo quebrado. " .
                        "Erro: {$e3->getMessage()}. finish_reason: {$finishReason}. " .
                        "Adicione AI_BLOG_MAX_OUTPUT_TOKENS=6000 no .env e rode php artisan optimize:clear."
                    );
                }
            } else {
                $context->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                throw new \RuntimeException("Payload inválido: " . $e->getMessage());
            }
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
            'finish_reason' => $response->finish_reason ?? null,
        ];

        return $data;
    }

    private function recordFailedGeneration(
        AgentTaskRun    $run,
        AiExecutionContext $context,
        mixed           $response,
        string          $reason,
        ?string         $extra = null
    ): void {
        $msg = $extra ?? ($response->error_message ?? $reason);
        $context->update(['status' => 'failed', 'error_message' => $msg]);
        $this->activityLog->error("ai.generation.{$reason}", $msg, ['run_id' => $run->id, 'module' => 'ai_tasks']);
    }

    private function updateRunMetadata(AgentTaskRun $run, array $extra): void
    {
        try {
            $existing = $run->metadata ?? [];
            $run->update(['metadata' => array_merge($existing, $extra)]);
        } catch (\Throwable) {}
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
                $imageAlt = $textPayload['image_alt'] ?? null;
                $asset = $this->imageService->generateFeaturedImage($package, $imagePrompt, $imageAlt);
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
            'image_alt'      => $payload['image_alt'] ?? null,
            'image_caption'  => $payload['image_caption'] ?? null,
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

        // Back-fill generated_content_package_id on the entity if it has that column
        if ($entity instanceof BlogPost && \Schema::hasColumn('blog_posts', 'generated_content_package_id')) {
            $entity->update(['generated_content_package_id' => $package->id]);
        } elseif ($entity instanceof \App\Models\SocialPost && \Schema::hasColumn('social_posts', 'generated_content_package_id')) {
            $entity->update(['generated_content_package_id' => $package->id]);
        }

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

        // categories: json column
        $categories = $payload['categories'] ?? [];
        if (is_string($categories)) {
            $categories = array_values(array_filter(array_map('trim', explode(',', $categories))));
        }
        $categories = empty($categories) ? null : $categories;

        $post = BlogPost::create([
            'company_id'              => $task->company_id,
            'client_id'               => $task->client_id,
            'ai_employee_id'          => $run->ai_employee_id,
            'agent_task_id'           => $task->id,
            'agent_task_run_id'       => $run->id,
            'generated_content_package_id' => null, // filled after package creation
            'title'                   => $payload['title'] ?? $task->title,
            'slug'                    => $slug,
            'subtitle'                => $payload['subtitle'] ?? null,
            'excerpt'                 => $payload['excerpt'] ?? null,
            'content'                 => $payload['content_html'] ?? '',
            'seo_title'               => $payload['seo_title'] ?? null,
            'seo_description'         => $payload['seo_description'] ?? null,
            'meta_description'        => $payload['meta_description'] ?? $payload['seo_description'] ?? null,
            'focus_keyword'           => $payload['focus_keyword'] ?? null,
            'secondary_keywords'      => $secondaryKeywords,
            'tags'                    => $tags,
            'categories'              => $categories,
            'featured_image_alt'      => $payload['image_alt'] ?? null,
            'featured_image_caption'  => $payload['image_caption'] ?? null,
            'status'                  => 'pending_approval',
            'type'                    => $postType,
            'ai_metadata'             => [
                'generated_by_task' => $task->id,
                'run_id'            => $run->id,
                'image_prompt'      => $payload['image_prompt'] ?? null,
            ],
            'scheduled_at' => $task->auto_schedule_after_approval ? $this->calculateScheduledAt($task) : null,
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

    /**
     * Validate and normalize the payload from Gemini by task type.
     */
    private function validateAndNormalizePayload(AgentTask $task, array $data): array
    {
        return match (true) {
            $task->isBlogType()      => $this->payloadValidator->validateBlogPostPayload($data),
            $task->isCarouselType()  => $this->payloadValidator->validateInstagramCarouselPayload($data),
            $task->isInstagramType() => $this->payloadValidator->validateInstagramPostPayload($data),
            default                  => $data,
        };
    }
}
