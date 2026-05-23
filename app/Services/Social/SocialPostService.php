<?php

namespace App\Services\Social;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\SocialPost;
use App\Models\SocialPostVariant;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use Illuminate\Support\Facades\Auth;

class SocialPostService
{
    public function __construct(private ApprovalService $approvalService) {}

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

    private function logActivity(SocialPost $post, string $action, ?User $user): void
    {
        if (!class_exists(ActivityLog::class)) {
            return;
        }

        ActivityLog::create([
            'user_id'     => $user?->id ?? Auth::id(),
            'client_id'   => $post->client_id,
            'action'      => $action,
            'module'      => 'social_media',
            'description' => "SocialPost #{$post->id} — {$post->title}",
            'metadata'    => [
                'social_post_id' => $post->id,
                'status'         => $post->status,
            ],
        ]);
    }
}
