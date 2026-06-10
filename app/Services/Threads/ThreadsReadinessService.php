<?php

namespace App\Services\Threads;

use App\Models\SocialChannel;
use App\Models\SocialPost;

class ThreadsReadinessService
{
    public function canBeScheduled(SocialPost $post): array
    {
        $reasons = $this->getBlockingReasons($post, 'schedule');
        return ['ready' => empty($reasons), 'reasons' => $reasons];
    }

    public function canBePublished(SocialPost $post): array
    {
        $reasons = $this->getBlockingReasons($post, 'publish');
        return ['ready' => empty($reasons), 'reasons' => $reasons];
    }

    public function getBlockingReasons(SocialPost $post, string $action = 'publish'): array
    {
        $reasons = [];

        if ($post->platform !== 'threads') {
            $reasons[] = "platform='{$post->platform}' não é threads";
        }

        if ($post->content_type !== 'threads_text') {
            $reasons[] = "content_type='{$post->content_type}' não é threads_text";
        }

        if (!$this->hasValidText($post)) {
            $reasons[] = 'Texto do post está vazio';
        }

        if ($action === 'publish') {
            if (!in_array($post->status, ['approved', 'scheduled', 'publishing'])) {
                $reasons[] = "Status '{$post->status}' não permite publicação (precisa ser approved ou scheduled)";
            }
            if ($post->requires_approval && (empty($post->approved_by) || empty($post->approved_at))) {
                $reasons[] = 'Requer aprovação mas ainda não foi aprovado';
            }
            if (!$this->hasConnectedChannel($post)) {
                $reasons[] = 'Canal Threads não conectado';
            }
            if (!config('threads.publishing_enabled', false)) {
                $reasons[] = 'THREADS_PUBLISHING_ENABLED=false — publicação bloqueada';
            }
        }

        if ($action === 'schedule') {
            if (!in_array($post->status, ['draft', 'pending_approval', 'approved'])) {
                $reasons[] = "Status '{$post->status}' não permite agendamento";
            }
        }

        return $reasons;
    }

    public function hasValidText(SocialPost $post): bool
    {
        return !empty(trim($post->main_caption ?? ''));
    }

    public function hasConnectedChannel(SocialPost $post): bool
    {
        if ($post->social_channel_id) {
            $channel = SocialChannel::find($post->social_channel_id);
            return $channel && $channel->isConnected() && $channel->hasValidToken();
        }

        $channel = SocialChannel::where('platform', 'threads')
            ->where('company_id', $post->company_id)
            ->whereNull('client_id')
            ->first();

        return $channel && $channel->isConnected() && $channel->hasValidToken();
    }
}
