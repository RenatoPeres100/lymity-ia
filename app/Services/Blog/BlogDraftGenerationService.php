<?php

namespace App\Services\Blog;

use App\Models\AiEmployee;
use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\ContentBrief;
use App\Models\User;
use App\Services\AI\AICostGuardService;
use App\Services\AI\AIProviderManager;
use App\Services\AI\AITaskLogService;
use App\Services\Brand\BrandContextService;
use Illuminate\Support\Str;

class BlogDraftGenerationService
{
    public function __construct(
        private readonly AIProviderManager   $providerManager,
        private readonly AICostGuardService  $costGuard,
        private readonly BrandContextService $brandContext,
        private readonly AITaskLogService    $logService,
    ) {}

    public function generateFromBrief(ContentBrief $brief, User $user): BlogPost
    {
        $this->brandContext->validateReadyForGeneration();
        $this->costGuard->assertCanGenerate('generate_draft');

        $writer = AiEmployee::where('slug', 'blog-writer-ia')
            ->where('status', 'active')
            ->first();

        if (!$writer) {
            throw new \RuntimeException('Blog Writer IA não encontrado ou inativo. Verifique os agentes em /admin/employees.');
        }

        $brandCtx     = $this->brandContext->getCompactContext();
        $systemPrompt = $writer->system_prompt ?? $this->defaultWriterSystemPrompt();
        $userMessage  = $this->buildWriterPrompt($brief, $brandCtx);

        $startedAt = now();
        $provider  = $this->providerManager->provider();

        $response = $provider->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => $userMessage,
            'json_mode'     => true,
            'max_tokens'    => config('ai.max_output_tokens', 3500),
            'temperature'   => config('ai.temperature', 0.7),
        ]);

        if (!$response->success) {
            $this->logService->logGeneration([
                'task_type'     => 'generate_draft',
                'status'        => 'error',
                'provider'      => $response->provider,
                'model'         => $response->model,
                'error_message' => $response->error_message,
                'agent_id'      => $writer->id,
                'message'       => "Falha ao gerar artigo: {$response->error_message}",
                'level'         => 'error',
                'started_at'    => $startedAt,
                'payload'       => ['brief_id' => $brief->id],
            ]);
            throw new \RuntimeException("Falha ao gerar artigo: {$response->error_message}");
        }

        $json = $response->json;
        if (empty($json)) {
            throw new \RuntimeException('Blog Writer IA retornou resposta vazia ou JSON inválido.');
        }

        $slug = $json['slug'] ?? Str::slug($json['title'] ?? 'artigo-' . now()->format('YmdHis'));
        $slug = $this->uniqueSlug($slug);

        $post = BlogPost::create([
            'title'             => $json['title'] ?? $brief->title,
            'slug'              => $slug,
            'subtitle'          => $json['subtitle'] ?? null,
            'excerpt'           => $json['excerpt'] ?? null,
            'content'           => $json['content_html'] ?? '',
            'seo_title'         => $json['seo_title'] ?? null,
            'seo_description'   => $json['seo_description'] ?? null,
            'focus_keyword'     => $json['focus_keyword'] ?? $brief->primary_keyword,
            'secondary_keywords'=> $json['secondary_keywords'] ?? $brief->secondary_keywords ?? [],
            'type'              => 'agency',
            'status'            => 'pending_approval',
            'author_id'         => $user->id,
            'generated_by_agent_id' => $writer->id,
            'ai_employee_id'    => $writer->id,
            'content_brief_id'  => $brief->id,
            'featured_image'    => null,
            'ai_metadata'       => [
                'cta_final'          => $json['cta_final'] ?? null,
                'model'              => $response->model,
                'fallback_used'      => $response->fallback_used,
                'input_tokens'       => $response->input_tokens,
                'output_tokens'      => $response->output_tokens,
                'estimated_cost_usd' => $response->estimated_cost_usd,
                'generated_by'       => $user->email,
                'brief_id'           => $brief->id,
            ],
        ]);

        // Create ApprovalRequest
        ApprovalRequest::create([
            'approvable_type'  => BlogPost::class,
            'approvable_id'    => $post->id,
            'title'            => 'Aprovação: ' . $post->title,
            'description'      => "Artigo gerado por Blog Writer IA aguardando revisão e aprovação.",
            'approval_type'    => 'blog',
            'status'           => 'pending',
            'sensitive_level'  => 'medium',
            'ai_employee_id'   => $writer->id,
            'requested_by'     => $user->id,
            'payload'          => [
                'blog_post_id'       => $post->id,
                'content_brief_id'   => $brief->id,
                'seo_title'          => $post->seo_title,
                'seo_description'    => $post->seo_description,
                'focus_keyword'      => $post->focus_keyword,
                'model'              => $response->model,
                'estimated_cost_usd' => $response->estimated_cost_usd,
                'input_tokens'       => $response->input_tokens,
                'output_tokens'      => $response->output_tokens,
            ],
        ]);

        // Mark brief as used
        $brief->update(['status' => 'used']);

        // Register usage
        $this->costGuard->registerUsage($response, [
            'agent_id'     => $writer->id,
            'task_type'    => 'generate_draft',
            'related_type' => BlogPost::class,
            'related_id'   => $post->id,
        ]);

        // Log
        $this->logService->fromResponse($response, [
            'task_type'    => 'generate_draft',
            'agent_id'     => $writer->id,
            'related_type' => BlogPost::class,
            'related_id'   => $post->id,
            'started_at'   => $startedAt,
            'payload'      => ['brief_id' => $brief->id],
        ]);

        ActivityLog::create([
            'user_id'      => $user->id,
            'action'       => 'generate_draft',
            'module'       => 'blog',
            'level'        => 'info',
            'description'  => "Artigo gerado por Blog Writer IA: {$post->title}",
            'subject_type' => BlogPost::class,
            'subject_id'   => $post->id,
            'metadata'     => ['post_id' => $post->id, 'brief_id' => $brief->id, 'agent' => 'blog-writer-ia'],
        ]);

        $writer->update(['last_run_at' => now()]);

        return $post;
    }

    private function buildWriterPrompt(ContentBrief $brief, string $brandCtx): string
    {
        $outline = collect($brief->outline ?? [])->map(fn($o) =>
            "- {$o['heading']}: {$o['notes']}"
        )->join("\n");

        return <<<PROMPT
Contexto da marca:
{$brandCtx}

Briefing do artigo:
Título sugerido: {$brief->title}
Tópico: {$brief->topic}
Objetivo: {$brief->goal}
Público: {$brief->audience}
Palavra-chave principal: {$brief->primary_keyword}
Palavras secundárias: {$this->formatList($brief->secondary_keywords ?? [])}
Funil: {$brief->funnel_stage}
Intenção: {$brief->search_intent}
CTA sugerido: {$brief->cta_suggestion}

Estrutura sugerida:
{$outline}

Escreva o artigo completo em português do Brasil. Use H2 e H3. Finalize com um CTA real para a Lymity IA.
Responda APENAS com JSON válido neste formato exato:
{
  "title": "string",
  "slug": "string",
  "subtitle": "string",
  "excerpt": "string",
  "seo_title": "string",
  "seo_description": "string",
  "focus_keyword": "string",
  "secondary_keywords": ["string"],
  "content_html": "string",
  "cta_final": "string"
}
PROMPT;
    }

    private function formatList(array $items): string
    {
        return implode(', ', $items) ?: '—';
    }

    private function uniqueSlug(string $slug): string
    {
        $base = $slug;
        $n    = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    private function defaultWriterSystemPrompt(): string
    {
        return 'Você é o Blog Writer IA da Lymity IA. Sua função é escrever artigos estratégicos para o blog da agência, com tom especialista, claro, profundo e direto. Gere saída JSON válida com título, slug, excerpt, meta title, meta description, palavra-chave principal, palavras secundárias, conteúdo HTML e CTA final. Não publique. Não gere imagem. O artigo deve ficar aguardando aprovação.';
    }
}
