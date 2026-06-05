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
        $this->requireNonEmpty($payload, 'title', 'Blog Post');

        // Accept either content_html or content_markdown
        if (empty($payload['content_html']) && empty($payload['content_markdown'])) {
            throw new AIInvalidJsonResponseException(
                "Payload do blog post inválido: campo 'content_html' ou 'content_markdown' é obrigatório."
            );
        }

        // Convert markdown to HTML if only markdown provided
        if (empty($payload['content_html']) && !empty($payload['content_markdown'])) {
            $payload['content_html'] = $this->markdownToHtml($payload['content_markdown']);
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
        $payload['focus_keyword']   = $payload['focus_keyword'] ?? '';
        $payload['cta_final']       = $payload['cta_final'] ?? '';
        $payload['image_prompt']    = $payload['image_prompt'] ?? "Professional image for article: {$payload['title']}";
        $payload['sources_used']    = $payload['sources_used'] ?? [];

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
