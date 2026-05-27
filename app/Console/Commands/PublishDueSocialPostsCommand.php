<?php

namespace App\Console\Commands;

use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Services\Instagram\InstagramPublishingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishDueSocialPostsCommand extends Command
{
    protected $signature   = 'social:publish-due';
    protected $description = 'Publica posts sociais da agência agendados para o horário atual ou anterior.';

    public function handle(InstagramPublishingService $publisher): int
    {
        $this->line('SOCIAL_PUBLISH_STARTED');

        // Get agency Instagram channel
        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if (!$channel) {
            $this->warn('Canal @lymity.ia não encontrado. Configure em /admin/social/instagram.');
            $this->line('DUE_POSTS=0');
            $this->line('PUBLISHED=0');
            $this->line('FAILED=0');
            $this->line('SOCIAL_PUBLISH_FINISHED');
            return self::SUCCESS;
        }

        if (!config('meta.instagram_publishing_enabled', false)) {
            $this->warn('INSTAGRAM_PUBLISHING_ENABLED=false — publicação bloqueada. Nenhum post será publicado.');
            $this->line('DUE_POSTS=0');
            $this->line('PUBLISHED=0');
            $this->line('FAILED=0');
            $this->line('SOCIAL_PUBLISH_FINISHED');
            return self::SUCCESS;
        }

        // Find due posts: agency, scheduled, feed/feed_image, past scheduled_at
        $due = SocialPost::whereNotNull('company_id')
            ->whereNull('client_id')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->where(function ($q) {
                $q->where('content_type', 'feed')
                  ->orWhere('creative_format', 'feed_image');
            })
            ->get();

        $this->line("DUE_POSTS={$due->count()}");

        if ($due->isEmpty()) {
            $this->line('Nenhum post social agendado e devido para publicação.');
            $this->line('PUBLISHED=0');
            $this->line('FAILED=0');
            $this->line('SOCIAL_PUBLISH_FINISHED');
            return self::SUCCESS;
        }

        $published = 0;
        $failed    = 0;

        foreach ($due as $post) {
            // Guard: approval
            if ($post->requires_approval && !$post->isApprovedForPublishing()) {
                $reason = "Post #{$post->id} requer aprovação e não foi aprovado.";
                $this->warn("  SKIP #{$post->id} — {$reason}");
                $publisher->blockPublish($post, $reason, $channel);
                continue;
            }

            // Guard: public image
            if (!$post->hasPublicImage()) {
                $reason = "Post #{$post->id} sem public_image_url válida (HTTPS).";
                $this->warn("  SKIP #{$post->id} — {$reason}");
                $publisher->blockPublish($post, $reason, $channel);
                continue;
            }

            // Guard: channel connected
            if (!$channel->isConnected() || !$channel->hasValidToken()) {
                $reason = 'Canal Instagram não conectado ou token inválido.';
                $this->warn("  SKIP #{$post->id} — {$reason}");
                $publisher->blockPublish($post, $reason, $channel);
                $post->markFailed($reason);
                $failed++;
                continue;
            }

            try {
                $this->info("  PUBLISHING #{$post->id} — {$post->title}");
                $publisher->publishSingleImage($channel, $post);
                $post->refresh();
                $this->info("  PUBLISHED #{$post->id} external_id={$post->external_post_id}");
                $published++;
            } catch (\Throwable $e) {
                $this->error("  FAILED #{$post->id} — {$e->getMessage()}");
                $publisher->handleError($e, $channel, $post);
                $failed++;
                Log::error("[social:publish-due] Post #{$post->id} failed: " . $e->getMessage());
            }
        }

        $this->line("PUBLISHED={$published}");
        $this->line("FAILED={$failed}");
        $this->line('SOCIAL_PUBLISH_FINISHED');

        return self::SUCCESS;
    }
}
