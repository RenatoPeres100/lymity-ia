<?php

namespace App\Services\AI;

use App\Models\SocialPost;
use App\Services\Social\SocialImagePromptService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiImageGenerationService
{
    private ?string $apiKey;
    private string  $model;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini_api_key');
        $this->model  = config('ai.gemini_image_model', 'imagen-3.0-generate-002');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey)
            && config('ai.provider', 'google') === 'google'
            && config('ai.image_generation_enabled', true);
    }

    public function assertConfigured(): void
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY não configurado. Verifique o .env.');
        }
        if (config('ai.provider', 'google') !== 'google') {
            throw new \RuntimeException('AI_PROVIDER não é google. Geração de imagem Gemini requer AI_PROVIDER=google.');
        }
        if (!config('ai.image_generation_enabled', true)) {
            throw new \RuntimeException('AI_IMAGE_GENERATION_ENABLED=false no .env. Geração de imagem bloqueada.');
        }
    }

    /**
     * Generate an image and save it to storage.
     * Returns ['path' => ..., 'public_url' => ..., 'bytes' => int, 'mime' => ...]
     */
    public function generateAndStore(string $prompt, string $storagePath): array
    {
        $this->assertConfigured();

        $bytes = $this->generateImageBytes($prompt);

        $mime = $this->detectMime($bytes);
        $ext  = $mime === 'image/png' ? 'png' : 'jpg';
        $fullPath = rtrim($storagePath, '/') . '.' . $ext;

        $disk = config('social.image.disk', 'public');
        Storage::disk($disk)->put($fullPath, $bytes);

        $baseUrl    = config('social.image.public_base_url', config('app.url') . '/storage');
        $publicUrl  = rtrim($baseUrl, '/') . '/' . $fullPath;

        return [
            'path'       => $fullPath,
            'public_url' => $publicUrl,
            'bytes'      => strlen($bytes),
            'mime'       => $mime,
        ];
    }

    private function generateImageBytes(string $prompt): string
    {
        // Imagen 3 via Predict API
        $url = "https://us-central1-aiplatform.googleapis.com/v1/projects/";

        // Try Gemini 2.0 Flash Experimental image generation (generateContent with image output)
        // This is the publicly available image generation endpoint
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateImages";

        $body = [
            'prompt'         => $prompt,
            'number_of_images' => 1,
            'aspect_ratio'   => '1:1',
            'safety_filter_level' => 'block_some',
            'person_generation' => 'dont_allow',
        ];

        try {
            $response = Http::withoutVerifying()
                ->timeout(120)
                ->withQueryParameters(['key' => $this->apiKey])
                ->post($endpoint, $body);

            if ($response->successful()) {
                $data = $response->json();
                // Imagen response format
                if (!empty($data['generatedImages'][0]['image']['imageBytes'])) {
                    return base64_decode($data['generatedImages'][0]['image']['imageBytes']);
                }
                if (!empty($data['predictions'][0]['bytesBase64Encoded'])) {
                    return base64_decode($data['predictions'][0]['bytesBase64Encoded']);
                }
            }

            $errMsg = $response->json('error.message') ?? 'HTTP ' . $response->status();
            Log::warning("[GeminiImage] Imagen endpoint failed ({$this->model}): {$errMsg}. Trying generateContent.");
        } catch (\Throwable $e) {
            Log::warning("[GeminiImage] Imagen endpoint exception: {$e->getMessage()}. Trying generateContent.");
        }

        // Fallback: Gemini 2.0 Flash with image response via generateContent
        return $this->generateViaGenerateContent($prompt);
    }

    private function generateViaGenerateContent(string $prompt): string
    {
        // Use gemini-2.0-flash-exp or gemini-2.0-flash-thinking-exp for image generation
        $models = [
            'gemini-2.0-flash-exp-image-generation',
            'gemini-2.0-flash-exp',
            'gemini-2.0-flash',
        ];

        foreach ($models as $model) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

            $body = [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE', 'TEXT'],
                ],
            ];

            try {
                $response = Http::withoutVerifying()
                    ->timeout(120)
                    ->withQueryParameters(['key' => $this->apiKey])
                    ->post($endpoint, $body);

                if ($response->successful()) {
                    $data  = $response->json();
                    $parts = $data['candidates'][0]['content']['parts'] ?? [];
                    foreach ($parts as $part) {
                        if (!empty($part['inlineData']['data'])) {
                            return base64_decode($part['inlineData']['data']);
                        }
                    }
                }

                $errMsg = $response->json('error.message') ?? 'HTTP ' . $response->status();
                Log::debug("[GeminiImage] generateContent with {$model} failed: {$errMsg}");
            } catch (\Throwable $e) {
                Log::debug("[GeminiImage] generateContent exception with {$model}: {$e->getMessage()}");
            }
        }

        throw new \RuntimeException(
            'Geração de imagem com Gemini falhou em todos os modelos disponíveis. ' .
            'Verifique se GEMINI_API_KEY tem acesso a modelos de geração de imagem. ' .
            'Você pode substituir a imagem manualmente na tela de edição do post.'
        );
    }

    /**
     * Generate carousel plan JSON via Gemini text model.
     * Returns ['recommended' => bool, 'reason' => string, 'slides' => array]
     */
    public function generateCarouselPlan(SocialPost $post, SocialImagePromptService $promptService): array
    {
        $this->assertConfigured();

        $textModel = config('ai.gemini_model', 'gemini-1.5-flash');
        $prompt    = $promptService->buildCarouselPlanPrompt($post);
        $endpoint  = "https://generativelanguage.googleapis.com/v1beta/models/{$textModel}:generateContent";

        $body = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 2048],
        ];

        $response = Http::withoutVerifying()
            ->timeout(60)
            ->withQueryParameters(['key' => $this->apiKey])
            ->post($endpoint, $body);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini text falhou ao gerar plano de carrossel: ' . ($response->json('error.message') ?? 'HTTP ' . $response->status()));
        }

        $text = $response->json('candidates.0.content.parts.0.text') ?? '';

        // Extract JSON from markdown code block if wrapped
        if (preg_match('/```(?:json)?\s*([\s\S]+?)\s*```/i', $text, $m)) {
            $text = $m[1];
        }

        $plan = json_decode(trim($text), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($plan['slides'])) {
            Log::warning('[GeminiImage] Carousel plan JSON parse failed. Raw: ' . mb_substr($text, 0, 500));
            throw new \RuntimeException('Gemini não retornou JSON válido para o plano de carrossel.');
        }

        return $plan;
    }

    private function detectMime(string $bytes): string
    {
        // PNG signature: 89 50 4E 47
        if (strlen($bytes) >= 4 && substr($bytes, 0, 4) === "\x89PNG") {
            return 'image/png';
        }
        // JPEG signature: FF D8 FF
        if (strlen($bytes) >= 3 && substr($bytes, 0, 3) === "\xFF\xD8\xFF") {
            return 'image/jpeg';
        }
        // WebP
        if (strlen($bytes) >= 12 && substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') {
            return 'image/webp';
        }
        return 'image/jpeg';
    }
}
