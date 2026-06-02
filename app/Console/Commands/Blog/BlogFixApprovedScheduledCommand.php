<?php

namespace App\Console\Commands\Blog;

use App\Models\ActivityLog;
use App\Models\BlogPost;
use Illuminate\Console\Command;

class BlogFixApprovedScheduledCommand extends Command
{
    protected $signature   = 'blog:fix-approved-scheduled';
    protected $description = 'Corrige posts de blog aprovados com scheduled_at futuro para status=scheduled.';

    public function handle(): int
    {
        $posts = BlogPost::where('status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->get();

        $fixed = 0;

        foreach ($posts as $post) {
            $post->update(['status' => 'scheduled']);
            $fixed++;

            ActivityLog::create([
                'action'      => 'blog_post_fix_approved_to_scheduled',
                'module'      => 'blog',
                'description' => "Post de blog #{$post->id} corrigido para scheduled (scheduled_at: {$post->scheduled_at->format('d/m/Y H:i')})",
                'metadata'    => ['blog_post_id' => $post->id],
            ]);
        }

        $this->line("BLOG_FIXED={$fixed}");
        $this->info("blog:fix-approved-scheduled concluído. {$fixed} post(s) corrigido(s).");

        return self::SUCCESS;
    }
}
