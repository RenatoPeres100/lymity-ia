<?php

namespace App\Services\Social;

use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\ApprovalRequest;
use App\Models\Company;
use App\Models\SocialPost;
use App\Models\SocialPostVariant;
use App\Models\User;
use App\Services\AI\AIProviderManager;
use App\Services\Approval\ApprovalService;
use App\Services\Brand\BrandContextService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class SocialPostService
{
    public function __construct(
        private ApprovalService  $approvalService,
        private ?AIProviderManager  $providerManager = null,
        private ?BrandContextService $brandContext    = null,
        private ?SocialImageService  $imageService    = null,
    ) {}

    public function createDraft(array $data, User $user): SocialPost
    {
        $data['status'] = 'draft';

        // Agency posts always have company_id from first company if not provided
        if (empty($data['company_id'])) {
            $data['company_id'] = Company::first()?->id;
        }

        return $this->create($data, $user);
    }

    public function generateCaptionWithGemini(SocialPost $post, User $user): SocialPost
    {
        $pm = $this->providerManager ?? app(AIProviderManager::class);
        $bc = $this->brandContext    ?? app(BrandContextService::class);

        $provider = $pm->provider();
        if (!$provider->isConfigured()) {
            throw new \RuntimeException('Gemini não configurado. Verifique GEMINI_API_KEY e AI_PROVIDER=google no .env.');
        }

        $brandCtx = $bc->getCompactContext();

        $objective   = $post->objective_label;
        $contentType = $post->content_type_label;
        $brief       = $post->creative_brief ?? $post->title;
        $audience    = $post->metadata['target_audience'] ?? 'empreendedores e gestores digitais';
        $tone        = $post->metadata['tone'] ?? 'profissional e inspirador';
        $cta         = $post->metadata['desired_cta'] ?? $post->cta ?? '';

        $systemPrompt = <<<PROMPT
Você é o Social Media IA da Lymity — agência de inteligência artificial aplicada ao crescimento de negócios digitais.

Sua função é criar legendas excepcionais para Instagram que geram engajamento real, comunicam autoridade e convertem.

Contexto da marca:
{$brandCtx}

Regras:
- Tom: {$tone}
- Objetivo: {$objective}
- Formato: {$contentType}
- Público: {$audience}
- Retorne JSON válido com as chaves: main_caption, hashtags, cta
- main_caption: texto completo da legenda (até 2.200 chars), emojis estratégicos, quebras de linha naturais
- hashtags: string com 5-10 hashtags relevantes separadas por espaço
- cta: chamada para ação clara e direta (1 linha)
- Nunca invente dados, métricas ou fatos sem base
- Não use linguagem genérica — seja específico ao universo de IA e crescimento digital
PROMPT;

        $userMessage = "Crie uma legenda para Instagram sobre o seguinte tema:\n\n{$brief}";
        if ($cta) $userMessage .= "\n\nCTA desejado: {$cta}";

        $response = $provider->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => 1500,
            'temperature'   => 0.75,
        ]);

        if (!$response->success) {
            $this->logActivity($post, 'social_caption_generation_failed', $user, ['error' => $response->error_message]);
            throw new \RuntimeException("Falha ao gerar legenda: {$response->error_message}");
        }

        $json = $response->json ?? [];

        $post->update([
            'main_caption' => $json['main_caption'] ?? $response->text,
            'hashtags'     => $json['hashtags'] ?? null,
            'cta'          => $json['cta'] ?? null,
        ]);

        $this->logActivity($post, 'social_caption_generated', $user, [
            'provider' => $response->provider,
            'model'    => $response->model,
        ]);

        return $post->fresh();
    }

    public function generateImageWithGemini(SocialPost $post, User $user): SocialPost
    {
        $imgSvc = $this->imageService ?? app(SocialImageService::class);
        return $imgSvc->generateWithGemini($post, $user);
    }

    public function submitForApproval(SocialPost $post, User $user): SocialPost
    {
        return $this->sendToApproval($post, $user);
    }

    public function schedule(SocialPost $post, Carbon|string $scheduledAt, User $user): SocialPost
    {
        if ($post->status !== 'approved') {
            throw new \RuntimeException("Post #{$post->id} precisa estar aprovado para ser agendado. Status atual: {$post->status}");
        }

        $at = $scheduledAt instanceof Carbon ? $scheduledAt : Carbon::parse($scheduledAt);

        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $at,
        ]);

        $this->logActivity($post, 'social_post_scheduled', $user);

        return $post->fresh();
    }

    public function publishNow(SocialPost $post, User $user): SocialPost
    {
        if (!in_array($post->status, ['approved', 'scheduled'])) {
            throw new \RuntimeException("Post #{$post->id} precisa estar aprovado ou agendado para publicar. Status: {$post->status}");
        }

        $this->logActivity($post, 'social_post_publish_now_clicked', $user);

        return $post->fresh();
    }

    public function markPublishing(SocialPost $post): SocialPost
    {
        $post->update(['status' => 'publishing']);
        $this->logActivity($post, 'social_post_publishing', null);
        return $post->fresh();
    }

    public function markPublished(SocialPost $post, array $result = []): SocialPost
    {
        $post->update([
            'status'            => 'published',
            'published_at'      => now(),
            'external_post_id'  => $result['id'] ?? $post->external_post_id,
            'publication_error' => null,
        ]);
        $this->logActivity($post, 'social_post_published', null);
        return $post->fresh();
    }

    public function markFailed(SocialPost $post, Throwable|string $error): SocialPost
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;
        $safe    = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $message);
        $post->update(['status' => 'failed', 'publication_error' => $safe]);
        $this->logActivity($post, 'social_post_failed', null);
        return $post->fresh();
    }

    public function registerLog(SocialPost $post, string $action, ?User $user = null, array $metadata = []): void
    {
        $this->logActivity($post, $action, $user, $metadata);
    }

    public function create(array $data, ?User $user = null): SocialPost
    {
        $post = SocialPost::create([
            'client_id'        => $data['client_id'] ?? null,
            'company_id'       => $data['company_id'] ?? null,
            'created_by'       => $user?->id ?? Auth::id(),
            'ai_employee_id'   => $data['ai_employee_id'] ?? null,
            'title'            => $data['title'],
            'objective'        => $data['objective'] ?? 'authority',
            'content_type'     => $data['content_type'] ?? 'feed',
            'main_caption'     => $data['main_caption'] ?? null,
            'creative_brief'   => $data['creative_brief'] ?? null,
            'hashtags'         => $data['hashtags'] ?? null,
            'cta'              => $data['cta'] ?? null,
            'status'           => $data['status'] ?? 'draft',
            'requires_approval'=> $data['requires_approval'] ?? true,
            'scheduled_at'     => $data['scheduled_at'] ?? null,
            'metadata'         => $data['metadata'] ?? null,
        ]);

        $this->logActivity($post, 'social_post_created', $user);

        return $post;
    }

    public function sendToApproval(SocialPost $post, User $user): SocialPost
    {
        if (!in_array($post->status, ['draft', 'rejected'])) {
            throw new \RuntimeException("Post #{$post->id} não pode ser enviado para aprovação no status: {$post->status}");
        }

        $post->update(['status' => 'pending_approval']);

        $sensitiveLevel = in_array($post->objective, ['leads', 'sales']) ? 'medium' : 'low';

        $this->approvalService->createApproval([
            'client_id'       => $post->client_id,
            'requested_by'    => $user->id,
            'ai_employee_id'  => $post->ai_employee_id,
            'approvable_type' => SocialPost::class,
            'approvable_id'   => $post->id,
            'title'           => "Aprovar post: {$post->title}",
            'description'     => $post->main_caption
                ? substr($post->main_caption, 0, 500)
                : $post->creative_brief,
            'approval_type'   => 'post',
            'sensitive_level' => $sensitiveLevel,
            'payload'         => [
                'objective'    => $post->objective,
                'content_type' => $post->content_type,
                'main_caption' => $post->main_caption,
                'hashtags'     => $post->hashtags,
                'cta'          => $post->cta,
                'scheduled_at' => $post->scheduled_at?->toIso8601String(),
            ],
        ]);

        $this->logActivity($post, 'social_post_sent_to_approval', $user);

        return $post->fresh();
    }

    public function approve(SocialPost $post, User $user, ?string $notes = null): SocialPost
    {
        $post->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->logActivity($post, 'social_post_approved', $user);

        return $post->fresh();
    }

    public function reject(SocialPost $post, User $user, ?string $notes = null): SocialPost
    {
        $post->update(['status' => 'rejected']);
        $this->logActivity($post, 'social_post_rejected', $user);

        return $post->fresh();
    }

    public function backToDraft(SocialPost $post, User $user): SocialPost
    {
        $post->update(['status' => 'draft']);
        $this->logActivity($post, 'social_post_updated', $user);

        return $post->fresh();
    }

    public function generateVariants(SocialPost $post, array $platforms, ?string $baseCaption = null): array
    {
        $variants = [];

        $platformNotes = [
            'instagram' => 'Tom emocional, emojis, visual. Máx 2.200 chars.',
            'facebook'  => 'Tom amigável e informativo. Links funcionam.',
            'linkedin'  => 'Tom profissional, dados e resultados. Foco em B2B.',
            'tiktok'    => 'Tom dinâmico e direto. Gancho forte nas 3 primeiras linhas.',
            'threads'   => 'Tom conversacional e opinativo. Pode fazer perguntas.',
            'youtube'   => 'Descrição de vídeo com SEO. Timestamps recomendados.',
            'pinterest' => 'Foco em inspiração e descoberta. Descrição com keywords.',
        ];

        $caption = $baseCaption ?? $post->main_caption ?? $post->creative_brief ?? $post->title;

        foreach ($platforms as $platform) {
            $existing = SocialPostVariant::where('social_post_id', $post->id)
                ->where('platform', $platform)
                ->first();

            if ($existing) {
                $variants[] = $existing;
                continue;
            }

            $variant = SocialPostVariant::create([
                'social_post_id' => $post->id,
                'platform'       => $platform,
                'caption'        => $this->adaptCaptionForPlatform($caption, $platform),
                'hashtags'       => $post->hashtags,
                'cta'            => $post->cta,
                'creative_notes' => $platformNotes[$platform] ?? null,
                'status'         => 'draft',
            ]);

            $variants[] = $variant;
        }

        return $variants;
    }

    private function adaptCaptionForPlatform(string $caption, string $platform): string
    {
        return match ($platform) {
            'linkedin' => "🔍 Reflexão do dia:\n\n{$caption}\n\nQuais são suas perspectivas sobre esse tema? Comente abaixo.",
            'tiktok'   => "POV: {$caption}\n\nSiga para mais conteúdo sobre IA e marketing digital 🎯",
            'threads'  => "{$caption}\n\nO que você acha? Me conta nos comentários.",
            default    => $caption,
        };
    }

    private function logActivity(SocialPost $post, string $action, ?User $user, array $extra = []): void
    {
        if (!class_exists(ActivityLog::class)) {
            return;
        }

        try {
            ActivityLog::create([
                'user_id'     => $user?->id ?? Auth::id(),
                'client_id'   => $post->client_id,
                'action'      => $action,
                'module'      => 'social_media',
                'description' => "SocialPost #{$post->id} — {$post->title}",
                'metadata'    => array_merge([
                    'social_post_id' => $post->id,
                    'status'         => $post->status,
                ], $extra),
            ]);
        } catch (\Throwable) {
            // Never break service flow due to log failure
        }
    }
}
