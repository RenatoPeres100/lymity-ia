<?php

namespace App\Services\Instagram;

use App\Models\ActivityLog;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class InstagramPublishingService
{
    private string $graphBase;

    public function __construct()
    {
        $version         = config('meta.graph_version', 'v25.0');
        $this->graphBase = "https://graph.facebook.com/{$version}";
    }

    // ── Publish guards ─────────────────────────────────────────────────────────

    public function canPublish(SocialChannel $channel, ?SocialPost $post = null): bool
    {
        if (!config('meta.instagram_publishing_enabled', false)) return false;
        if (!$channel->isConnected() || !$channel->hasValidToken())  return false;
        if (empty($channel->instagram_user_id))                       return false;

        if ($post !== null) {
            if (!in_array($post->status, ['approved', 'scheduled']))  return false;
            if (in_array($post->status, ['rejected', 'archived', 'failed'])) return false;
            if (!$post->hasPublicImage())                              return false;
            $caption = trim($post->main_caption ?? $post->content ?? '');
            if (empty($caption))                                       return false;
            if ($post->requires_approval && !$post->isApprovedForPublishing()) return false;
        }

        return true;
    }

    public function validateChannel(SocialChannel $channel): void
    {
        abort_unless(
            config('meta.instagram_publishing_enabled', false), 403,
            'Publicação no Instagram desabilitada. Configure INSTAGRAM_PUBLISHING_ENABLED=true após validar a conexão.'
        );
        abort_unless($channel->isConnected(), 422,
            'Canal Instagram não conectado. Conecte primeiro em /admin/social/instagram.');
        abort_unless($channel->hasValidToken(), 422,
            'Token do canal expirado ou inválido. Reconecte o Instagram.');
        abort_unless(!empty($channel->instagram_user_id), 422,
            'ID de usuário Instagram ausente. Reconecte o canal.');
    }

    public function validatePost(SocialPost $post): void
    {
        abort_if(in_array($post->status, ['rejected', 'archived', 'failed']), 422,
            "Post no status '{$post->status_label}' não pode ser publicado.");
        abort_unless(in_array($post->status, ['approved', 'scheduled', 'publishing']), 422,
            'Post não aprovado. Aprovação é obrigatória antes de publicar.');

        if ($post->requires_approval) {
            abort_unless($post->isApprovedForPublishing(), 422,
                'Post requer aprovação e ainda não foi aprovado.');
        }

        $caption = trim($post->main_caption ?? $post->content ?? '');
        abort_if(empty($caption), 422, 'Conteúdo do post está vazio.');

        abort_unless($post->hasPublicImage(), 422,
            'URL de imagem pública ausente ou inválida. Deve ser uma URL HTTPS acessível publicamente.');
    }

    // ── Media containers ───────────────────────────────────────────────────────

    public function createMediaContainer(SocialChannel $channel, string $imageUrl, string $caption): array
    {
        $this->validateChannel($channel);
        $this->assertPublicUrl($imageUrl);

        $response = Http::post("{$this->graphBase}/{$channel->instagram_user_id}/media", [
            'image_url'    => $imageUrl,
            'caption'      => $caption,
            'access_token' => $channel->access_token,
        ]);

        $this->assertApiSuccess($response, 'Falha ao criar container de mídia');

        $this->logActivity('instagram_publish_container_created', null, [
            'channel_id' => $channel->id,
            'container_id' => $response->json('id'),
        ]);

        return $response->json();
    }

    public function createCarouselItemContainer(SocialChannel $channel, string $imageUrl): array
    {
        $this->validateChannel($channel);
        $this->assertPublicUrl($imageUrl);

        $response = Http::post("{$this->graphBase}/{$channel->instagram_user_id}/media", [
            'image_url'        => $imageUrl,
            'is_carousel_item' => true,
            'access_token'     => $channel->access_token,
        ]);

        $this->assertApiSuccess($response, 'Falha ao criar item de carrossel');

        return $response->json();
    }

    public function createCarouselContainer(SocialChannel $channel, array $childrenIds, string $caption): array
    {
        $this->validateChannel($channel);

        $response = Http::post("{$this->graphBase}/{$channel->instagram_user_id}/media", [
            'media_type'   => 'CAROUSEL',
            'children'     => implode(',', $childrenIds),
            'caption'      => $caption,
            'access_token' => $channel->access_token,
        ]);

        $this->assertApiSuccess($response, 'Falha ao criar container de carrossel');

        return $response->json();
    }

    public function publishContainer(SocialChannel $channel, string $creationId): array
    {
        $this->validateChannel($channel);

        $response = Http::post("{$this->graphBase}/{$channel->instagram_user_id}/media_publish", [
            'creation_id'  => $creationId,
            'access_token' => $channel->access_token,
        ]);

        $this->assertApiSuccess($response, 'Falha ao publicar container');

        return $response->json();
    }

    public function getPublishStatus(SocialChannel $channel, string $creationId): array
    {
        $response = Http::get("{$this->graphBase}/{$creationId}", [
            'fields'       => 'status_code,status',
            'access_token' => $channel->access_token,
        ]);

        $this->assertApiSuccess($response, 'Falha ao verificar status de publicação');

        return $response->json();
    }

    // ── High-level publish ─────────────────────────────────────────────────────

    public function publishSingleImage(SocialChannel $channel, SocialPost $post): array
    {
        $this->validateChannel($channel);
        $this->validatePost($post);

        $imageUrl = $post->public_image_url;
        $caption  = $this->buildCaption($post);

        $this->logActivity('instagram_publish_started', null, [
            'channel_id' => $channel->id,
            'post_id'    => $post->id,
        ]);

        $post->markPublishing();
        $this->logActivity('social_post_marked_publishing', null, ['post_id' => $post->id]);

        $container = $this->createMediaContainer($channel, $imageUrl, $caption);
        $published = $this->publishContainer($channel, $container['id']);

        $externalId = $published['id'] ?? null;
        $post->markPublished($externalId);

        $this->logActivity('instagram_publish_success', null, [
            'channel_id'     => $channel->id,
            'post_id'        => $post->id,
            'external_post_id' => $externalId,
        ]);
        $this->logActivity('social_post_published', null, [
            'post_id'        => $post->id,
            'external_post_id' => $externalId,
        ]);

        return $published;
    }

    public function publishCarousel(SocialChannel $channel, SocialPost $post): array
    {
        // Carousel is prepared but blocked in this phase
        $message = 'Publicação de carrossel ainda não habilitada. Use imagem única nesta etapa.';
        $this->logActivity('instagram_publish_blocked', null, [
            'channel_id' => $channel->id,
            'post_id'    => $post->id,
            'reason'     => $message,
        ]);
        throw new \RuntimeException($message);
    }

    // ── Controlled blocking ────────────────────────────────────────────────────

    public function blockPublish(SocialPost $post, string $reason, ?SocialChannel $channel = null): void
    {
        $this->logActivity('instagram_publish_blocked', null, [
            'channel_id' => $channel?->id,
            'post_id'    => $post->id,
            'reason'     => $reason,
        ]);
    }

    // ── Error handling ─────────────────────────────────────────────────────────

    public function handleError(Throwable|string $error, ?SocialChannel $channel = null, ?SocialPost $post = null): void
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;
        $safe    = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $message);

        Log::error('[InstagramPublishingService] ' . $safe);

        $this->logActivity('instagram_publish_failed', null, [
            'channel_id' => $channel?->id,
            'post_id'    => $post?->id,
            'error'      => $safe,
        ]);

        if ($post) {
            $post->markFailed($safe);
            $this->logActivity('social_post_failed', null, ['post_id' => $post->id, 'error' => $safe]);
        }

        if ($channel) {
            $channel->markError($safe);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function buildCaption(SocialPost $post): string
    {
        $parts   = [];
        $caption = trim($post->main_caption ?? $post->content ?? '');
        if ($caption) $parts[] = $caption;
        if ($post->hashtags) $parts[] = $post->hashtags;
        return implode("\n\n", array_filter($parts));
    }

    private function assertPublicUrl(string $url): void
    {
        abort_unless(
            filter_var($url, FILTER_VALIDATE_URL) && str_starts_with($url, 'https://'),
            422,
            'URL de imagem inválida. Deve ser HTTPS pública.'
        );
    }

    private function assertApiSuccess(\Illuminate\Http\Client\Response $response, string $context): void
    {
        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            $safe  = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $error);
            Log::error("[InstagramPublishingService] {$context}: {$safe}");
            throw new \RuntimeException("{$context}: " . ($response->json('error.message') ?? 'Erro da API Meta'));
        }
    }

    private function logActivity(string $action, ?User $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'instagram',
                'level'       => str_contains($action, 'fail') || str_contains($action, 'block') ? 'warning' : 'info',
                'description' => "Instagram: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (\Throwable) {
            // Never break publishing flow due to log failure
        }
    }
}
