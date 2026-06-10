<?php

namespace App\Services\Threads;

use App\Models\ActivityLog;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ThreadsPublishingService
{
    private string $baseUrl;
    private string $graphVersion;

    public function __construct()
    {
        $this->baseUrl      = config('threads.base_url', 'https://graph.threads.net');
        $this->graphVersion = config('threads.graph_version', 'v1.0');
    }

    public function canPublish(SocialChannel $channel): bool
    {
        return $channel->isThreads()
            && $channel->isConnected()
            && $channel->hasValidToken()
            && !empty($channel->threads_user_id)
            && config('threads.publishing_enabled', false);
    }

    public function validateTextPost(SocialPost $post): void
    {
        abort_unless($post->platform === 'threads', 422,
            "Post com platform='{$post->platform}' não pode ser publicado no Threads.");
        abort_unless($post->content_type === 'threads_text', 422,
            "content_type='{$post->content_type}' não suportado nesta fase. Use 'threads_text'.");
        abort_unless(
            in_array($post->status, ['approved', 'scheduled', 'publishing']),
            422,
            "Post no status '{$post->status}' não pode ser publicado. Aprovação obrigatória."
        );
        if ($post->requires_approval) {
            abort_unless(
                !empty($post->approved_by) && !empty($post->approved_at),
                422,
                'Post requer aprovação formal mas ainda não foi aprovado.'
            );
        }
        $text = trim($post->main_caption ?? '');
        abort_if(empty($text), 422, 'Texto do post está vazio. Preencha o conteúdo antes de publicar.');
    }

    public function createTextContainer(SocialChannel $channel, SocialPost $post): array
    {
        $text = $this->buildPostText($post);

        $response = Http::post("{$this->baseUrl}/{$this->graphVersion}/{$channel->threads_user_id}/threads", [
            'media_type'   => 'TEXT',
            'text'         => $text,
            'access_token' => $channel->access_token,
        ]);

        if ($response->failed()) {
            $error = $this->sanitizeApiError($response);
            Log::error("[ThreadsPublishing] createTextContainer failed for post #{$post->id}: {$error}");
            throw new \RuntimeException("Falha ao criar container Threads: {$error}");
        }

        $data = $response->json();
        abort_unless(!empty($data['id']), 422, 'API Threads não retornou ID do container.');

        $this->logActivity('threads.post.publishing_started', null, [
            'post_id'      => $post->id,
            'channel_id'   => $channel->id,
            'container_id' => $data['id'],
        ]);

        return $data;
    }

    public function publishContainer(SocialChannel $channel, string $creationId): array
    {
        $response = Http::post("{$this->baseUrl}/{$this->graphVersion}/{$channel->threads_user_id}/threads_publish", [
            'creation_id'  => $creationId,
            'access_token' => $channel->access_token,
        ]);

        if ($response->failed()) {
            $error = $this->sanitizeApiError($response);
            Log::error("[ThreadsPublishing] publishContainer failed for container {$creationId}: {$error}");
            throw new \RuntimeException("Falha ao publicar container Threads: {$error}");
        }

        return $response->json();
    }

    public function publishTextPost(SocialPost $post): array
    {
        abort_unless(config('threads.publishing_enabled', false), 422,
            'Publicação Threads desabilitada. Configure THREADS_PUBLISHING_ENABLED=true no .env após validar a conexão.');

        $channel = $this->resolveChannel($post);
        $this->validateTextPost($post);

        $post->update(['status' => 'publishing']);
        $this->logActivity('threads.post.publishing_started', null, ['post_id' => $post->id, 'channel_id' => $channel->id]);

        // Step 1: Create text container
        $container   = $this->createTextContainer($channel, $post);
        $containerId = $container['id'];

        // Step 2: Publish container
        $published  = $this->publishContainer($channel, $containerId);
        $externalId = $published['id'] ?? null;

        // Step 3: Update post
        $post->update([
            'status'            => 'published',
            'published_at'      => now(),
            'external_post_id'  => $externalId,
            'publication_error' => null,
        ]);

        $this->logActivity('threads.post.published', null, [
            'post_id'          => $post->id,
            'channel_id'       => $channel->id,
            'external_post_id' => $externalId,
        ]);

        return array_merge($published, ['container_id' => $containerId]);
    }

    public function handleError(Throwable $e, SocialPost $post): void
    {
        $safe = $this->sanitizeError($e);
        Log::error('[ThreadsPublishing] ' . $safe);

        $post->update([
            'status'            => 'failed',
            'publication_error' => $safe,
        ]);

        $this->logActivity('threads.post.failed', null, [
            'post_id' => $post->id,
            'error'   => $safe,
        ]);
    }

    public function sanitizeResponse(array $response): array
    {
        // Never return token-like keys
        return array_filter($response, fn($k) => !in_array($k, ['access_token', 'token']), ARRAY_FILTER_USE_KEY);
    }

    public function blockPublish(SocialPost $post, string $reason): void
    {
        $this->logActivity('threads.post.publish_blocked', null, [
            'post_id' => $post->id,
            'reason'  => $reason,
        ]);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function resolveChannel(SocialPost $post): SocialChannel
    {
        $channel = null;

        if ($post->social_channel_id) {
            $channel = SocialChannel::find($post->social_channel_id);
        }

        if (!$channel) {
            $channel = SocialChannel::where('platform', 'threads')
                ->where('company_id', $post->company_id)
                ->whereNull('client_id')
                ->first();
        }

        abort_unless($channel, 422, 'Canal Threads não encontrado. Configure em /admin/social/threads.');
        abort_unless($channel->isConnected() && $channel->hasValidToken(), 422,
            'Canal Threads não está conectado ou token expirado. Reconecte em /admin/social/threads.');
        abort_unless(!empty($channel->threads_user_id), 422,
            'threads_user_id ausente. Reconecte o canal Threads para obter o ID.');

        return $channel;
    }

    private function buildPostText(SocialPost $post): string
    {
        $parts = [];
        $text  = trim($post->main_caption ?? '');
        if ($text) $parts[] = $text;
        if ($post->hashtags) {
            $tags = trim($post->hashtags);
            if ($tags && !str_contains($text, '#')) {
                $parts[] = $tags;
            }
        }
        return implode("\n\n", array_filter($parts));
    }

    private function sanitizeApiError(\Illuminate\Http\Client\Response $response): string
    {
        $error = $response->json('error.message') ?? $response->json('error_message') ?? $response->body();
        return preg_replace('/THQA[A-Za-z0-9_\-]+|EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', (string) $error);
    }

    private function sanitizeError(Throwable|string $e): string
    {
        $msg = $e instanceof Throwable ? $e->getMessage() : $e;
        return preg_replace('/THQA[A-Za-z0-9_\-]+|EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $msg);
    }

    private function logActivity(string $action, ?User $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'threads',
                'level'       => str_contains($action, 'fail') || str_contains($action, 'block') ? 'warning' : 'info',
                'description' => "Threads: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (Throwable) {}
    }
}
