<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private int    $maxTokens;
    private float  $temperature;
    private string $baseUrl     = 'https://api.anthropic.com/v1';
    private string $apiVersion  = '2023-06-01';

    public function __construct()
    {
        $this->apiKey      = config('ai.api_key', '');
        $this->model       = config('ai.model', 'claude-haiku-4-5-20251001');
        $this->maxTokens   = (int) config('ai.max_tokens', 1200);
        $this->temperature = (float) config('ai.temperature', 0.7);
    }

    public function providerName(): string { return 'claude'; }

    public function supportsStructuredOutput(): bool { return true; }

    public function testConnection(): array
    {
        $start = microtime(true);

        if (empty($this->apiKey)) {
            return [
                'success'    => false,
                'message'    => 'AI_API_KEY não configurada para Claude. Configure no .env: AI_API_KEY=sk-ant-...',
                'model'      => $this->model,
                'latency_ms' => null,
            ];
        }

        try {
            // Minimal call to verify credentials
            $response = Http::withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => $this->apiVersion,
                    'content-type'      => 'application/json',
                ])
                ->timeout(15)
                ->post("{$this->baseUrl}/messages", [
                    'model'      => $this->model,
                    'max_tokens' => 16,
                    'messages'   => [['role' => 'user', 'content' => 'ping']],
                ]);

            $latency = (int) round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'message'    => "Conexão com Anthropic Claude estabelecida. Modelo: {$this->model}",
                    'model'      => $this->model,
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success'    => false,
                'message'    => 'Anthropic respondeu com erro ' . $response->status() . ': ' . $this->safeErrorMessage($response),
                'model'      => $this->model,
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            Log::warning('[ClaudeProvider] testConnection falhou', ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'message'    => 'Erro ao conectar com Anthropic Claude: ' . $e->getMessage(),
                'model'      => $this->model,
                'latency_ms' => null,
            ];
        }
    }

    public function generate(array $payload): array
    {
        if (empty($this->apiKey)) {
            return $this->errorResult('AI_API_KEY não configurada para Claude. Configure no .env: AI_API_KEY=sk-ant-...');
        }

        $systemPrompt = $this->buildSystemPrompt($payload);
        $userMessage  = $this->buildUserMessage($payload);
        $promptPreview = mb_substr($userMessage, 0, 300);

        try {
            $response = Http::withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => $this->apiVersion,
                    'content-type'      => 'application/json',
                ])
                ->timeout(60)
                ->post("{$this->baseUrl}/messages", [
                    'model'      => $this->model,
                    'max_tokens' => $this->maxTokens,
                    'system'     => $systemPrompt,
                    'messages'   => [['role' => 'user', 'content' => $userMessage]],
                    'temperature' => $this->temperature,
                ]);

            if (!$response->successful()) {
                $msg = 'Claude erro ' . $response->status() . ': ' . $this->safeErrorMessage($response);
                Log::warning('[ClaudeProvider] generate falhou', ['status' => $response->status()]);
                return $this->errorResult($msg, $promptPreview);
            }

            $body         = $response->json();
            $content      = $body['content'][0]['text'] ?? '';
            $inputTokens  = $body['usage']['input_tokens'] ?? null;
            $outputTokens = $body['usage']['output_tokens'] ?? null;
            $totalTokens  = ($inputTokens !== null && $outputTokens !== null)
                ? $inputTokens + $outputTokens
                : null;
            $cost = $this->estimateCost($inputTokens, $outputTokens);

            return [
                'success'          => true,
                'provider'         => 'claude',
                'model'            => $this->model,
                'prompt_preview'   => $promptPreview,
                'response'         => $content,
                'response_summary' => mb_substr(strip_tags($content), 0, 300),
                'input_tokens'     => $inputTokens,
                'output_tokens'    => $outputTokens,
                'total_tokens'     => $totalTokens,
                'estimated_cost'   => $cost,
                'error_message'    => null,
                'raw_response'     => ['id' => $body['id'] ?? null, 'model' => $body['model'] ?? null],
            ];
        } catch (\Throwable $e) {
            Log::warning('[ClaudeProvider] generate exception', ['error' => $e->getMessage()]);
            return $this->errorResult('Erro ao chamar Claude: ' . $e->getMessage(), $promptPreview);
        }
    }

    private function buildSystemPrompt(array $payload): string
    {
        $base = "Você é um especialista em marketing digital da agência Lymity IA.\n";

        if (!empty($payload['system_prompt'])) {
            $base .= "\n" . $payload['system_prompt'] . "\n";
        }

        if (!empty($payload['client_context'])) {
            $ctx  = $payload['client_context'];
            $base .= "\nContexto do cliente:\n";
            if (!empty($ctx['name']))            $base .= "- Nome: {$ctx['name']}\n";
            if (!empty($ctx['segment']))         $base .= "- Segmento: {$ctx['segment']}\n";
            if (!empty($ctx['brand_voice']))     $base .= "- Tom de voz: {$ctx['brand_voice']}\n";
            if (!empty($ctx['target_audience'])) $base .= "- Público: {$ctx['target_audience']}\n";
        }

        if (!empty($payload['memories'])) {
            $base .= "\nMemórias relevantes:\n";
            foreach (array_slice($payload['memories'], 0, 3) as $mem) {
                $base .= "- " . (is_array($mem) ? ($mem['content'] ?? '') : $mem) . "\n";
            }
        }

        $base .= "\nResponda sempre em português do Brasil. Gere apenas o conteúdo solicitado.";
        return $base;
    }

    private function buildUserMessage(array $payload): string
    {
        $taskType = $payload['task_type'] ?? 'general';
        $title    = $payload['title']     ?? '';
        $desc     = $payload['description'] ?? '';

        $msg = match ($taskType) {
            'generate_social_post', 'social_post'
                => "Gere 3 posts para redes sociais sobre: {$title}.\nDetalhes: {$desc}\nInclua: legenda, hashtags, CTA. Responda em JSON.",
            'generate_blog_post'
                => "Escreva um artigo completo sobre: {$title}.\nFoco: {$desc}\nInclua: título SEO, excerpt, conteúdo HTML, meta description.",
            'generate_google_ads_campaign'
                => "Crie campanha Google Ads: {$title}.\nDetalhes: {$desc}\nInclua: headlines, descriptions, extensões.",
            'generate_meta_ads_campaign'
                => "Crie campanha Meta Ads: {$title}.\nDetalhes: {$desc}\nInclua: copy, variações, segmentação.",
            'generate_proposal'
                => "Gere proposta comercial para: {$title}.\nDetalhes: {$desc}\nInclua: escopo, entregáveis, investimento.",
            'generate_budget'
                => "Elabore orçamento para: {$title}.\nDetalhes: {$desc}\nInclua: itens, quantidades, valores.",
            'analyze_campaign_metrics'
                => "Analise métricas da campanha: {$title}.\nDados: {$desc}\nInclua: insights, recomendações.",
            'generate_seo_plan'
                => "Crie plano de SEO para: {$title}.\nFoco: {$desc}\nInclua: keywords, clusters, estratégia.",
            default
                => "Tarefa: {$title}\nDescrição: {$desc}\nTipo: {$taskType}",
        };

        if (!empty($payload['user_message'])) {
            $msg .= "\n\nInformações adicionais: " . $payload['user_message'];
        }

        return $msg;
    }

    private function estimateCost(?int $inputTokens, ?int $outputTokens): ?float
    {
        if ($inputTokens === null && $outputTokens === null) {
            return null;
        }

        // Approximate Anthropic pricing per 1K tokens (USD)
        $costPerKInput  = match (true) {
            str_contains($this->model, 'claude-opus')   => 0.015,
            str_contains($this->model, 'claude-sonnet') => 0.003,
            str_contains($this->model, 'claude-haiku')  => 0.00025,
            default                                     => 0.003,
        };
        $costPerKOutput = $costPerKInput * 5;

        return round(
            (($inputTokens ?? 0) / 1000 * $costPerKInput) +
            (($outputTokens ?? 0) / 1000 * $costPerKOutput),
            6
        );
    }

    private function safeErrorMessage($response): string
    {
        $body = $response->json();
        return $body['error']['message'] ?? $response->body();
    }

    private function errorResult(string $message, string $promptPreview = ''): array
    {
        return [
            'success'          => false,
            'provider'         => 'claude',
            'model'            => $this->model,
            'prompt_preview'   => $promptPreview,
            'response'         => '',
            'response_summary' => '',
            'input_tokens'     => null,
            'output_tokens'    => null,
            'total_tokens'     => null,
            'estimated_cost'   => null,
            'error_message'    => $message,
            'raw_response'     => null,
        ];
    }
}
