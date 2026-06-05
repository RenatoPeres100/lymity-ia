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
        return "Você é {$name}, {$role} da Lymity IA. " .
               "Gere conteúdo de alta qualidade, estratégico e alinhado com a marca. " .
               "Responda APENAS em JSON válido, sem explicações fora do JSON.";
    }

    private function buildBrandSection(AiExecutionContext $context): string
    {
        $compact = $context->compact_brand_context ?? 'Contexto de marca não disponível.';
        return "=== CONTEXTO DA MARCA ===\n{$compact}";
    }

    private function buildTaskSection(AiExecutionContext $context): string
    {
        $compact = $context->compact_task_context ?? 'Contexto da tarefa não disponível.';
        return "=== TAREFA OPERACIONAL ===\n{$compact}";
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
=== FORMATO DE SAÍDA (JSON obrigatório) ===
Retorne exatamente este JSON e nada mais:
{
  "content_type": "blog_post",
  "title": "título atraente e com keyword",
  "slug": "slug-url-amigavel",
  "subtitle": "subtítulo complementar",
  "excerpt": "resumo de até 160 caracteres para SEO",
  "seo_title": "título SEO com keyword (max 60 chars)",
  "seo_description": "meta descrição com CTA (max 155 chars)",
  "focus_keyword": "palavra-chave principal",
  "secondary_keywords": ["keyword2", "keyword3"],
  "content_html": "<article>conteúdo completo em HTML com subtítulos h2/h3, parágrafos, lista se aplicável</article>",
  "cta_final": "chamada para ação final do artigo",
  "image_prompt": "descrição detalhada para geração de imagem destacada",
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
               "1. Nunca inventar fatos, dados, estatísticas ou notícias.\n" .
               "2. Nunca prometer resultados garantidos ou milagrosos.\n" .
               "3. Nunca usar termos proibidos da marca.\n" .
               "4. Conteúdo deve ser original, estratégico e alinhado ao posicionamento da marca.\n" .
               "5. Responda APENAS com JSON válido e nada mais.";
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
