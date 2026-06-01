<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Services\Social\SocialImagePromptService;
use Illuminate\Console\Command;

class SocialSuggestCarouselCommand extends Command
{
    protected $signature   = 'social:suggest-carousel {post_id : ID do SocialPost}';
    protected $description = 'Analisa se um post é melhor como carrossel ou imagem única.';

    public function handle(SocialImagePromptService $promptService): int
    {
        $post = SocialPost::find($this->argument('post_id'));

        if (!$post) {
            $this->error("Post #{$this->argument('post_id')} não encontrado.");
            return self::FAILURE;
        }

        if (empty(trim($post->main_caption ?? ''))) {
            $this->warn('Post sem legenda. Adicione texto antes de analisar.');
            return self::FAILURE;
        }

        $recommended = $promptService->shouldSuggestCarousel($post);

        if ($recommended) {
            $this->info("✓ CARROSSEL RECOMENDADO para post #{$post->id}");
            $this->info("  O conteúdo tem estrutura educativa, lista ou passo a passo.");
            $this->info("  Gere com: php artisan social:generate-carousel {$post->id}");
        } else {
            $this->info("  IMAGEM ÚNICA recomendada para post #{$post->id}");
            $this->info("  O conteúdo é mais adequado como post de imagem simples.");
            $this->info("  Gere com: php artisan social:generate-image {$post->id}");
        }

        $caption = mb_substr(trim($post->main_caption), 0, 150);
        $this->line("  Legenda: {$caption}...");

        return self::SUCCESS;
    }
}
