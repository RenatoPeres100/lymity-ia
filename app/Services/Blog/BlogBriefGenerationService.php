<?php

namespace App\Services\Blog;

use App\Models\AiEmployee;
use App\Models\ActivityLog;
use App\Models\ContentBrief;
use App\Models\User;
use App\Services\AI\AICostGuardService;
use App\Services\AI\AIProviderManager;
use App\Services\AI\AITaskLogService;
use App\Services\Brand\BrandContextService;
use Carbon\Carbon;

class BlogBriefGenerationService
{
    public function __construct(
        private readonly AIProviderManager   $providerManager,
        private readonly AICostGuardService  $costGuard,
        private readonly BrandContextService $brandContext,
        private readonly AITaskLogService    $logService,
    ) {}

    public function generate(array $data, User $user): ContentBrief
    {
        $this->brandContext->validateReadyForGeneration();
        $this->costGuard->assertCanGenerate('generate_brief');

        $planner = AiEmployee::where('slug', 'blog-planner-ia')
            ->where('status', 'active')
            ->first();

        if (!$planner) {
            throw new \RuntimeException('Blog Planner IA não encontrado ou inativo. Verifique os agentes em /admin/employees.');
        }

        $brandCtx    = $this->brandContext->getCompactContext();
        $systemPrompt = $planner->system_prompt ?? $this->defaultPlannerSystemPrompt();
        $userMessage  = $this->buildPlannerPrompt($data, $brandCtx);

        $startedAt = now();
        $provider  = $this->providerManager->provider();

        // Token estimate
        $estimate = $provider->countTokens([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
        ]);

        $response = $provider->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => config('ai.max_output_tokens', 3500),
            'temperature'   => config('ai.temperature', 0.7),
        ]);

        if (!$response->success) {
            $this->logService->logGeneration([
                'task_type'  => 'generate_brief',
                'status'     => 'error',
                'provider'   => $response->provider,
                'model'      => $response->model,
                'error_message' => $response->error_message,
                'agent_id'   => $planner->id,
                'message'    => "Falha ao gerar briefing: {$response->error_message}",
                'level'      => 'error',
                'started_at' => $startedAt,
                'payload'    => ['topic' => $data['topic'] ?? null],
            ]);
            throw new \RuntimeException("Falha ao gerar briefing: {$response->error_message}");
        }

        $json = $response->json;
        if (empty($json)) {
            throw new \RuntimeException('Blog Planner IA retornou resposta vazia ou JSON inválido.');
        }

        $brief = ContentBrief::create([
            'title'              => $json['title'] ?? ($data['topic'] ?? 'Briefing sem título'),
            'topic'              => $json['topic'] ?? ($data['topic'] ?? ''),
            'goal'               => $json['goal'] ?? ($data['goal'] ?? null),
            'audience'           => $json['audience'] ?? null,
            'primary_keyword'    => $json['primary_keyword'] ?? ($data['keyword'] ?? null),
            'secondary_keywords' => $json['secondary_keywords'] ?? [],
            'funnel_stage'       => $json['funnel_stage'] ?? 'awareness',
            'search_intent'      => $json['search_intent'] ?? 'informational',
            'outline'            => $json['outline'] ?? [],
            'cta_suggestion'     => $json['cta_suggestion'] ?? null,
            'status'             => 'generated',
            'generated_by_agent_id' => $planner->id,
            'metadata'           => [
                'model'              => $response->model,
                'fallback_used'      => $response->fallback_used,
                'input_tokens'       => $response->input_tokens,
                'output_tokens'      => $response->output_tokens,
                'estimated_cost_usd' => $response->estimated_cost_usd,
                'generated_by'       => $user->email,
            ],
        ]);

        // Register usage
        $this->costGuard->registerUsage($response, [
            'agent_id'     => $planner->id,
            'task_type'    => 'generate_brief',
            'related_type' => ContentBrief::class,
            'related_id'   => $brief->id,
        ]);

        // Log
        $this->logService->fromResponse($response, [
            'task_type'    => 'generate_brief',
            'agent_id'     => $planner->id,
            'related_type' => ContentBrief::class,
            'related_id'   => $brief->id,
            'started_at'   => $startedAt,
            'payload'      => ['topic' => $data['topic'] ?? null, 'keyword' => $data['keyword'] ?? null],
        ]);

        ActivityLog::create([
            'user_id'      => $user->id,
            'action'       => 'generate_brief',
            'module'       => 'blog',
            'level'        => 'info',
            'description'  => "Briefing gerado por Blog Planner IA: {$brief->title}",
            'subject_type' => ContentBrief::class,
            'subject_id'   => $brief->id,
            'metadata'     => ['brief_id' => $brief->id, 'agent' => 'blog-planner-ia'],
        ]);

        $planner->update(['last_run_at' => now()]);

        return $brief;
    }

    private function buildPlannerPrompt(array $data, string $brandCtx): string
    {
        $topic   = $data['topic'] ?? '';
        $keyword = $data['keyword'] ?? '';
        $goal    = $data['goal'] ?? '';

        return <<<PROMPT
Contexto da marca:
{$brandCtx}

Solicitação:
Tópico: {$topic}
Palavra-chave principal: {$keyword}
Objetivo: {$goal}

Gere um briefing estratégico completo para um post de blog. Responda APENAS com JSON válido neste formato exato:
{
  "title": "string",
  "topic": "string",
  "goal": "string",
  "audience": "string",
  "primary_keyword": "string",
  "secondary_keywords": ["string"],
  "funnel_stage": "awareness|consideration|conversion|retention",
  "search_intent": "informational|commercial|transactional|navigational",
  "outline": [
    {
      "heading": "string",
      "notes": "string"
    }
  ],
  "cta_suggestion": "string"
}
PROMPT;
    }

    private function defaultPlannerSystemPrompt(): string
    {
        return 'Você é o Blog Planner IA da Lymity IA. Sua função é criar briefings estratégicos para posts de blog da agência. Seja objetivo, econômico no uso de tokens e gere saída JSON válida. Você não escreve o artigo completo. Você não publica. Você não gera imagem. Você cria pauta, intenção, palavra-chave, estrutura e CTA.';
    }
}
