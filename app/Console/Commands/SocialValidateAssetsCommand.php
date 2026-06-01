<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Services\Social\SocialImageService;
use Illuminate\Console\Command;

class SocialValidateAssetsCommand extends Command
{
    protected $signature   = 'social:validate-assets {post_id : ID do SocialPost}';
    protected $description = 'Valida todos os assets de carrossel de um post.';

    public function handle(SocialImageService $imageService): int
    {
        $post = SocialPost::find($this->argument('post_id'));

        if (!$post) {
            $this->error("Post #{$this->argument('post_id')} não encontrado.");
            return self::FAILURE;
        }

        if ($post->assets()->count() === 0) {
            $this->warn("Post #{$post->id} não tem assets de carrossel.");
            return self::FAILURE;
        }

        $this->info("Validando assets do Post #{$post->id}...");

        $post = $imageService->validateCarouselAssets($post);

        foreach ($post->assets as $asset) {
            $icon = $asset->isValid() ? '✓' : '✗';
            $this->line("  [{$icon}] Slide {$asset->position} ({$asset->status}): {$asset->public_url}");
            if ($asset->validation_error) {
                $this->warn("       → " . $asset->validation_error);
            }
        }

        $this->info("Carousel status: " . ($post->carousel_status ?? '—'));
        $this->info("Válidos: " . $post->validAssets()->count() . "/" . $post->assets()->count());

        return self::SUCCESS;
    }
}
