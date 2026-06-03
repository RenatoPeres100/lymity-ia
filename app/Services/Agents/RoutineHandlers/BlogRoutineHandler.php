<?php

namespace App\Services\Agents\RoutineHandlers;

use App\Models\ActivityLog;
use App\Models\AgentRoutine;
use App\Models\AgentRoutineRun;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Services\AI\AIProviderManager;
use App\Services\Brand\BrandContextService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class BlogRoutineHandler
{
    public function __construct(
        private AIProviderManager  $providerManager,
        private BrandContextService $brandContext,
    ) {}

    /**
     * Create N blog posts for this routine run.
     * Returns ['items_created' => int, 'approvals_created' => int, 'summaries' => array]
     */
    public function handle(AgentRoutine $routine, AgentRoutineRun $run, int $quantity, bool $dryRun = false): array
    {
        $summaries        = [];
        $itemsCreated     = 0;
        $approvalsCreated = 0;

        $recentTitles = BlogPost::where('client_id', null)
            ->latest()->limit(20)->pluck('title')->toArray();

        for ($i = 0; $i < $quantity; $i++) {
            try {
                if ($dryRun) {
                    $summaries[] = "[dry-run] BlogPost #{$i} seria criado.";
                    continue;
                }

                $post = $this->createBlogPost($routine, $run, $recentTitles, $i);
                $recentTitles[] = $post->title;
                $itemsCreated++;

                $run->increment('items_created');
                $this->logActivity('agent_routine_content_generated', $routine, $run, [
                    'model' => 'BlogPost', 'post_id' => $post->id, 'title' => $post->title,
                ]);

                // Create approval request
                if ($routine->requires_approval && !$this->hasExistingApproval($post)) {
                    $this->createApproval($post, $routine, $run);
                    $approvalsCreated++;
                    $run->increment('approvals_created');
                }

                $summaries[] = "BlogPost #{$post->id}: {$post->title}";
            } catch (Throwable $e) {
                $safe = $this->redact($e->getMessage());
                Log::error("[BlogRoutineHandler] Item {$i} failed: {$safe}", ['routine_id' => $routine->id]);
                $summaries[] = "Erro item {$i}: {$safe}";
            }
        }

        return compact('itemsCreated', 'approvalsCreated', 'summaries');
    }

    private function createBlogPost(AgentRoutine $routine, AgentRoutineRun $run, array $recentTitles, int $index): BlogPost
    {
        $provider  = $this->providerManager->provider();
        $brandCtx  = $this->brandContext->getCompactContext();
        $employee  = $routine->aiEmployee;

        $recentStr = empty($recentTitles) ? 'Nenhum post recente.' : implode('; ', array_slice($recentTitles, 0, 10));

        // Step 1: Generate title + outline
        $systemPrompt = <<<PROMPT
Você é o Blog Writer IA da Lymity — agência de inteligência artificial aplicada ao crescimento de negócios digitais.

Contexto da marca:
{$brandCtx}

Você gera artigos de blog profissionais, com profundidade real, orientados a SEO e autoridade. Nunca genéricos.

Temas recentes a EVITAR (não repita): {$recentStr}

Retorne JSON com as chaves exatas:
{
  "title": "...",
  "subtitle": "...",
  "slug_suffix": "...",
  "excerpt": "...",
  "seo_title": "...",
  "seo_description": "...",
  "focus_keyword": "...",
  "tags": "...",
  "outline": "..."
}
PROMPT;

        $planResponse = $provider->generateText([
            'system_prompt' => $systemPrompt,
            'user_message'  => "Gere o planejamento de um artigo de blog para a Lymity IA. Tema: " . $this->pickTheme($brandCtx, $index),
            'json_mode'     => true,
            'max_tokens'    => 1000,
            'temperature'   => 0.8,
        ]);

        if (!$planResponse->success) {
            throw new \RuntimeException("Falha ao gerar plano: {$planResponse->error_message}");
        }

        $plan  = $planResponse->json ?? [];
        // Gemini sometimes wraps scalar values in arrays — flatten them, but keep tags as array
        foreach ($plan as $k => $v) {
            if (is_array($v) && $k !== 'tags') {
                $plan[$k] = implode(', ', array_map('strval', $v));
            }
        }
        $title = $plan['title'] ?? 'Artigo IA — ' . now()->format('d/m/Y H:i');

        // Step 2: Generate full article content
        $outline = $plan['outline'] ?? "Crie um conteúdo profissional sobre {$title}";
        $contentPrompt = <<<PROMPT
Você é o Blog Writer IA da Lymity.

Escreva o artigo completo em HTML (sem <html>/<body>/<head>) com base neste plano:
Título: {$title}
Outline: {$outline}

Regras:
- 600 a 1.200 palavras
- Use <h2> e <h3> para subtítulos
- Use <p> para parágrafos
- Use <strong> para destaques
- Linguagem profissional, clara e direta
- Foco em valor prático para o leitor
- Mencione a Lymity IA onde relevante, sem exagerar

Retorne APENAS o HTML do conteúdo.
PROMPT;

        $contentResponse = $provider->generateText([
            'system_prompt' => 'Você é um redator especialista em marketing de conteúdo e IA aplicada a negócios.',
            'user_message'  => $contentPrompt,
            'json_mode'     => false,
            'max_tokens'    => 2500,
            'temperature'   => 0.75,
        ]);

        if (!$contentResponse->success) {
            throw new \RuntimeException("Falha ao gerar conteúdo: {$contentResponse->error_message}");
        }

        $slugSuffix = Str::slug($plan['slug_suffix'] ?? $title) . '-' . now()->format('YmdHis');

        $scheduledAt = $this->calculateTargetPublicationDate($routine);

        $rawTags = $plan['tags'] ?? 'IA, Automação, Marketing Digital, Lymity';
        $tagsArr = is_array($rawTags)
            ? array_values(array_filter(array_map('trim', $rawTags)))
            : array_values(array_filter(array_map('trim', explode(',', (string)$rawTags))));

        $skwRaw = $plan['secondary_keywords'] ?? null;
        $skwArr = is_array($skwRaw)
            ? array_values(array_filter(array_map('trim', $skwRaw)))
            : (is_string($skwRaw) && !empty(trim($skwRaw))
                ? array_values(array_filter(array_map('trim', explode(',', $skwRaw))))
                : null);

        return BlogPost::create([
            'title'           => $title,
            'slug'            => Str::slug($title) . '-' . substr($slugSuffix, -8),
            'subtitle'        => $plan['subtitle'] ?? null,
            'excerpt'         => is_array($plan['excerpt'] ?? null) ? implode(' ', $plan['excerpt']) : ($plan['excerpt'] ?? null),
            'content'         => $contentResponse->text,
            'seo_title'       => is_string($plan['seo_title'] ?? null) ? $plan['seo_title'] : $title,
            'seo_description' => is_string($plan['seo_description'] ?? null) ? $plan['seo_description'] : null,
            'focus_keyword'      => is_string($plan['focus_keyword'] ?? null) ? $plan['focus_keyword'] : null,
            'secondary_keywords' => $skwArr,
            'tags'               => $tagsArr,
            'ai_employee_id'  => $routine->ai_employee_id,
            'client_id'       => $routine->client_id,
            'company_id'      => $routine->company_id,
            'type'            => $routine->client_id ? 'client' : 'agency',
            'status'          => $routine->requires_approval ? 'pending_approval' : 'draft',
            'requires_approval' => $routine->requires_approval,
            'scheduled_at'    => $scheduledAt,
            'ai_metadata'     => [
                'generated_by_routine_id' => $routine->id,
                'routine_run_id'          => $run->id,
                'provider'                => $planResponse->provider,
                'model'                   => $planResponse->model,
                'outline'                 => $plan['outline'] ?? null,
            ],
        ]);
    }

    private function pickTheme(string $brandCtx, int $index): string
    {
        $themes = [
            'como a inteligência artificial pode transformar a operação de marketing digital',
            'automação de conteúdo com IA: o que mudou e o que ainda vai mudar',
            'social media estratégico com IA: da criação à publicação automatizada',
            'SEO em 2026: como a IA acelera resultados orgânicos',
            'fluxo de aprovação de conteúdo: por que estrutura importa mais que velocidade',
            'agência digital com IA: o novo modelo operacional',
            'métricas que realmente importam para crescimento digital',
            'como construir um sistema de conteúdo escalável para pequenas e médias empresas',
        ];
        return $themes[$index % count($themes)];
    }

    private function calculateTargetPublicationDate(AgentRoutine $routine): ?\Illuminate\Support\Carbon
    {
        $leadDays = max(1, (int) ($routine->approval_lead_days ?? 1));
        $pubTime  = $routine->publication_time ?? $routine->time_of_day ?? '09:00';
        [$h, $m]  = explode(':', $pubTime);
        return now()->addDays($leadDays)->setHour((int)$h)->setMinute((int)$m)->setSecond(0);
    }

    private function hasExistingApproval(BlogPost $post): bool
    {
        return ApprovalRequest::where('approvable_type', BlogPost::class)
            ->where('approvable_id', $post->id)
            ->where('status', 'pending')
            ->exists();
    }

    private function createApproval(BlogPost $post, AgentRoutine $routine, AgentRoutineRun $run): void
    {
        ApprovalRequest::create([
            'client_id'       => $routine->client_id,
            'requested_by'    => null,
            'ai_employee_id'  => $routine->ai_employee_id,
            'approvable_type' => BlogPost::class,
            'approvable_id'   => $post->id,
            'title'           => "Aprovar post de blog: {$post->title}",
            'description'     => "Gerado pela rotina \"{$routine->title}\". Agente: {$routine->aiEmployee?->name}.",
            'approval_type'   => 'blog',
            'status'          => 'pending',
            'sensitive_level' => 'medium',
            'payload'         => [
                'routine_id'     => $routine->id,
                'routine_run_id' => $run->id,
                'company_id'     => $routine->company_id,
            ],
        ]);
    }

    private function logActivity(string $action, AgentRoutine $routine, AgentRoutineRun $run, array $extra = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => null,
                'action'      => $action,
                'module'      => 'agent_routines',
                'level'       => 'info',
                'description' => "BlogRoutineHandler: {$action}",
                'metadata'    => array_merge(['routine_id' => $routine->id, 'run_id' => $run->id], $extra),
            ]);
        } catch (Throwable) {}
    }

    private function redact(string $msg): string
    {
        return preg_replace('/(?:key|token|secret)=[^\s&]+/i', '***', $msg);
    }
}
