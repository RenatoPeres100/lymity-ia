<?php

namespace App\Services\AI\Response;

use App\Exceptions\AIInvalidJsonResponseException;
use Illuminate\Support\Str;

class AIContentPayloadValidatorService
{
    /**
     * Validate and normalize a blog post payload.
     */
    public function validateBlogPostPayload(array $payload): array
    {
        // Detect and reject generation_incomplete sentinel
        if (isset($payload['error']) && $payload['error'] === 'generation_incomplete') {
            throw new AIInvalidJsonResponseException(
                "A IA sinalizou que não conseguiu gerar o artigo completo. Motivo: " . ($payload['reason'] ?? 'desconhecido')
            );
        }

        $this->requireNonEmpty($payload, 'title', 'Blog Post');

        // Accept content under any known alias — ordered by preference
        $contentFieldAliases = [
            'content_markdown', 'content_html',
            'content', 'body', 'article', 'article_content',
            'text', 'html', 'markdown', 'post_content',
        ];
        $contentValue = null;
        $contentField = null;
        foreach ($contentFieldAliases as $alias) {
            if (!empty($payload[$alias])) {
                $contentValue = $payload[$alias];
                // Gemini sometimes returns content as array of paragraphs — flatten to string
                if (is_array($contentValue)) {
                    $contentValue = implode("\n\n", array_map(fn($v) => is_string($v) ? $v : json_encode($v), $contentValue));
                    $payload[$alias] = $contentValue;
                }
                $contentField = $alias;
                break;
            }
        }

        if (empty($contentValue)) {
            \Illuminate\Support\Facades\Log::warning('[PayloadValidator] Blog post sem campo de conteúdo. Campos recebidos: ' . implode(', ', array_keys($payload)));
            throw new AIInvalidJsonResponseException(
                "Payload do blog post inválido: nenhum campo de conteúdo encontrado. " .
                "Esperado: content_markdown ou content_html. Campos recebidos: " . implode(', ', array_keys($payload))
            );
        }

        // Always populate both content_html and content_markdown
        if ($contentField === 'content_html' || $contentField === 'html') {
            $payload['content_html']     = $contentValue;
            $payload['content_markdown'] = $payload['content_markdown'] ?? strip_tags($contentValue);
        } else {
            // content_markdown, content, body, article, article_content, text, markdown, post_content
            $payload['content_markdown'] = $contentValue;
            $payload['content_html']     = $this->markdownToHtml($contentValue);
        }

        // Normalize slug
        if (empty($payload['slug'])) {
            $payload['slug'] = Str::slug($payload['title']);
        } else {
            $payload['slug'] = Str::slug($payload['slug']);
        }

        // Normalize secondary_keywords to array
        if (isset($payload['secondary_keywords']) && is_string($payload['secondary_keywords'])) {
            $payload['secondary_keywords'] = array_values(array_filter(
                array_map('trim', explode(',', $payload['secondary_keywords']))
            ));
        }

        // Normalize hashtags to array
        if (isset($payload['hashtags']) && is_string($payload['hashtags'])) {
            $payload['hashtags'] = array_values(array_filter(
                array_map('trim', preg_split('/[\s,]+/', $payload['hashtags']))
            ));
        }

        // Fill optional fields with defaults
        $payload['excerpt']         = $payload['excerpt'] ?? Str::limit(strip_tags($payload['content_html']), 155);
        $payload['seo_title']       = $payload['seo_title'] ?? Str::limit($payload['title'], 60);
        $payload['seo_description'] = $payload['seo_description'] ?? ($payload['excerpt'] ?? '');
        // meta_description mirrors seo_description
        $payload['meta_description'] = $payload['meta_description']
            ?? $payload['seo_description']
            ?? ($payload['excerpt'] ?? '');
        // sync back
        if (empty($payload['seo_description']) && !empty($payload['meta_description'])) {
            $payload['seo_description'] = $payload['meta_description'];
        }
        $payload['focus_keyword']   = $payload['focus_keyword'] ?? '';
        $payload['cta_final']       = $payload['cta_final'] ?? '';
        $payload['image_prompt']    = $payload['image_prompt'] ?? "Professional image for article: {$payload['title']}";
        $payload['image_alt']       = $payload['image_alt'] ?? $payload['title'];
        $payload['image_caption']   = $payload['image_caption'] ?? null;
        $payload['sources_used']    = $payload['sources_used'] ?? [];

        // Normalize tags
        if (!isset($payload['tags'])) {
            // derive from secondary_keywords or focus_keyword
            $derived = array_filter(array_merge(
                [$payload['focus_keyword'] ?? null],
                (array)($payload['secondary_keywords'] ?? [])
            ));
            $payload['tags'] = array_values(array_unique(array_filter(array_map('trim', $derived))));
        } elseif (is_string($payload['tags'])) {
            $payload['tags'] = array_values(array_filter(array_map('trim', explode(',', $payload['tags']))));
        }
        $payload['tags'] = (array)($payload['tags'] ?? []);

        // Normalize categories
        if (!isset($payload['categories'])) {
            $payload['categories'] = ['Marketing Digital', 'Estratégia'];
        } elseif (is_string($payload['categories'])) {
            $payload['categories'] = array_values(array_filter(array_map('trim', explode(',', $payload['categories']))));
        }
        $payload['categories'] = (array)($payload['categories'] ?? []);

        // Normalize subtitle
        if (empty($payload['subtitle'])) {
            $payload['subtitle'] = Str::limit($payload['excerpt'] ?? $payload['title'], 120);
        }

        // === Minimum word count validation ===
        $minWords    = config('ai.blog_min_words', 500);
        $contentText = $payload['content_markdown']
            ?? strip_tags($payload['content_html'] ?? '');
        $wordCount   = str_word_count(preg_replace('/#+\s*|\*\*?|__?/', '', $contentText));

        if ($wordCount < $minWords) {
            throw new AIInvalidJsonResponseException(
                "Payload do blog post incompleto: artigo possui apenas {$wordCount} palavras. " .
                "Mínimo esperado: {$minWords}. O campo content_markdown deve conter o artigo completo."
            );
        }

        $payload['_word_count'] = $wordCount;

        return $payload;
    }

    /**
     * Validate and normalize an Instagram post payload.
     */
    public function validateInstagramPostPayload(array $payload): array
    {
        $this->requireNonEmpty($payload, 'title', 'Instagram Post');
        $this->requireNonEmpty($payload, 'caption', 'Instagram Post');

        // Normalize hashtags
        $payload['hashtags']  = $this->normalizeHashtags($payload['hashtags'] ?? []);
        $payload['cta']       = $payload['cta'] ?? '';
        $payload['creative_brief'] = $payload['creative_brief'] ?? '';
        $payload['image_prompt']   = $payload['image_prompt'] ?? "Professional Instagram image for: {$payload['title']}";

        return $payload;
    }

    /**
     * Validate and normalize an Instagram carousel payload.
     */
    public function validateInstagramCarouselPayload(array $payload): array
    {
        $this->requireNonEmpty($payload, 'title', 'Instagram Carousel');
        $this->requireNonEmpty($payload, 'caption', 'Instagram Carousel');

        if (empty($payload['slides']) || !is_array($payload['slides'])) {
            throw new AIInvalidJsonResponseException(
                "Payload do carrossel inválido: campo 'slides' é obrigatório e deve ser um array."
            );
        }

        // Validate each slide
        foreach ($payload['slides'] as $i => $slide) {
            $num = $i + 1;
            if (empty($slide['headline'])) {
                throw new AIInvalidJsonResponseException("Slide {$num} sem 'headline'.");
            }
        }

        $payload['hashtags'] = $this->normalizeHashtags($payload['hashtags'] ?? []);
        $payload['cta']      = $payload['cta'] ?? '';

        return $payload;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function requireNonEmpty(array $payload, string $field, string $schema): void
    {
        if (empty($payload[$field])) {
            throw new AIInvalidJsonResponseException(
                "Payload de {$schema} inválido: campo '{$field}' é obrigatório e não pode ser vazio."
            );
        }
    }

    private function normalizeHashtags(mixed $hashtags): array
    {
        if (is_array($hashtags)) {
            return array_values(array_filter(array_map('trim', $hashtags)));
        }
        if (is_string($hashtags)) {
            return array_values(array_filter(
                array_map('trim', preg_split('/[\s,#]+/', $hashtags))
            ));
        }
        return [];
    }

    /**
     * Simple markdown-to-HTML converter for common patterns.
     * Keeps content usable without requiring a full Markdown library.
     */
    private function markdownToHtml(string $markdown): string
    {
        $html = htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);

        // Headers
        $html = preg_replace('/^#{3}\s+(.+)$/m', '<h3>$1</h3>', $html) ?? $html;
        $html = preg_replace('/^#{2}\s+(.+)$/m', '<h2>$1</h2>', $html) ?? $html;
        $html = preg_replace('/^#{1}\s+(.+)$/m', '<h2>$1</h2>', $html) ?? $html;

        // Bold / italic
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html) ?? $html;
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html) ?? $html;

        // Unordered lists
        $html = preg_replace('/^[-*]\s+(.+)$/m', '<li>$1</li>', $html) ?? $html;
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html) ?? $html;

        // Paragraphs (lines not starting with HTML tags)
        $lines  = explode("\n", $html);
        $result = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            if (preg_match('/^<(h[2-6]|ul|li|ol|p|div|article)/', $trimmed)) {
                $result .= $trimmed . "\n";
            } else {
                $result .= "<p>{$trimmed}</p>\n";
            }
        }

        return '<article>' . $result . '</article>';
    }
}
