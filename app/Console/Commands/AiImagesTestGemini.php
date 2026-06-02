<?php

namespace App\Console\Commands;

use App\Models\AiImageGeneration;
use App\Services\AiImages\GeminiAiImageService;
use Illuminate\Console\Command;

class AiImagesTestGemini extends Command
{
    protected $signature   = 'ai-images:test-gemini';
    protected $description = 'Test Gemini image generation with a simple controlled prompt';

    public function handle(GeminiAiImageService $service): int
    {
        $this->info('=== Testing Gemini Image Generation ===');

        $result = $service->testConnection();
        if (!$result['ok']) {
            $this->error($result['message']);
            return self::FAILURE;
        }
        $this->info($result['message']);

        $this->info('Generating a test image...');
        $company = \App\Models\Company::first();

        $generation = AiImageGeneration::create([
            'company_id'      => $company?->id,
            'channel'         => 'general',
            'purpose'         => 'other',
            'generation_type' => 'single',
            'title'           => 'Teste Gemini - ' . now()->format('d/m H:i'),
            'subject'         => 'Teste de integração da Gemini Image API',
            'prompt_context'  => 'Imagem de teste premium para verificar funcionamento da API de geração de imagens.',
            'tone'            => 'tecnológico e profissional',
            'visual_style'    => 'premium, clean, digital',
            'slides_count'    => 1,
            'aspect_ratio'    => '1:1',
            'status'          => 'draft',
        ]);

        $this->line("Generation ID: {$generation->id}");

        try {
            $result = $service->generateSingle($generation);
            $image  = $result['image'];

            $this->info('');
            $this->info('SUCCESS!');
            $this->line("  Image ID:    {$image->id}");
            $this->line("  Public URL:  {$image->public_url}");
            $this->line("  MIME type:   {$image->mime_type}");
            $this->line("  Model used:  " . ($image->metadata['model'] ?? 'unknown'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Generation failed: ' . $e->getMessage());
            $this->line('This may be expected if the Gemini image models require a paid plan.');
            $this->line('Use manual upload or external URL for now.');
            return self::FAILURE;
        }
    }
}
