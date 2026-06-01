<?php

namespace App\Services\Agents\RoutineHandlers;

use App\Models\ActivityLog;
use App\Models\AgentRoutine;
use App\Models\AgentRoutineRun;
use App\Models\ApprovalRequest;
use App\Models\SocialPost;
use App\Services\AI\AIProviderManager;
use App\Services\Brand\BrandContextService;
use App\Services\Social\SocialImageService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SocialRoutineHandler
{
    public function __construct(
        private AIProviderManager   $providerManager,
        private BrandContextService $brandContext,
        private SocialImageService  $imageService,
    ) {}

    /**
     * Returns ['items_created' => int, 'approvals_created' => int, 'summaries' => array]
     */
    public function handle(AgentRoutine $routine, AgentRoutineRun $run, int $quantity, bool $dryRun = false): array
    {
        $summaries        = [];
        $itemsCreated     = 0;
        $approvalsCreated = 0;
        $autoImage        = (bool) ($routine->metadata['auto_generate_image'] ?? false);

        for ($i = 0; $i < $quantity; $i++) {
            try {
                if ($dryRun) {
                    $summaries[] = "[dry-run] SocialPost #{$i} seria criado.";
                    continue;
                }

                $post = $this->createSocialPost($routine, $run, $i);
                $itemsCreated++;
                $run->increment('items_created');

                // Auto-generate image if configured and caption was created
                if ($autoImage && !empty(trim($post->main_caption ?? ''))) {
                    try {
                        $fakeUser = new \App\Models\User(['id' => null, 'name' => $routine->aiEmployee?->name ?? 'Agent']);
                        $fakeUser->exists = false;
                        $this->imageService->generateSingleImageFromPost($post, $fakeUser);
                        $post->refresh();
                    } catch (Throwable $e) {
                        Log::warning("[SocialRoutineHandler] Auto-image failed for post #{$post->id}: " . $e->getMessage());
                    }
                }

                $this->logActivity('agent_routine_content_generated', $routine, $run, [
                    'model' => 'SocialPost', 'post_id' => $post->id, 'title' => $post->title,
                ]);

                if ($routine->requires_approval && !$this->hasExistingApproval($post)) {
                    $this->createApproval($post, $routine, $run);
                    $approvalsCreated++;
                    $run->increment('approvals_created');
                }

                $summaries[] = "SocialPost #{$post->id}: {$post->title}";
            } catch (Throwable $e) {
                $safe = $this->redact($e->getMessage());
                Log::error("[SocialRoutineHandler] Item {$i} failed: {$safe}", ['routine_id' => $routine->id]);
                $summaries[] = "Erro item {$i}: {$safe}";
            }
        }

        return compact('itemsCreated', 'approvalsCreated', 'summaries');
    }

    private function createSocialPost(AgentRoutine $routine, AgentRoutineRun $run, int $index): SocialPost
    {
        $provider  = $this->providerManager->provider();
        $brandCtx  = $this->brandContext->getCompactContext();
        $employee  = $routine->aiEmployee;

        $objectives  = ['authority', 'engagement', 'leads', 'relationship', 'awareness'];
        $objective   = $objectives[$index % count($objectives)];

        $systemPrompt = <<<PROMPT
Você é o Social Media IA da Lymity — agência de inteligência artificial aplicada ao crescimento de negócios digitais.

Contexto da marca:
{$brandCtx}

Crie um post para Instagram que comunique autoridade, gere engajamento e seja específico ao universo de IA e crescimento digital. Não use linguagem genérica.

Retorne JSON com as chaves exatas:
{
  "title": "...",
  "main_caption": "...",
  "hashtags": "...",
  "cta": "...",
  "creative_brief": "..."
}
- main_caption: até 2.200 chars, emojis estratégicos, quebras de linha naturais
- hashtags: 5-10 hashtags relevantes separadas por espaço
- cta: chamada para ação direta (1 linha)
PROMPT;

        $themes = [
            'automação de conteúdo com IA para agências',
            'como IA aumenta a produtividade de equipes de marketing',
            'resultado real de social media com estratégia e IA',
            'aprovação humana + execução IA: a operação do futuro',
            'como a Lymity IA transforma a criação de conteúdo',
        ];
        $theme = $themes[$index % count($themes)];

        $response = $provider->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => "Crie um post de Instagram para a Lymity IA com objetivo '{$objective}' sobre o tema: {$theme}",
            'json_mode'     => true,
            'max_tokens'    => 1200,
            'temperature'   => 0.8,
        ]);

        if (!$response->success) {
            throw new \RuntimeException("Falha ao gerar post social: {$response->error_message}");
        }

        $json = $response->json ?? [];

        $scheduledAt = $this->calculateTargetPublicationDate($routine);

        return SocialPost::create([
            'company_id'       => $routine->company_id,
            'client_id'        => $routine->client_id,
            'ai_employee_id'   => $routine->ai_employee_id,
            'title'            => $json['title'] ?? "Post Instagram — {$theme}",
            'objective'        => $objective,
            'content_type'     => 'feed',
            'creative_format'  => 'feed_image',
            'main_caption'     => $json['main_caption'] ?? null,
            'hashtags'         => $json['hashtags'] ?? '#LymityIA #MarketingIA',
            'cta'              => $json['cta'] ?? 'Conheça a Lymity IA',
            'creative_brief'   => $json['creative_brief'] ?? $theme,
            'status'           => $routine->requires_approval ? 'pending_approval' : 'draft',
            'requires_approval'=> $routine->requires_approval,
            'image_status'     => 'missing',
            'image_validation_status' => null,
            'scheduled_at'     => $scheduledAt,
            'metadata'         => [
                'generated_by_routine_id' => $routine->id,
                'routine_run_id'          => $run->id,
                'provider'                => $response->provider,
                'model'                   => $response->model,
            ],
        ]);
    }

    private function calculateTargetPublicationDate(AgentRoutine $routine): ?\Illuminate\Support\Carbon
    {
        $leadDays = max(1, (int) ($routine->approval_lead_days ?? 1));
        $pubTime  = $routine->publication_time ?? $routine->time_of_day ?? '09:00';
        [$h, $m]  = explode(':', $pubTime);
        return now()->addDays($leadDays)->setHour((int)$h)->setMinute((int)$m)->setSecond(0);
    }

    private function hasExistingApproval(SocialPost $post): bool
    {
        return ApprovalRequest::where('approvable_type', SocialPost::class)
            ->where('approvable_id', $post->id)
            ->where('status', 'pending')
            ->exists();
    }

    private function createApproval(SocialPost $post, AgentRoutine $routine, AgentRoutineRun $run): void
    {
        ApprovalRequest::create([
            'client_id'       => $routine->client_id,
            'requested_by'    => null,
            'ai_employee_id'  => $routine->ai_employee_id,
            'approvable_type' => SocialPost::class,
            'approvable_id'   => $post->id,
            'title'           => "Aprovar post social: {$post->title}",
            'description'     => "Gerado pela rotina \"{$routine->title}\". Agente: {$routine->aiEmployee?->name}.",
            'approval_type'   => 'post',
            'status'          => 'pending',
            'sensitive_level' => 'medium',
            'payload'         => [
                'routine_id'     => $routine->id,
                'routine_run_id' => $run->id,
                'company_id'     => $routine->company_id,
            ],
        ]);
    }

    private function logActivity(string $action, AgentRoutine $routine, AgentRoutineRun $run, array $extra = []): void
    {
        try {
            ActivityLog::create([
                'user_id' => null, 'action' => $action, 'module' => 'agent_routines', 'level' => 'info',
                'description' => "SocialRoutineHandler: {$action}",
                'metadata' => array_merge(['routine_id' => $routine->id, 'run_id' => $run->id], $extra),
            ]);
        } catch (Throwable) {}
    }

    private function redact(string $msg): string
    {
        return preg_replace('/(?:key|token|secret)=[^\s&]+/i', '***', $msg);
    }
}
