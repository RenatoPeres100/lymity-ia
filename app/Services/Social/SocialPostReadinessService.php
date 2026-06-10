<?php

namespace App\Services\Social;

use App\Models\SocialPost;

class SocialPostReadinessService
{
    public function __construct(private SocialPostImageResolver $imageResolver) {}

    public function canBeScheduled(SocialPost $post): array
    {
        $reasons = $this->getBlockingReasons($post, 'schedule');
        return [
            'ready'   => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    public function canBePublished(SocialPost $post): array
    {
        $reasons = $this->getBlockingReasons($post, 'publish');
        return [
            'ready'   => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    public function hasRequiredImage(SocialPost $post): bool
    {
        if (!$this->requiresImage($post)) {
            return true;
        }

        $url = $this->imageResolver->resolveUrl($post);
        return $this->imageResolver->isValidPublicHttps($url);
    }

    public function getBlockingReasons(SocialPost $post, string $action = 'publish'): array
    {
        $reasons = [];

        if (empty(trim($post->main_caption ?? ''))) {
            $reasons[] = 'Legenda obrigatória ausente.';
        }

        if ($this->requiresImage($post)) {
            $url = $this->imageResolver->resolveUrl($post);

            if (!$url) {
                $reasons[] = 'Imagem obrigatória ausente. Gere ou adicione imagem antes de ' .
                    ($action === 'schedule' ? 'agendar.' : 'publicar.');
            } elseif (!$this->imageResolver->isValidPublicHttps($url)) {
                $reasons[] = 'A imagem precisa ser uma URL HTTPS pública (não localhost/interno). ' .
                    "URL atual: {$url}";
            }
        }

        if ($action === 'publish') {
            if (empty($post->approved_by)) {
                $reasons[] = 'Post não foi aprovado.';
            }
        }

        return $reasons;
    }

    private function requiresImage(SocialPost $post): bool
    {
        if ($post->carousel_enabled) {
            return false; // carousel uses assets, not single image
        }

        return in_array($post->content_type, ['feed', 'reels', 'story', null, '']);
    }
}
