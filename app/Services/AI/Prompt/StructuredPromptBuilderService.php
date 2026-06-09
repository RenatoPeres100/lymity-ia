<?php

namespace App\Services\AI\Prompt;

use App\Models\AgentTask;
use App\Models\AiEmployee;
use App\Models\AiExecutionContext;
use App\Services\AI\Memory\AIMemoryService;
use Illuminate\Support\Collection;

class StructuredPromptBuilderService
{
    public function __construct(
        private AIMemoryService $memoryService,
    ) {}

    public function buildForTaskExecution(
        AgentTask $task,
        AiEmployee $employee,
        AiExecutionContext $context
    ): string {
        return match (true) {
            $task->isBlogType()     => $this->buildBlogPostPrompt($task, $employee, $context),
            $task->isCarouselType() => $this->buildCarouselPrompt($task, $employee, $context),
            $task->isInstagramType()=> $this->buildInstagramPostPrompt($task, $employee, $context),
            default                 => $this->buildGenericPrompt($task, $employee, $context),
        };
    }

    public function buildBlogPostPrompt(
        AgentTask $task,
        AiEmployee $employee,
        AiExecutionContext $context
    ): string {
        $memories = $this->getMemoriesFromContext($context);

        $prompt  = $this->buildHeader($employee);
        $prompt .= "\n\n" . $this->buildBrandSection($context);
        $prompt .= "\n\n" . $this->buildTaskSection($context);
        if ($memories->isNotEmpty()) {
            $prompt .= "\n\n" . $this->memoryService->formatMemoriesForPrompt($memories);
        }
        if (!empty($context->external_research_snapshot)) {
            $prompt .= "\n\n" . $this->buildResearchSection($context->external_research_snapshot);
        }
        $prompt .= "\n\n" . $this->buildBlogOutputRules();
        $prompt .= "\n\n" . $this->buildSecurityRules();

        return $prompt;
    }

    public function buildInstagramPostPrompt(
        AgentTask $task,
        AiEmployee $employee,
        AiExecutionContext $context
    ): string {
        $memories = $this->getMemoriesFromContext($context);

        $prompt  = $this->buildHeader($employee);
        $prompt .= "\n\n" . $this->buildBrandSection($context);
        $prompt .= "\n\n" . $this->buildTaskSection($context);
        if ($memories->isNotEmpty()) {
            $prompt .= "\n\n" . $this->memoryService->formatMemoriesForPrompt($memories);
        }
        if (!empty($context->external_research_snapshot)) {
            $prompt .= "\n\n" . $this->buildResearchSection($context->external_research_snapshot);
        }
        $prompt .= "\n\n" . $this->buildInstagramOutputRules();
        $prompt .= "\n\n" . $this->buildSecurityRules();

        return $prompt;
    }

    public function buildCarouselPrompt(
        AgentTask $task,
        AiEmployee $employee,
        AiExecutionContext $context
    ): string {
        $slidesCount = $task->carousel_slides_count ?: 5;
        $memories    = $this->getMemoriesFromContext($context);

        $prompt  = $this->buildHeader($employee);
        $prompt .= "\n\n" . $this->buildBrandSection($context);
        $prompt .= "\n\n" . $this->buildTaskSection($context);
        if ($memories->isNotEmpty()) {
            $prompt .= "\n\n" . $this->memoryService->formatMemoriesForPrompt($memories);
        }
        $prompt .= "\n\n" . $this->buildCarouselOutputRules($slidesCount);
        $prompt .= "\n\n" . $this->buildSecurityRules();

        return $prompt;
    }

    public function buildImagePrompt(AgentTask $task, array $textPayload, AiExecutionContext $context): string
    {
        $brandContext = $context->compact_brand_context ?? '';
        $imagePrompt  = $textPayload['image_prompt'] ?? '';
        $title        = $textPayload['title'] ?? '';

        if ($imagePrompt) {
            $base = $imagePrompt;
        } else {
            $base = "Imagem profissional para: {$title}";
        }

        $visual = '';
        if (str_contains($brandContext, 'Visual:')) {
            preg_match('/Visual:\s*([^.]+)/', $brandContext, $m);
            $visual = $m[1] ?? '';
        }

        if ($visual) {
            $base .= ". Estilo visual: {$visual}";
        }

        return $base . '. Não incluir texto na imagem. Alta qualidade, profissional.';
    }

    public function buildResearchPromptIfNeeded(AgentTask $task, AiExecutionContext $context): ?string
    {
        if (!$task->requires_external_research) return null;
        if (!config('external-research.enabled', false)) return null;

        $topics  = $task->external_research_topics ?? 'tópicos relevantes';
        $days    = $task->external_research_recency_days ?? 7;
        $brand   = $context->compact_brand_context ?? '';

        return "Pesquise notícias e tendências recentes dos últimos {$days} dias sobre: {$topics}. " .
               "Contexto da marca: {$brand}. " .
               "Retorne apenas informações verificáveis e relevantes para o público. " .
               "Formato de resposta: JSON com array 'results' contendo 'title', 'summary', 'relevance'.";
    }

    public function sanitizePromptPreview(string $prompt): string
    {
        // Remove API keys, tokens, email addresses from preview
        $sanitized = preg_replace('/\b[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}\b/', '[email]', $prompt);
        $sanitized = preg_replace('/\b(sk-|AIza|ya29\.|Bearer\s+)[A-Za-z0-9\-_\.]{10,}\b/', '[token]', $sanitized ?? $prompt);
        return mb_substr($sanitized ?? $prompt, 0, 1000);
    }

    // ── Private builders ──────────────────────────────────────────────────────

    private function buildHeader(AiEmployee $employee): string
    {
        $name = $employee->name ?? 'Funcionário IA';
        $role = $employee->role ?? 'Content Creator';
        return "Você é {$name}, especialista em {$role} da Lymity IA — agência de marketing digital e automação inteligente.\n" .
               "Sua missão é gerar conteúdo de alta qualidade, profundo, estratégico e 100% alinhado com a marca e as instruções da tarefa.\n" .
               "IMPORTANTE: Responda APENAS com o JSON solicitado. Nenhum texto fora do JSON.";
    }

    private function buildBrandSection(AiExecutionContext $context): string
    {
        $compact = $context->compact_brand_context ?? '';
        if (!$compact || str_contains($compact, 'não configurado')) {
            return "=== CONTEXTO DA MARCA ===\nUse posicionamento profissional, linguagem especialista e estratégica.";
        }
        return "=== CONTEXTO DA MARCA (siga rigorosamente) ===\n{$compact}";
    }

    private function buildTaskSection(AiExecutionContext $context): string
    {
        $compact = $context->compact_task_context ?? 'Contexto da tarefa não disponível.';
        return "=== TAREFA E INSTRUÇÕES OBRIGATÓRIAS (siga cada instrução) ===\n{$compact}";
    }

    private function buildResearchSection(array $research): string
    {
        if (empty($research)) return '';
        $lines = ["=== PESQUISA EXTERNA (use como inspiração, não invente fatos) ==="];
        foreach (array_slice($research, 0, 5) as $item) {
            $lines[] = "- " . ($item['title'] ?? '') . ": " . ($item['summary'] ?? '');
        }
        return implode("\n", $lines);
    }

    private function buildBlogOutputRules(): string
    {
        return <<<'JSON'
=== INSTRUÇÕES OBRIGATÓRIAS DE FORMATO ===
RESPONDA APENAS JSON VÁLIDO.
NÃO use markdown fora do JSON.
NÃO use bloco ```json.
NÃO escreva explicações antes ou depois do JSON.
O campo content_markdown é obrigatório — não use content, body, article ou text.
Escape corretamente quebras de linha dentro das strings como \n.
Não use caracteres de controle literais.
Não use vírgulas finais.

=== PADRÕES DE QUALIDADE OBRIGATÓRIOS ===
- O artigo deve ter NO MÍNIMO 800 palavras no campo content_markdown
- Use ## para seções principais, ### para subseções (formato Markdown)
- Inclua introdução envolvente, desenvolvimento com dados/exemplos e conclusão com CTA
- O conteúdo deve ser original, profundo e verdadeiramente útil para o leitor
- Conecte o assunto ao posicionamento e serviços da marca sempre que natural
- O CTA final deve ser específico e alinhado ao Brand Context

=== FORMATO DE SAÍDA (retorne APENAS este JSON, sem markdown externo) ===
{
  "content_type": "blog_post",
  "title": "título atraente direto e com a keyword principal",
  "slug": "slug-url-amigavel-sem-acentos",
  "subtitle": "subtítulo que complementa o título",
  "excerpt": "resumo atrativo de até 160 caracteres para SEO",
  "seo_title": "título SEO com keyword no início max 60 chars",
  "seo_description": "meta descrição com benefício e CTA max 155 chars",
  "focus_keyword": "palavra-chave principal do artigo",
  "secondary_keywords": ["keyword 2", "keyword 3"],
  "content_markdown": "## Introdução\n\nTexto da introdução...\n\n## Seção Principal\n\nTexto da seção...\n\n## Conclusão\n\nTexto final com CTA.",
  "cta_final": "chamada para ação alinhada ao Brand Context",
  "image_prompt": "professional image for: describe scene in english",
  "sources_used": []
}
JSON;
    }

    private function buildInstagramOutputRules(): string
    {
        return <<<'JSON'
=== FORMATO DE SAÍDA (JSON obrigatório) ===
Retorne exatamente este JSON e nada mais:
{
  "content_type": "instagram_post",
  "title": "título interno para referência",
  "caption": "legenda completa do post (até 2200 chars, com quebras de linha)",
  "hashtags": ["hashtag1", "hashtag2"],
  "cta": "chamada para ação",
  "creative_brief": "briefing criativo para designer",
  "image_prompt": "descrição detalhada para geração da imagem"
}
JSON;
    }

    private function buildCarouselOutputRules(int $slidesCount): string
    {
        return <<<JSON
=== FORMATO DE SAÍDA (JSON obrigatório) ===
Retorne exatamente este JSON e nada mais:
{
  "content_type": "instagram_carousel",
  "title": "título interno",
  "caption": "legenda principal do carrossel",
  "hashtags": ["hashtag1", "hashtag2"],
  "cta": "chamada para ação",
  "slides": [
    {
      "slide_number": 1,
      "headline": "headline do slide (max 8 palavras)",
      "body": "texto do slide (max 30 palavras)",
      "visual_direction": "direção visual para o designer",
      "image_prompt": "prompt para geração de imagem deste slide"
    }
  ]
}
Gere exatamente {$slidesCount} slides no array slides.
JSON;
    }

    private function buildGenericPrompt(
        AgentTask $task,
        AiEmployee $employee,
        AiExecutionContext $context
    ): string {
        return $this->buildHeader($employee) . "\n\n" .
               $this->buildBrandSection($context) . "\n\n" .
               $this->buildTaskSection($context) . "\n\n" .
               "Retorne um JSON com o conteúdo gerado de acordo com as instruções da tarefa.\n\n" .
               $this->buildSecurityRules();
    }

    private function buildSecurityRules(): string
    {
        return "=== REGRAS OBRIGATÓRIAS ===\n" .
               "1. Siga TODAS as instruções da tarefa acima — elas são mandatórias.\n" .
               "2. Respeite o Brand Context — tom, público, CTAs e termos proibidos.\n" .
               "3. Nunca inventar fatos, dados ou estatísticas sem base.\n" .
               "4. Nunca prometer resultados garantidos ou linguagem de 'fórmula mágica'.\n" .
               "5. O conteúdo deve ser original, profundo e genuinamente útil.\n" .
               "6. Responda APENAS com JSON válido. Nenhum texto, comentário ou markdown fora do JSON.";
    }

    private function getMemoriesFromContext(AiExecutionContext $context): Collection
    {
        $memories = $context->selected_memories ?? [];
        if (empty($memories)) return collect();

        return AiMemory::whereIn('id', array_column($memories, 'id'))
            ->active()
            ->get();
    }
}
