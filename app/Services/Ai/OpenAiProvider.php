<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private int    $maxTokens;
    private float  $temperature;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey      = config('ai.api_key', '');
        $this->model       = config('ai.model', 'gpt-4o-mini');
        $this->maxTokens   = (int) config('ai.max_tokens', 1200);
        $this->temperature = (float) config('ai.temperature', 0.7);
    }

    public function providerName(): string { return 'openai'; }

    public function supportsStructuredOutput(): bool { return true; }

    public function testConnection(): array
    {
        $start = microtime(true);

        if (empty($this->apiKey)) {
            return [
                'success'    => false,
                'message'    => 'AI_API_KEY não configurada. Adicione no .env: AI_API_KEY=sk-...',
                'model'      => $this->model,
                'latency_ms' => null,
            ];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->get("{$this->baseUrl}/models");

            $latency = (int) round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'message'    => "Conexão com OpenAI estabelecida. Modelo: {$this->model}",
                    'model'      => $this->model,
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success'    => false,
                'message'    => 'OpenAI respondeu com erro ' . $response->status() . ': ' . $this->safeErrorMessage($response),
                'model'      => $this->model,
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            Log::warning('[OpenAiProvider] testConnection falhou', ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'message'    => 'Erro ao conectar com OpenAI: ' . $e->getMessage(),
                'model'      => $this->model,
                'latency_ms' => null,
            ];
        }
    }

    public function generate(array $payload): array
    {
        if (empty($this->apiKey)) {
            return $this->errorResult('AI_API_KEY não configurada para OpenAI. Configure no .env: AI_API_KEY=sk-...');
        }

        $systemPrompt = $this->buildSystemPrompt($payload);
        $userMessage  = $this->buildUserMessage($payload);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userMessage],
        ];

        $promptPreview = mb_substr($userMessage, 0, 300);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);

            if (!$response->successful()) {
                $msg = 'OpenAI erro ' . $response->status() . ': ' . $this->safeErrorMessage($response);
                Log::warning('[OpenAiProvider] generate falhou', ['status' => $response->status()]);
                return $this->errorResult($msg, $promptPreview);
            }

            $body         = $response->json();
            $content      = $body['choices'][0]['message']['content'] ?? '';
            $inputTokens  = $body['usage']['prompt_tokens'] ?? null;
            $outputTokens = $body['usage']['completion_tokens'] ?? null;
            $totalTokens  = $body['usage']['total_tokens'] ?? null;
            $cost         = $this->estimateCost($inputTokens, $outputTokens);

            return [
                'success'          => true,
                'provider'         => 'openai',
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
            Log::warning('[OpenAiProvider] generate exception', ['error' => $e->getMessage()]);
            return $this->errorResult('Erro ao chamar OpenAI: ' . $e->getMessage(), $promptPreview);
        }
    }

    private function buildSystemPrompt(array $payload): string
    {
        $employeePrompt = $payload['system_prompt'] ?? '';
        $basePrompt = "Você é um especialista em marketing digital da agência Lymity IA.\n";

        if ($employeePrompt) {
            $basePrompt .= "\n{$employeePrompt}\n";
        }

        if (!empty($payload['client_context'])) {
            $ctx = $payload['client_context'];
            $basePrompt .= "\nContexto do cliente:\n";
            if (!empty($ctx['name']))           $basePrompt .= "- Nome: {$ctx['name']}\n";
            if (!empty($ctx['segment']))        $basePrompt .= "- Segmento: {$ctx['segment']}\n";
            if (!empty($ctx['brand_voice']))    $basePrompt .= "- Tom de voz: {$ctx['brand_voice']}\n";
            if (!empty($ctx['target_audience'])) $basePrompt .= "- Público-alvo: {$ctx['target_audience']}\n";
        }

        if (!empty($payload['memories'])) {
            $basePrompt .= "\nMemórias relevantes:\n";
            foreach (array_slice($payload['memories'], 0, 3) as $mem) {
                $basePrompt .= "- " . (is_array($mem) ? ($mem['content'] ?? '') : $mem) . "\n";
            }
        }

        $basePrompt .= "\nSempre responda em português do Brasil. Gere apenas o conteúdo solicitado, sem comentários extras.";

        return $basePrompt;
    }

    private function buildUserMessage(array $payload): string
    {
        $taskType = $payload['task_type'] ?? 'general';
        $title    = $payload['title']     ?? '';
        $desc     = $payload['description'] ?? '';

        $instructions = match ($taskType) {
            'generate_social_post', 'social_post'
                => "Gere 3 posts para redes sociais sobre: {$title}.\nDetalhes: {$desc}\nInclua: legenda, hashtags, CTA. Responda em JSON.",
            'generate_blog_post'
                => "Escreva um artigo completo sobre: {$title}.\nFoco: {$desc}\nInclua: título SEO, excerpt, conteúdo em HTML, meta description.",
            'generate_google_ads_campaign'
                => "Crie uma campanha Google Ads sobre: {$title}.\nDetalhes: {$desc}\nInclua: headlines, descriptions, extensões sugeridas.",
            'generate_meta_ads_campaign'
                => "Crie uma campanha Meta Ads sobre: {$title}.\nDetalhes: {$desc}\nInclua: copy do anúncio, variações, segmentação sugerida.",
            'generate_proposal'
                => "Gere uma proposta comercial para: {$title}.\nDetalhes: {$desc}\nInclua: escopo, entregáveis, investimento sugerido.",
            'generate_budget'
                => "Elabore um orçamento detalhado para: {$title}.\nDetalhes: {$desc}\nInclua: itens, quantidades, valores estimados.",
            'analyze_campaign_metrics'
                => "Analise e interprete as métricas da campanha: {$title}.\nDados: {$desc}\nInclua: insights, recomendações de otimização.",
            'generate_seo_plan'
                => "Crie um plano de SEO para: {$title}.\nFoco: {$desc}\nInclua: keywords, clusters, estratégia de conteúdo.",
            default
                => "Tarefa: {$title}\nDescrição: {$desc}\nTipo: {$taskType}",
        };

        if (!empty($payload['user_message'])) {
            $instructions .= "\n\nInformações adicionais: " . $payload['user_message'];
        }

        return $instructions;
    }

    private function estimateCost(?int $inputTokens, ?int $outputTokens): ?float
    {
        if ($inputTokens === null && $outputTokens === null) {
            return null;
        }

        // Approximate prices per 1K tokens (USD) — gpt-4o-mini as baseline
        $costPerKInput  = match (true) {
            str_contains($this->model, 'gpt-4o')   => 0.005,
            str_contains($this->model, 'gpt-4')    => 0.03,
            str_contains($this->model, 'gpt-3.5')  => 0.001,
            default                                => 0.002,
        };
        $costPerKOutput = $costPerKInput * 3;

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
            'provider'         => 'openai',
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
