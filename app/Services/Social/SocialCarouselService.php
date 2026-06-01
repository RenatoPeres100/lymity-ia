<?php

namespace App\Services\Social;

use App\Models\SocialPost;
use App\Models\SocialPostAsset;
use App\Models\User;

class SocialCarouselService
{
    public function __construct(
        private SocialImageService       $imageService,
        private SocialImagePromptService $promptService,
    ) {}

    public function shouldSuggestCarousel(SocialPost $post): bool
    {
        return $this->promptService->shouldSuggestCarousel($post);
    }

    public function generateCarouselPlan(SocialPost $post): array
    {
        // Returns the slides plan via the AI provider
        // Actual generation is in SocialImageService::generateCarouselFromPost
        return [
            'recommended' => $this->shouldSuggestCarousel($post),
            'min_slides'  => config('social.carousel.min_slides', 3),
            'max_slides'  => config('social.carousel.max_slides', 5),
        ];
    }

    public function generateCarouselImages(SocialPost $post, User $user): SocialPost
    {
        return $this->imageService->generateCarouselFromPost($post, $user);
    }

    public function validateCarousel(SocialPost $post): bool
    {
        $this->imageService->validateCarouselAssets($post);
        $post->refresh();

        $min = config('social.carousel.min_slides', 3);
        $validCount = $post->validAssets()->count();

        return $post->carousel_status === 'valid' && $validCount >= $min;
    }

    public function getCarouselStatus(SocialPost $post): array
    {
        $total  = $post->assets()->count();
        $valid  = $post->validAssets()->count();
        $min    = config('social.carousel.min_slides', 3);

        return [
            'enabled'        => (bool) $post->carousel_enabled,
            'status'         => $post->carousel_status ?? 'none',
            'total_slides'   => $total,
            'valid_slides'   => $valid,
            'min_required'   => $min,
            'ready'          => $post->canBePublishedAsCarousel(),
        ];
    }
}
