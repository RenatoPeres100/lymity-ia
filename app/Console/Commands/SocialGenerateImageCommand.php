<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Models\User;
use App\Services\Social\SocialImageService;
use Illuminate\Console\Command;

class SocialGenerateImageCommand extends Command
{
    protected $signature   = 'social:generate-image {post_id : ID do SocialPost}';
    protected $description = 'Gera imagem com Gemini para um post social existente.';

    public function handle(SocialImageService $imageService): int
    {
        $post = SocialPost::find($this->argument('post_id'));

        if (!$post) {
            $this->error("Post #{$this->argument('post_id')} não encontrado.");
            return self::FAILURE;
        }

        $this->info("Gerando imagem com Gemini para Post #{$post->id} — {$post->title}");

        $admin = User::where('email', 'admin@lymity.local')->first()
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'admin_geral'))->first()
            ?? User::first();

        if (!$admin) {
            $this->error('Nenhum usuário admin encontrado.');
            return self::FAILURE;
        }

        try {
            $post = $imageService->generateWithGemini($post, $admin);
            $this->info("  Imagem gerada: " . $post->public_image_url);
            $this->info("  Status imagem: " . $post->image_status);
            $this->info("  Validação    : " . ($post->image_validation_status ?? '—'));
            if ($post->image_validation_error) {
                $this->warn("  Aviso validação: " . $post->image_validation_error);
            }
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Falha ao gerar imagem: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
