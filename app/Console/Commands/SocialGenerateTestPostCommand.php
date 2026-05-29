<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\Social\SocialImageService;
use App\Services\Social\SocialPostService;
use Illuminate\Console\Command;

class SocialGenerateTestPostCommand extends Command
{
    protected $signature   = 'social:generate-test-post';
    protected $description = 'Cria um post de teste da agência, gera legenda e imagem com Gemini, valida — sem publicar.';

    public function handle(SocialPostService $postService, SocialImageService $imageService): int
    {
        $this->info('=== SOCIAL GENERATE TEST POST ===');

        $admin = User::where('email', 'admin@lymity.local')->first()
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'admin_geral'))->first()
            ?? User::first();

        if (!$admin) {
            $this->error('Nenhum usuário admin encontrado.');
            return self::FAILURE;
        }

        $company = Company::first();

        $post = $postService->createDraft([
            'title'          => 'Post de Teste — Lymity IA [' . now()->format('d/m H:i') . ']',
            'objective'      => 'authority',
            'content_type'   => 'feed',
            'creative_format'=> 'feed_image',
            'creative_brief' => 'Destaque como a Lymity IA transforma pequenas e médias empresas com automação inteligente, levando mais resultados com menos esforço manual. Mostre o poder da IA aplicada ao crescimento real de negócios.',
            'company_id'     => $company?->id,
            'requires_approval' => true,
            'metadata'       => [
                'target_audience' => 'empreendedores e gestores digitais',
                'tone'            => 'profissional, inspirador e direto',
                'desired_cta'     => 'Fale com a Lymity IA',
            ],
        ], $admin);

        $this->info("  Post criado: #{$post->id} — {$post->title}");

        // Generate caption
        $this->line('  Gerando legenda com Gemini...');
        try {
            $post = $postService->generateCaptionWithGemini($post, $admin);
            $this->info('  Legenda gerada: OK');
            $this->line('  Caption: ' . mb_substr($post->main_caption ?? '', 0, 120) . '...');
        } catch (\Throwable $e) {
            $this->warn("  Legenda: FALHOU — {$e->getMessage()}");
        }

        // Generate image
        $this->line('  Gerando imagem com Gemini...');
        try {
            $post = $imageService->generateWithGemini($post, $admin);
            $this->info("  Imagem gerada: {$post->public_image_url}");
            $this->info("  Status imagem: {$post->image_status}");
            $this->info("  Validação    : " . ($post->image_validation_status ?? '—'));
            if ($post->image_validation_error) {
                $this->warn("  Aviso validação: {$post->image_validation_error}");
            }
        } catch (\Throwable $e) {
            $this->warn("  Imagem: FALHOU — {$e->getMessage()}");
        }

        $this->info('');
        $this->info("POST_ID={$post->id}");
        $this->info("STATUS={$post->status}");
        $this->info("IMAGE_STATUS=" . ($post->image_status ?? 'none'));
        $this->info("IMAGE_VALIDATION=" . ($post->image_validation_status ?? 'none'));
        $this->info("PUBLIC_IMAGE_URL=" . ($post->public_image_url ?? 'none'));
        $this->info('');
        $this->line('Post não publicado. Use:');
        $this->line("  php artisan social:validate-image {$post->id}");
        $this->line("  php artisan social:test-instagram-publish {$post->id}");

        return self::SUCCESS;
    }
}
