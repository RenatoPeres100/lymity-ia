<?php

namespace App\Services\AiImages;

use App\Models\AiGeneratedImage;
use App\Models\AiImageGeneration;
use App\Services\AI\GeminiImageGenerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiAiImageService
{
    public function __construct(
        private readonly GeminiImageGenerationService  $gemini,
        private readonly AiImagePromptBuilderService   $promptBuilder,
    ) {}

    public function isConfigured(): bool
    {
        return $this->gemini->isConfigured()
            && config('ai-images.enabled', false);
    }

    public function testConnection(): array
    {
        if (empty(config('ai.gemini_api_key'))) {
            return ['ok' => false, 'message' => 'GEMINI_API_KEY não configurado no .env.'];
        }
        if (config('ai.provider', 'google') !== 'google') {
            return ['ok' => false, 'message' => 'AI_PROVIDER precisa ser "google" para usar Gemini.'];
        }
        if (!config('ai-images.enabled', false)) {
            return ['ok' => false, 'message' => 'GEMINI_IMAGE_ENABLED=false no .env. Ative para usar imagens.'];
        }
        if (!config('ai.gemini_image_model')) {
            return ['ok' => false, 'message' => 'GEMINI_IMAGE_MODEL não configurado no .env.'];
        }

        // Verify storage link
        $disk = config('ai-images.storage.disk', 'public');
        $path = config('ai-images.storage.path', 'ai-images');
        if (!Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->makeDirectory($path);
        }

        return [
            'ok'      => true,
            'message' => 'Gemini configurado corretamente. Pronto para geração de imagens.',
            'model'   => config('ai.gemini_image_model'),
        ];
    }

    public function generateSingle(AiImageGeneration $generation): array
    {
        $this->assertConfigured();

        $prompt    = $this->promptBuilder->buildSinglePrompt($generation);
        $startTime = microtime(true);

        try {
            $generation->update([
                'status'       => 'generating',
                'final_prompt' => $prompt,
                'provider'     => 'google',
                'model'        => config('ai.gemini_image_model'),
            ]);

            $storagePath = $this->buildStoragePath($generation, 1);
            $result = $this->gemini->generateAndStore($prompt, $storagePath);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $image = $this->persistImage($generation, $result, 1, $prompt);

            $generation->update([
                'status'            => 'completed',
                'generated_at'      => now(),
                'duration_ms'       => $durationMs,
                'response_metadata' => ['model_used' => $result['model'] ?? null],
            ]);

            return ['success' => true, 'image' => $image, 'generation' => $generation->fresh()];
        } catch (\Throwable $e) {
            $generation->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'duration_ms'   => (int) ((microtime(true) - $startTime) * 1000),
            ]);
            Log::error("[GeminiAiImageService] generateSingle failed: {$e->getMessage()}", [
                'generation_id' => $generation->id,
            ]);
            throw $e;
        }
    }

    public function generateCarousel(AiImageGeneration $generation): array
    {
        $this->assertConfigured();

        $prompts   = $this->promptBuilder->buildCarouselPrompts($generation);
        $startTime = microtime(true);
        $images    = [];
        $errors    = [];

        try {
            $generation->update([
                'status'   => 'generating',
                'provider' => 'google',
                'model'    => config('ai.gemini_image_model'),
            ]);

            foreach ($prompts as $index => $prompt) {
                $slideNumber = $index + 1;
                try {
                    $storagePath = $this->buildStoragePath($generation, $slideNumber);
                    $result = $this->gemini->generateAndStore($prompt, $storagePath);
                    $image  = $this->persistImage($generation, $result, $slideNumber, $prompt);
                    $images[] = $image;
                } catch (\Throwable $e) {
                    $errors[] = "Slide {$slideNumber}: " . $e->getMessage();
                    Log::warning("[GeminiAiImageService] Carousel slide {$slideNumber} failed: {$e->getMessage()}");
                }
            }

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if (empty($images)) {
                $generation->update([
                    'status'        => 'failed',
                    'error_message' => implode(' | ', $errors),
                    'duration_ms'   => $durationMs,
                ]);
                throw new \RuntimeException('Nenhum slide do carrossel foi gerado com sucesso. Erros: ' . implode(' | ', $errors));
            }

            $generation->update([
                'status'       => 'completed',
                'generated_at' => now(),
                'duration_ms'  => $durationMs,
            ]);

            return ['success' => true, 'images' => $images, 'errors' => $errors, 'generation' => $generation->fresh()];
        } catch (\Throwable $e) {
            $generation->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'duration_ms'   => (int) ((microtime(true) - $startTime) * 1000),
            ]);
            Log::error("[GeminiAiImageService] generateCarousel failed: {$e->getMessage()}", [
                'generation_id' => $generation->id,
            ]);
            throw $e;
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function assertConfigured(): void
    {
        if (empty(config('ai.gemini_api_key'))) {
            throw new \RuntimeException('GEMINI_API_KEY não configurado. Configure no .env e tente novamente.');
        }
        if (!config('ai-images.enabled', false)) {
            throw new \RuntimeException('GEMINI_IMAGE_ENABLED=false. Ative no .env para gerar imagens.');
        }
        if (!config('ai.gemini_image_model')) {
            throw new \RuntimeException('GEMINI_IMAGE_MODEL não configurado no .env.');
        }
    }

    private function buildStoragePath(AiImageGeneration $generation, int $slideNumber): string
    {
        $basePath = config('ai-images.storage.path', 'ai-images');
        $channel  = $generation->channel ?? 'general';
        return "{$basePath}/{$channel}/{$generation->id}/slide_{$slideNumber}";
    }

    private function buildPublicUrl(string $storagePath): string
    {
        $baseUrl = rtrim(config('ai-images.storage.public_base_url', config('app.url') . '/storage'), '/');
        return "{$baseUrl}/{$storagePath}";
    }

    private function persistImage(
        AiImageGeneration $generation,
        array $result,
        int $slideNumber,
        string $prompt
    ): AiGeneratedImage {
        return AiGeneratedImage::create([
            'ai_image_generation_id' => $generation->id,
            'slide_number'           => $slideNumber,
            'storage_path'           => $result['path'],
            'public_url'             => $result['public_url'],
            'mime_type'              => $result['mime'] ?? 'image/jpeg',
            'prompt_used'            => $prompt,
            'status'                 => 'active',
            'metadata'               => [
                'model' => $result['model'] ?? null,
                'bytes' => $result['bytes'] ?? null,
            ],
        ]);
    }
}
