<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\DTO\AITextResponse;
use App\Services\AI\DTO\TokenEstimate;
use App\Services\AI\Response\AIJsonResponseNormalizerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleGeminiProvider implements AIProviderInterface
{
    private string $model;
    private string $fallbackModel;
    private ?string $apiKey;

    public function __construct()
    {
        $this->apiKey        = config('ai.gemini_api_key');
        $this->model         = config('ai.gemini_text_model', 'gemini-2.5-flash');
        $this->fallbackModel = config('ai.gemini_text_fallback_model', 'gemini-2.5-flash-lite');
    }

    public function providerName(): string
    {
        return 'google';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function generateText(array $payload): AITextResponse
    {
        if (!$this->isConfigured()) {
            return AITextResponse::error('google', $this->model,
                'Gemini não configurado. Verifique GEMINI_API_KEY e AI_PROVIDER=google no .env.');
        }

        $systemPrompt = $payload['system_prompt'] ?? '';
        $userMessage  = $payload['user_message'] ?? '';
        $jsonMode     = $payload['json_mode'] ?? false;
        $maxTokens    = $payload['max_tokens'] ?? config('ai.max_output_tokens', 4096);
        // Lower temperature for JSON mode = more deterministic, less likely to produce invalid chars
        $defaultTemp  = $jsonMode ? 0.35 : config('ai.temperature', 0.7);
        $temperature  = $payload['temperature'] ?? $defaultTemp;

        $result = $this->callGemini($this->model, $systemPrompt, $userMessage, $jsonMode, $maxTokens, $temperature);

        if (!$result->success && $result->model !== $this->fallbackModel) {
            Log::warning("[Gemini] Primary model failed, trying fallback: {$result->error_message}");
            $result = $this->callGemini($this->fallbackModel, $systemPrompt, $userMessage, $jsonMode, $maxTokens, $temperature, true);
        }

        return $result;
    }

    private function callGemini(
        string $model,
        string $systemPrompt,
        string $userMessage,
        bool   $jsonMode,
        int    $maxTokens,
        float  $temperature,
        bool   $isFallback = false,
    ): AITextResponse {
        $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $body = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $userMessage]]],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
                'temperature'     => $temperature,
            ],
        ];

        if (!empty($systemPrompt)) {
            $body['systemInstruction'] = ['parts' => [['text' => $systemPrompt]]];
        }

        if ($jsonMode) {
            $body['generationConfig']['responseMimeType'] = 'application/json';
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withQueryParameters(['key' => $this->apiKey])
                ->post($baseUrl, $body);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMsg  = $errorBody['error']['message'] ?? "HTTP {$response->status()}";
                Log::error("[Gemini] API error on model {$model}: {$errorMsg}");
                return AITextResponse::error('google', $model, $errorMsg);
            }

            $data     = $response->json();
            $rawText  = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($rawText === null) {
                $reason = $data['candidates'][0]['finishReason'] ?? 'unknown';
                return AITextResponse::error('google', $model, "Empty response from Gemini. Finish reason: {$reason}");
            }

            $inputTokens  = $data['usageMetadata']['promptTokenCount'] ?? null;
            $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? null;
            $cost         = $this->estimateCost($model, $inputTokens, $outputTokens);

            // For JSON mode, pre-clean with normalizer so AITextResponse carries clean text
            // Final JSON parsing happens in the execution service with full retry logic
            if ($jsonMode) {
                $normalizer  = app(AIJsonResponseNormalizerService::class);
                $cleanedText = $normalizer->stripCodeFences($rawText);
                $cleanedText = $normalizer->removeInvalidControlCharacters($cleanedText);
                $cleanedText = $normalizer->fixCommonEncodingIssues($cleanedText);
                $rawText     = $cleanedText;
            }

            return AITextResponse::success(
                provider: 'google',
                model: $model,
                text: $rawText,
                json: null, // JSON parsing delegated to execution service with retry
                raw: null,
                inputTokens: $inputTokens,
                outputTokens: $outputTokens,
                cost: $cost,
                fallback: $isFallback,
            );
        } catch (\Throwable $e) {
            Log::error("[Gemini] Exception on model {$model}: {$e->getMessage()}");
            return AITextResponse::error('google', $model, $e->getMessage());
        }
    }

    public function countTokens(array $payload): TokenEstimate
    {
        if (!$this->isConfigured()) {
            return new TokenEstimate('google', $this->model);
        }

        $userMessage  = $payload['user_message'] ?? '';
        $systemPrompt = $payload['system_prompt'] ?? '';
        $fullText     = $systemPrompt . "\n" . $userMessage;

        // Rough estimate: ~4 chars per token
        $inputTokens  = (int) ceil(mb_strlen($fullText) / 4);
        $outputTokens = (int) ceil($inputTokens * 1.5);
        $total        = $inputTokens + $outputTokens;
        $cost         = $this->estimateCost($this->model, $inputTokens, $outputTokens);

        return new TokenEstimate(
            provider: 'google',
            model: $this->model,
            input_tokens: $inputTokens,
            estimated_output_tokens: $outputTokens,
            estimated_total_tokens: $total,
            estimated_cost_usd: $cost,
        );
    }

    private function estimateCost(string $model, ?int $input, ?int $output): ?float
    {
        if ($input === null && $output === null) return null;
        // Use array access to avoid dot-notation issues with model names like gemini-2.5-flash
        $estimates = config('ai.cost_estimates');
        $rates = $estimates['google'][$model] ?? null;
        if (!$rates) return null;
        return round(
            (($input ?? 0) / 1000 * $rates['input']) + (($output ?? 0) / 1000 * $rates['output']),
            6
        );
    }

    private function cleanJsonString(string $text): string
    {
        // Strip markdown code fences if present
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        // Remove control characters except valid whitespace (\t, \n, \r)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        return $text;
    }
}
