<?php

namespace App\Console\Commands;

use App\Jobs\PublishScheduledBlogPostJob;
use App\Models\BlogPost;
use App\Services\Blog\BlogPipelineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishDueBlogPostsCommand extends Command
{
    protected $signature   = 'blog:publish-due';
    protected $description = 'Publica posts de blog da agência agendados para o horário atual ou anterior.';

    public function handle(BlogPipelineService $pipeline): int
    {
        $due = BlogPost::agency()
            ->dueForPublishing()
            ->get();

        if ($due->isEmpty()) {
            $this->line('Nenhum post devido para publicação.');
            return self::SUCCESS;
        }

        $this->info("Encontrados {$due->count()} post(s) para publicar.");

        $published = 0;
        $failed    = 0;

        foreach ($due as $post) {
            // Double-check: never publish without approval
            if (!$post->canBePublished()) {
                $this->warn("  SKIP #{$post->id} {$post->title} — status inválido: {$post->status}");
                continue;
            }

            try {
                if (config('queue.default') === 'sync') {
                    $pipeline->publish($post);
                    $post->refresh();
                    if ($post->isPublished()) {
                        $this->info("  PUBLISHED #{$post->id} {$post->title}");
                        $published++;
                    } else {
                        $this->error("  FAILED #{$post->id} {$post->title} — {$post->publication_error}");
                        $failed++;
                    }
                } else {
                    PublishScheduledBlogPostJob::dispatch($post->id);
                    $this->info("  QUEUED #{$post->id} {$post->title}");
                    $published++;
                }
            } catch (\Throwable $e) {
                $pipeline->failPublication($post, $e->getMessage());
                $this->error("  FAILED #{$post->id} {$post->title} — {$e->getMessage()}");
                $failed++;
                Log::error("[blog:publish-due] Post #{$post->id}: {$e->getMessage()}");
            }
        }

        $this->line("Resultado: {$published} publicados/enfileirados, {$failed} com falha.");
        return self::SUCCESS;
    }
}
