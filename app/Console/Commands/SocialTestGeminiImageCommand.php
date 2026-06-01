<?php

namespace App\Console\Commands;

use App\Services\AI\GeminiImageGenerationService;
use App\Services\Social\PublicImageValidatorService;
use Illuminate\Console\Command;

class SocialTestGeminiImageCommand extends Command
{
    protected $signature   = 'social:test-gemini-image';
    protected $description = 'Gera uma imagem de teste com Gemini, valida e imprime a URL pública.';

    public function handle(
        GeminiImageGenerationService $gemini,
        PublicImageValidatorService  $validator,
    ): int {
        $this->info('Testando geração de imagem com Gemini...');

        if (!$gemini->isConfigured()) {
            $this->error('Gemini não configurado. Verifique GEMINI_API_KEY e AI_PROVIDER=google no .env.');
            return self::FAILURE;
        }

        $prompt = "Crie uma imagem quadrada 1080x1080 pixels de teste para a Lymity IA. "
            . "Visual premium, moderno e tecnológico. Paleta azul profundo e roxo digital. "
            . "Elementos abstratos de inteligência artificial e automação. "
            . "Sem texto, sem logotipos. Alta qualidade.";

        $storagePath = 'social/generated/test/gemini_test_' . date('YmdHis');

        try {
            $this->line('Gerando imagem...');
            $result = $gemini->generateAndStore($prompt, $storagePath);

            $this->info("  Arquivo salvo: " . $result['path']);
            $this->info("  URL pública  : " . $result['public_url']);
            $this->info("  Tamanho      : " . round($result['bytes'] / 1024) . " KB");
            $this->info("  MIME         : " . $result['mime']);

            $this->line('Validando URL pública...');
            $validation = $validator->validateUrl($result['public_url']);

            if ($validation->valid) {
                $this->info("  Validação    : ✓ OK — {$validation->width}x{$validation->height}px");
            } else {
                $this->warn("  Validação    : ✗ " . $validation->error);
            }

            $this->newLine();
            $this->info("URL para teste: " . $result['public_url']);
            $this->info("Imagem NÃO foi publicada no Instagram.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Falha: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
