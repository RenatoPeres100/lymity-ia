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

    // Models with IMAGE modality support available on this account (generateContent endpoint)
    private const IMAGE_MODELS = [
        'gemini-3.1-flash-image',
        'gemini-2.5-flash-image',
        'gemini-3-pro-image',
        'gemini-3.1-flash-image-preview',
        'gemini-3-pro-image-preview',
    ];

    // Imagen 4 models (predict endpoint — requires paid plan)
    private const IMAGEN_MODELS = [
        'imagen-4.0-fast-generate-001',
        'imagen-4.0-generate-001',
    ];

    public function __construct()
    {
        $this->apiKey = config('ai.gemini_api_key');
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
            throw new \RuntimeException('AI_PROVIDER precisa ser "google" para usar Gemini.');
        }
        if (!config('ai.image_generation_enabled', true)) {
            throw new \RuntimeException('AI_IMAGE_GENERATION_ENABLED=false. Ative no .env para gerar imagens.');
        }
    }

    /**
     * Generate an image and save to public storage.
     * Returns ['path', 'public_url', 'bytes', 'mime', 'model']
     */
    public function generateAndStore(string $prompt, string $storagePath): array
    {
        $this->assertConfigured();

        [$bytes, $usedModel] = $this->generateImageBytes($prompt);

        $mime     = $this->detectMime($bytes);
        $ext      = $mime === 'image/png' ? 'png' : 'jpg';
        $fullPath = rtrim($storagePath, '/') . '.' . $ext;

        $disk = config('social.image.disk', 'public');
        Storage::disk($disk)->put($fullPath, $bytes);

        $baseUrl   = config('social.image.public_base_url', config('app.url') . '/storage');
        $publicUrl = rtrim($baseUrl, '/') . '/' . $fullPath;

        Log::info("[GeminiImage] Image stored. model={$usedModel} path={$fullPath} size=" . strlen($bytes));

        return [
            'path'       => $fullPath,
            'public_url' => $publicUrl,
            'bytes'      => strlen($bytes),
            'mime'       => $mime,
            'model'      => $usedModel,
        ];
    }

    // ── Private generation logic ───────────────────────────────────────────────

    /**
     * Returns [bytes, modelName]
     */
    private function generateImageBytes(string $prompt): array
    {
        $errors = [];

        // Strategy 1: Gemini image models via generateContent with IMAGE modality
        foreach (self::IMAGE_MODELS as $model) {
            try {
                $bytes = $this->tryGenerateContent($model, $prompt);
                if ($bytes !== null) {
                    return [$bytes, $model];
                }
            } catch (\Throwable $e) {
                $errors[] = "{$model}: " . $e->getMessage();
                Log::debug("[GeminiImage] {$model} failed: " . $e->getMessage());
            }
        }

        // Strategy 2: Imagen 4 via predict endpoint (requires paid plan)
        foreach (self::IMAGEN_MODELS as $model) {
            try {
                $bytes = $this->tryImagenPredict($model, $prompt);
                if ($bytes !== null) {
                    return [$bytes, $model];
                }
            } catch (\Throwable $e) {
                $errors[] = "{$model}: " . $e->getMessage();
                Log::debug("[GeminiImage] {$model} failed: " . $e->getMessage());
            }
        }

        $summary = implode(' | ', array_slice($errors, 0, 3));
        throw new \RuntimeException(
            "Geração de imagem falhou. Detalhes: {$summary}\n"
            . "Se aparecer 'quota' ou 'rate limit', aguarde alguns minutos e tente novamente. "
            . "Se aparecer 'paid plan', faça upgrade no Google AI Studio. "
            . "Enquanto isso, use Upload Manual ou URL na seção de imagem."
        );
    }

    private function tryGenerateContent(string $model, string $prompt): ?string
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::withoutVerifying()
            ->timeout(120)
            ->withQueryParameters(['key' => $this->apiKey])
            ->post($endpoint, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE', 'TEXT'],
                ],
            ]);

        if ($response->status() === 429) {
            throw new \RuntimeException("Rate limit / quota atingido para {$model}. Aguarde e tente novamente.");
        }

        if ($response->status() === 400) {
            $err = $response->json('error.message') ?? '';
            if (str_contains($err, 'not available in your country')) {
                throw new \RuntimeException("Geração de imagem não disponível no país deste servidor para {$model}.");
            }
            if (str_contains($err, 'responseModalities') || str_contains($err, 'modalities')) {
                // Model doesn't support image output — skip silently
                return null;
            }
            throw new \RuntimeException("Erro 400 em {$model}: {$err}");
        }

        if ($response->failed()) {
            throw new \RuntimeException("HTTP {$response->status()} em {$model}: " . ($response->json('error.message') ?? 'sem detalhe'));
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];
        foreach ($parts as $part) {
            if (!empty($part['inlineData']['data'])) {
                return base64_decode($part['inlineData']['data']);
            }
        }

        // Successful response but no image data
        return null;
    }

    private function tryImagenPredict(string $model, string $prompt): ?string
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:predict";

        $response = Http::withoutVerifying()
            ->timeout(120)
            ->withQueryParameters(['key' => $this->apiKey])
            ->post($endpoint, [
                'instances'  => [['prompt' => $prompt]],
                'parameters' => ['sampleCount' => 1, 'aspectRatio' => '1:1'],
            ]);

        if ($response->status() === 429) {
            throw new \RuntimeException("Rate limit / quota atingido para {$model}.");
        }

        if ($response->status() === 400) {
            $err = $response->json('error.message') ?? '';
            if (str_contains($err, 'paid plan') || str_contains($err, 'upgrade')) {
                throw new \RuntimeException("Imagen requer plano pago. Upgrade em ai.dev/projects.");
            }
            throw new \RuntimeException("Erro 400 em {$model}: {$err}");
        }

        if ($response->failed()) {
            throw new \RuntimeException("HTTP {$response->status()} em {$model}: " . ($response->json('error.message') ?? 'sem detalhe'));
        }

        // predictions[0].bytesBase64Encoded
        $b64 = $response->json('predictions.0.bytesBase64Encoded');
        if (!empty($b64)) {
            return base64_decode($b64);
        }

        // predictions[0].image.imageBytes (alternate format)
        $b64Alt = $response->json('predictions.0.image.imageBytes');
        if (!empty($b64Alt)) {
            return base64_decode($b64Alt);
        }

        return null;
    }

    // ── Carousel plan (uses text model) ───────────────────────────────────────

    public function generateCarouselPlan(SocialPost $post, SocialImagePromptService $promptService): array
    {
        $this->assertConfigured();

        // Use text model — prefer gemini_text_model, fallback to known working model
        $textModel = config('ai.gemini_text_model')
            ?: config('ai.gemini_model')
            ?: 'gemini-2.5-flash';

        $prompt   = $promptService->buildCarouselPlanPrompt($post);
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$textModel}:generateContent";

        $response = Http::withoutVerifying()
            ->timeout(60)
            ->withQueryParameters(['key' => $this->apiKey])
            ->post($endpoint, [
                'contents'         => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 2048],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Gemini texto falhou ao gerar plano de carrossel: '
                . ($response->json('error.message') ?? 'HTTP ' . $response->status())
            );
        }

        $text = $response->json('candidates.0.content.parts.0.text') ?? '';

        if (preg_match('/```(?:json)?\s*([\s\S]+?)\s*```/i', $text, $m)) {
            $text = $m[1];
        }

        $plan = json_decode(trim($text), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($plan['slides'])) {
            Log::warning('[GeminiImage] Carousel plan JSON invalid. Raw: ' . mb_substr($text, 0, 400));
            throw new \RuntimeException('Gemini não retornou JSON válido para o plano de carrossel.');
        }

        return $plan;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function detectMime(string $bytes): string
    {
        if (strlen($bytes) >= 4 && substr($bytes, 0, 4) === "\x89PNG") {
            return 'image/png';
        }
        if (strlen($bytes) >= 3 && substr($bytes, 0, 3) === "\xFF\xD8\xFF") {
            return 'image/jpeg';
        }
        return 'image/jpeg';
    }
}
