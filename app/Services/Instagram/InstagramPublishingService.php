<?php

namespace App\Services\Instagram;

use App\Models\ActivityLog;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\SocialPostAsset;
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
        if (empty($channel->facebook_page_id))                        return false;

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
            'Publicação Instagram desabilitada no .env. Configure INSTAGRAM_PUBLISHING_ENABLED=true após validar a conexão.'
        );
        abort_unless($channel->isConnected(), 422,
            'Canal Instagram ainda não conectado. Acesse /admin/social/instagram e clique em Conectar Instagram.');
        abort_unless(!empty($channel->getRawOriginal('access_token')), 422,
            'Token de acesso ausente. Reconecte o canal Instagram em /admin/social/instagram.');
        abort_unless(!$channel->isExpired(), 422,
            'Token expirado. Reconecte o canal Instagram em /admin/social/instagram.');
        abort_unless(!empty($channel->instagram_user_id), 422,
            'Instagram User ID ausente. Reconecte o canal Instagram para obter o ID.');
        abort_unless(!empty($channel->facebook_page_id), 422,
            'Facebook Page ID ausente. Reconecte o canal Instagram para obter o ID da página.');
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

    public function getContainerStatus(SocialChannel $channel, string $creationId): array
    {
        return $this->getPublishStatus($channel, $creationId);
    }

    public function waitUntilContainerFinished(SocialChannel $channel, string $creationId, int $maxAttempts = 10): array
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $status = $this->getContainerStatus($channel, $creationId);
            $code   = $status['status_code'] ?? null;

            if ($code === 'FINISHED') {
                return $status;
            }

            if ($code === 'ERROR') {
                throw new \RuntimeException("Container de mídia retornou ERROR. Container ID: {$creationId}");
            }

            if ($i < $maxAttempts - 1) {
                sleep(3);
            }
        }

        throw new \RuntimeException("Container de mídia não ficou FINISHED após {$maxAttempts} tentativas.");
    }

    public function getPublishedMedia(SocialChannel $channel, string $mediaId): array
    {
        $response = Http::get("{$this->graphBase}/{$mediaId}", [
            'fields'       => 'id,permalink,caption,media_type,timestamp',
            'access_token' => $channel->access_token,
        ]);

        if ($response->failed()) {
            Log::warning("[InstagramPublishingService] Could not fetch media {$mediaId}: HTTP {$response->status()}");
            return ['id' => $mediaId];
        }

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

        // Step 1: Create container
        $container    = $this->createMediaContainer($channel, $imageUrl, $caption);
        $containerId  = $container['id'];

        // Save container ID
        $post->update(['instagram_container_id' => $containerId]);

        $this->logActivity('instagram_container_created', null, [
            'channel_id'   => $channel->id,
            'post_id'      => $post->id,
            'container_id' => $containerId,
        ]);

        // Step 2: Wait for FINISHED
        $this->waitUntilContainerFinished($channel, $containerId);

        // Step 3: Publish container
        $published  = $this->publishContainer($channel, $containerId);
        $externalId = $published['id'] ?? null;

        // Step 4: Fetch permalink
        $permalink = null;
        if ($externalId) {
            try {
                $media     = $this->getPublishedMedia($channel, $externalId);
                $permalink = $media['permalink'] ?? null;
            } catch (\Throwable $e) {
                Log::warning("[InstagramPublishingService] Could not fetch permalink for {$externalId}: {$e->getMessage()}");
            }
        }

        $post->markPublished($externalId, $permalink);

        $this->logActivity('instagram_publish_success', null, [
            'channel_id'      => $channel->id,
            'post_id'         => $post->id,
            'external_post_id'=> $externalId,
            'permalink'       => $permalink,
        ]);
        $this->logActivity('social_post_published', null, [
            'post_id'         => $post->id,
            'external_post_id'=> $externalId,
            'permalink'       => $permalink,
        ]);

        return array_merge($published, ['permalink' => $permalink]);
    }

    public function publishCarousel(SocialChannel $channel, SocialPost $post): array
    {
        $this->validateChannel($channel);
        $this->validatePost($post);

        $validAssets = $post->validAssets()->get();
        $min         = config('social.carousel.min_slides', 3);

        if ($validAssets->count() < $min) {
            throw new \RuntimeException(
                "Carrossel precisa de ao menos {$min} assets válidos. "
                . "Encontrado: {$validAssets->count()}. "
                . "Gere ou valide os slides antes de publicar."
            );
        }

        $caption = $this->buildCaption($post);

        $this->logActivity('instagram_carousel_publish_started', null, [
            'channel_id' => $channel->id,
            'post_id'    => $post->id,
            'slides'     => $validAssets->count(),
        ]);

        $post->markPublishing();

        // Step 1: Create a container for each carousel item
        $childIds = [];
        foreach ($validAssets as $asset) {
            $this->assertPublicUrl($asset->public_url);
            $itemContainer  = $this->createCarouselItemContainer($channel, $asset->public_url);
            $itemContainerId = $itemContainer['id'] ?? null;

            if (!$itemContainerId) {
                throw new \RuntimeException("Falha ao criar container para slide #{$asset->position}.");
            }

            $asset->update(['instagram_container_id' => $itemContainerId, 'status' => 'publishing']);

            $this->waitUntilContainerFinished($channel, $itemContainerId);

            $childIds[] = $itemContainerId;
        }

        // Step 2: Create carousel container
        $carouselContainer   = $this->createCarouselContainer($channel, $childIds, $caption);
        $carouselContainerId = $carouselContainer['id'] ?? null;

        if (!$carouselContainerId) {
            throw new \RuntimeException('Falha ao criar container de carrossel pai.');
        }

        $post->update(['instagram_container_id' => $carouselContainerId]);

        $this->waitUntilContainerFinished($channel, $carouselContainerId);

        // Step 3: Publish carousel
        $published  = $this->publishContainer($channel, $carouselContainerId);
        $externalId = $published['id'] ?? null;

        // Step 4: Fetch permalink
        $permalink = null;
        if ($externalId) {
            try {
                $media     = $this->getPublishedMedia($channel, $externalId);
                $permalink = $media['permalink'] ?? null;
            } catch (\Throwable $e) {
                Log::warning("[InstagramPublishingService] Could not fetch carousel permalink: {$e->getMessage()}");
            }
        }

        $post->markPublished($externalId, $permalink);

        // Mark all assets as published
        $post->assets()->whereIn('status', ['valid', 'publishing'])->update(['status' => 'published']);

        $this->logActivity('instagram_carousel_publish_success', null, [
            'channel_id'      => $channel->id,
            'post_id'         => $post->id,
            'external_post_id'=> $externalId,
            'permalink'       => $permalink,
            'slides'          => count($childIds),
        ]);

        return array_merge($published, ['permalink' => $permalink]);
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
