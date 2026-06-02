<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\SocialPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ContentFixApprovedScheduledCommand extends Command
{
    protected $signature   = 'content:fix-approved-scheduled';
    protected $description = 'Corrige conteúdos aprovados com scheduled_at futuro para status=scheduled.';

    public function handle(): int
    {
        $this->info('CONTENT_FIX_APPROVED_SCHEDULED_STARTED');

        $blogFixed   = 0;
        $socialFixed = 0;
        $socialSkipped = 0;

        // Fix BlogPosts
        $blogPosts = BlogPost::where('status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->get();

        foreach ($blogPosts as $post) {
            $post->update(['status' => 'scheduled']);
            $blogFixed++;

            ActivityLog::create([
                'action'      => 'blog_post_fix_approved_to_scheduled',
                'module'      => 'blog',
                'description' => "Post de blog #{$post->id} corrigido para scheduled (scheduled_at: {$post->scheduled_at->format('d/m/Y H:i')})",
                'metadata'    => ['blog_post_id' => $post->id, 'scheduled_at' => $post->scheduled_at->toIso8601String()],
            ]);

            Log::info("[content:fix-approved-scheduled] BlogPost #{$post->id} fixed to scheduled.");
        }

        // Fix SocialPosts
        $socialPosts = SocialPost::where('status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->get();

        foreach ($socialPosts as $post) {
            $imageValid = $post->image_validation_status === 'valid' && !empty($post->public_image_url);

            if ($imageValid) {
                $post->update(['status' => 'scheduled']);
                $socialFixed++;

                ActivityLog::create([
                    'client_id'   => $post->client_id,
                    'action'      => 'social_post_fix_approved_to_scheduled',
                    'module'      => 'social',
                    'description' => "Post social #{$post->id} corrigido para scheduled (scheduled_at: {$post->scheduled_at->format('d/m/Y H:i')})",
                    'metadata'    => ['social_post_id' => $post->id, 'scheduled_at' => $post->scheduled_at->toIso8601String()],
                ]);

                Log::info("[content:fix-approved-scheduled] SocialPost #{$post->id} fixed to scheduled.");
            } else {
                $socialSkipped++;
                Log::warning("[content:fix-approved-scheduled] SocialPost #{$post->id} skipped — invalid image (status: {$post->image_validation_status}).");
            }
        }

        $this->line("BLOG_FIXED={$blogFixed}");
        $this->line("SOCIAL_FIXED={$socialFixed}");
        $this->line("SOCIAL_SKIPPED_INVALID_IMAGE={$socialSkipped}");
        $this->info('CONTENT_FIX_APPROVED_SCHEDULED_FINISHED');

        return self::SUCCESS;
    }
}
