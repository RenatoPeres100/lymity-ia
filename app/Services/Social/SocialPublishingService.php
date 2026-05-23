<?php

namespace App\Services\Social;

use App\Models\ActivityLog;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SocialPublishingService
{
    public function validateChannel(SocialChannel $channel): bool
    {
        return $channel->isActive()
            && ($channel->client_id || $channel->company_id);
    }

    public function schedule(SocialPost $post, Carbon|string $date): SocialPost
    {
        $scheduledAt = $date instanceof Carbon ? $date : Carbon::parse($date);

        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);

        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'client_id'   => $post->client_id,
                'action'      => 'social_post_scheduled',
                'module'      => 'social_media',
                'description' => "Post #{$post->id} agendado para {$scheduledAt->format('d/m/Y H:i')}",
                'metadata'    => ['social_post_id' => $post->id, 'scheduled_at' => $scheduledAt->toIso8601String()],
            ]);
        }

        return $post->fresh();
    }

    public function publish(SocialPost $post): SocialPost
    {
        throw new \RuntimeException(
            'Publicação real ainda não está habilitada nesta fase. ' .
            'Use schedule() para agendar ou markAsPublished() para marcar manualmente. ' .
            'Integração com APIs de redes sociais será implementada na Fase 7.'
        );
    }

    public function markAsPublished(SocialPost $post, ?User $user = null): SocialPost
    {
        $post->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id'     => $user?->id ?? Auth::id(),
                'client_id'   => $post->client_id,
                'action'      => 'social_post_published',
                'module'      => 'social_media',
                'description' => "Post #{$post->id} marcado como publicado manualmente.",
                'metadata'    => ['social_post_id' => $post->id],
            ]);
        }

        return $post->fresh();
    }
}
