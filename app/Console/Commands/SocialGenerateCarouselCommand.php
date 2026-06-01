<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Models\User;
use App\Services\Social\SocialImageService;
use Illuminate\Console\Command;

class SocialGenerateCarouselCommand extends Command
{
    protected $signature   = 'social:generate-carousel {post_id : ID do SocialPost}';
    protected $description = 'Gera carrossel com Gemini para um post social existente.';

    public function handle(SocialImageService $imageService): int
    {
        $post = SocialPost::find($this->argument('post_id'));

        if (!$post) {
            $this->error("Post #{$this->argument('post_id')} não encontrado.");
            return self::FAILURE;
        }

        if (empty(trim($post->main_caption ?? ''))) {
            $this->error('Post sem legenda. Crie o texto do post antes de gerar carrossel.');
            return self::FAILURE;
        }

        $this->info("Gerando carrossel para Post #{$post->id} — {$post->title}");

        $admin = User::where('email', 'admin@lymity.local')->first()
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'admin_geral'))->first()
            ?? User::first();

        try {
            $post = $imageService->generateCarouselFromPost($post, $admin);
            $count = $post->validAssets()->count();
            $this->info("  Carrossel gerado: {$count} slides válidos");
            $this->info("  Carousel status : " . $post->carousel_status);

            foreach ($post->assets as $asset) {
                $icon = $asset->isValid() ? '✓' : '✗';
                $this->line("  [{$icon}] Slide {$asset->position}: {$asset->public_url}");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Falha ao gerar carrossel: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
