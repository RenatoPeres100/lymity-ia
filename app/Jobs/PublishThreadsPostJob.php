<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Services\Threads\ThreadsPublishingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishThreadsPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(public int $postId) {}

    public function handle(ThreadsPublishingService $publisher): void
    {
        $post = SocialPost::find($this->postId);

        if (!$post) {
            Log::warning("[PublishThreadsPostJob] Post #{$this->postId} não encontrado.");
            return;
        }

        if ($post->platform !== 'threads') {
            Log::warning("[PublishThreadsPostJob] Post #{$this->postId} não é Threads (platform={$post->platform}).");
            return;
        }

        if (!in_array($post->status, ['scheduled', 'approved'])) {
            Log::info("[PublishThreadsPostJob] Post #{$this->postId} status={$post->status} — ignorado.");
            return;
        }

        if (!config('threads.publishing_enabled', false)) {
            $reason = 'THREADS_PUBLISHING_ENABLED=false — publicação bloqueada.';
            $publisher->blockPublish($post, $reason);
            $post->update(['publication_error' => $reason]);
            return;
        }

        try {
            $publisher->publishTextPost($post);
            Log::info("[PublishThreadsPostJob] Post #{$this->postId} publicado com sucesso.");
        } catch (Throwable $e) {
            $publisher->handleError($e, $post);
            Log::error("[PublishThreadsPostJob] Post #{$this->postId} falhou: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $post = SocialPost::find($this->postId);
        if ($post) {
            $safe = preg_replace('/THQA[A-Za-z0-9_\-]+|EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $e->getMessage());
            $post->update(['status' => 'failed', 'publication_error' => $safe]);
        }
        Log::error("[PublishThreadsPostJob] Job falhou definitivamente para post #{$this->postId}: " . $e->getMessage());
    }
}
