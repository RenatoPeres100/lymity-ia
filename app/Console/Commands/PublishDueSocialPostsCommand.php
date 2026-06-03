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

        // Find due posts: approved or scheduled, with valid image and approved_by set
        $due = SocialPost::whereNotNull('company_id')
            ->whereNull('client_id')
            ->whereIn('status', ['approved', 'scheduled'])
            ->where('scheduled_at', '<=', now())
            ->whereNotNull('approved_by')
            ->where('image_validation_status', 'valid')
            ->whereNotNull('public_image_url')
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

            // Guard: public image + validation
            if (!$post->hasPublicImage()) {
                $reason = "Post #{$post->id} sem public_image_url válida (HTTPS).";
                $this->warn("  SKIP #{$post->id} — {$reason}");
                $publisher->blockPublish($post, $reason, $channel);
                continue;
            }

            if ($post->image_validation_status !== 'valid') {
                $reason = "Post #{$post->id} imagem não validada (image_validation_status=" . ($post->image_validation_status ?? 'null') . ").";
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

            // Guard: outdated image — skip if post is already approved (admin reviewed content)
            if ($post->isImageOutdated() && empty($post->approved_by)) {
                $reason = "Post #{$post->id} com imagem desatualizada — texto foi editado após a imagem. Regenere a imagem.";
                $this->warn("  SKIP #{$post->id} — {$reason}");
                $publisher->blockPublish($post, $reason, $channel);
                continue;
            }

            try {
                $this->info("  PUBLISHING #{$post->id} — {$post->title}");

                // Carousel or single image
                if ($post->carousel_enabled && $post->canBePublishedAsCarousel()) {
                    $publisher->publishCarousel($channel, $post);
                    $this->info("  PUBLISHED CAROUSEL #{$post->id}");
                } else {
                    $publisher->publishSingleImage($channel, $post);
                }

                $post->refresh();
                $this->info("  PUBLISHED #{$post->id} external_id={$post->external_post_id} permalink=" . ($post->permalink ?? 'none'));
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
