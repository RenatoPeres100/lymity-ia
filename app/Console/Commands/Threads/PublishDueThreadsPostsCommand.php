<?php

namespace App\Console\Commands\Threads;

use App\Jobs\PublishThreadsPostJob;
use App\Models\SocialPost;
use Illuminate\Console\Command;

class PublishDueThreadsPostsCommand extends Command
{
    protected $signature   = 'threads:publish-due';
    protected $description = 'Publica posts Threads agendados para o horário atual ou anterior.';

    public function handle(): int
    {
        $this->line('THREADS_PUBLISH_STARTED');

        if (!config('threads.publishing_enabled', false)) {
            $this->warn('THREADS_PUBLISHING_ENABLED=false — publicação bloqueada. Nenhum post será publicado.');
            $this->line('DUE_POSTS=0 PUBLISHED=0 FAILED=0');
            $this->line('THREADS_PUBLISH_FINISHED');
            return self::SUCCESS;
        }

        $due = SocialPost::where('platform', 'threads')
            ->where('content_type', 'threads_text')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->whereNotNull('approved_by')
            ->get();

        $this->line("DUE_POSTS={$due->count()}");

        if ($due->isEmpty()) {
            $this->line('Nenhum post Threads agendado e devido para publicação.');
            $this->line('PUBLISHED=0 FAILED=0');
            $this->line('THREADS_PUBLISH_FINISHED');
            return self::SUCCESS;
        }

        $dispatched = 0;
        foreach ($due as $post) {
            PublishThreadsPostJob::dispatch($post->id);
            $this->info("  DISPATCHED #{$post->id} — {$post->title}");
            $dispatched++;
        }

        $this->line("DISPATCHED={$dispatched}");
        $this->line('THREADS_PUBLISH_FINISHED');

        return self::SUCCESS;
    }
}
