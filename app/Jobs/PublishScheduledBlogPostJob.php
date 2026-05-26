<?php

namespace App\Jobs;

use App\Models\BlogPost;
use App\Services\Blog\BlogPipelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishScheduledBlogPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public readonly int $blogPostId) {}

    public function handle(BlogPipelineService $pipeline): void
    {
        $post = BlogPost::find($this->blogPostId);

        if (!$post) {
            Log::warning("[PublishScheduledBlogPostJob] Post {$this->blogPostId} não encontrado.");
            return;
        }

        if (!$post->canBePublished()) {
            Log::info("[PublishScheduledBlogPostJob] Post {$this->blogPostId} ignorado — status={$post->status}.");
            return;
        }

        Log::info("[PublishScheduledBlogPostJob] Publicando post {$post->id}: {$post->title}");

        $pipeline->publish($post);

        $post->refresh();
        Log::info("[PublishScheduledBlogPostJob] Post {$post->id} finalizado — status={$post->status}");
    }

    public function failed(Throwable $e): void
    {
        $post = BlogPost::find($this->blogPostId);
        if ($post) {
            app(BlogPipelineService::class)->failPublication($post, $e->getMessage());
        }
        Log::error("[PublishScheduledBlogPostJob] Falha fatal no post {$this->blogPostId}: {$e->getMessage()}");
    }
}
