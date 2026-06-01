<?php

namespace App\Services\Social;

use App\Models\SocialPost;
use App\Services\Brand\BrandContextService;

class SocialImagePromptService
{
    public function __construct(private BrandContextService $brandContext) {}

    public function buildSingleImagePrompt(SocialPost $post): string
    {
        if (!empty($post->image_prompt)) {
            return $post->image_prompt;
        }

        $brand     = mb_substr($this->brandContext->getCompactContext(), 0, 400);
        $caption   = mb_substr(trim($post->main_caption ?? $post->creative_brief ?? $post->title), 0, 300);
        $objective = $post->objective_label;
        $cta       = trim($post->cta ?? '');
        $hashtags  = trim($post->hashtags ?? '');
        $format    = $post->content_type_label;
        $tone      = $post->metadata['tone'] ?? 'profissional, moderno e direto';
        $audience  = $post->metadata['target_audience'] ?? 'empreendedores e gestores de negócios digitais';
        $brief     = mb_substr(trim($post->creative_brief ?? ''), 0, 200);

        $prompt  = "Crie uma imagem quadrada 1080x1080 pixels para Instagram da Lymity IA — agência de inteligência artificial aplicada ao crescimento de negócios digitais.\n\n";
        $prompt .= "ESTILO VISUAL OBRIGATÓRIO:\n";
        $prompt .= "Premium, moderno e tecnológico. Visual limpo, elegante, com sensação de empresa de alto nível aplicada ao crescimento de negócios. ";
        $prompt .= "Paleta: tons de azul profundo, roxo digital, branco, preto com detalhes luminosos. Gradiente sofisticado. ";
        $prompt .= "Elementos abstratos de inteligência artificial, automação, dados, estratégia e crescimento digital quando coerente com o tema.\n\n";
        $prompt .= "CONTEÚDO DO POST:\n";
        $prompt .= "Objetivo: {$objective}\n";
        $prompt .= "Formato: {$format}\n";
        $prompt .= "Tom: {$tone}\n";
        $prompt .= "Público: {$audience}\n";
        $prompt .= "Legenda: {$caption}\n";
        if ($cta) $prompt .= "CTA: {$cta}\n";
        if ($brief) $prompt .= "Brief visual: {$brief}\n";
        if ($brand) $prompt .= "Contexto da marca: {$brand}\n";
        $prompt .= "\nREGRAS OBRIGATÓRIAS:\n";
        $prompt .= "- A imagem deve ser diretamente coerente com a legenda e mensagem do post\n";
        $prompt .= "- Sem texto pequeno ou ilegível — se houver texto, use poucas palavras, grandes e em destaque\n";
        $prompt .= "- Prefira sem texto quando a legenda já comunica a ideia\n";
        $prompt .= "- Sem logotipos de terceiros\n";
        $prompt .= "- Sem rostos reais identificáveis\n";
        $prompt .= "- Sem elementos sem relação com o tema do post\n";
        $prompt .= "- Sem placeholders, imagens genéricas ou visuais de banco de imagem\n";
        $prompt .= "- Arte de alta qualidade, adequada para feed profissional do Instagram\n";
        $prompt .= "- Proporção exatamente quadrada 1:1";

        return $prompt;
    }

    public function buildCarouselPlanPrompt(SocialPost $post): string
    {
        $caption  = mb_substr(trim($post->main_caption ?? ''), 0, 600);
        $cta      = trim($post->cta ?? '');
        $objective = $post->objective_label;
        $min      = config('social.carousel.min_slides', 3);
        $max      = config('social.carousel.max_slides', 5);

        return "Analise o texto abaixo de um post da Lymity IA e crie um plano de carrossel visual para Instagram.\n\n"
            . "Legenda do post:\n{$caption}\n\n"
            . "Objetivo: {$objective}\n"
            . ($cta ? "CTA: {$cta}\n\n" : "\n")
            . "REGRAS:\n"
            . "- Crie entre {$min} e {$max} slides\n"
            . "- Cada slide deve ter uma mensagem visual única e conectada ao tema\n"
            . "- O último slide deve ter o CTA\n"
            . "- Use estrutura educativa, sequencial ou estratégica\n\n"
            . "Responda SOMENTE com JSON válido neste formato:\n"
            . "{\n"
            . "  \"recommended\": true,\n"
            . "  \"reason\": \"motivo curto\",\n"
            . "  \"slides\": [\n"
            . "    {\n"
            . "      \"position\": 1,\n"
            . "      \"visual_goal\": \"o que esta imagem deve comunicar visualmente\",\n"
            . "      \"message\": \"mensagem principal deste slide\",\n"
            . "      \"suggested_short_text\": \"texto curto opcional para a imagem ou null\"\n"
            . "    }\n"
            . "  ]\n"
            . "}";
    }

    public function buildCarouselSlidePrompt(SocialPost $post, array $slidePlan, int $position): string
    {
        $brand     = mb_substr($this->brandContext->getCompactContext(), 0, 300);
        $caption   = mb_substr(trim($post->main_caption ?? ''), 0, 200);
        $total     = count($slidePlan);
        $slide     = collect($slidePlan)->firstWhere('position', $position) ?? $slidePlan[$position - 1] ?? [];
        $goal      = $slide['visual_goal'] ?? 'slide do carrossel';
        $message   = $slide['message'] ?? '';
        $shortText = $slide['suggested_short_text'] ?? null;

        $prompt  = "Crie a imagem {$position} de {$total} de um carrossel para Instagram da Lymity IA.\n\n";
        $prompt .= "ESTILO: Premium, moderno, tecnológico. Paleta azul profundo, roxo digital, branco, com detalhes luminosos. Visual coeso com os outros slides do carrossel.\n\n";
        $prompt .= "ESTE SLIDE:\n";
        $prompt .= "Objetivo visual: {$goal}\n";
        $prompt .= "Mensagem: {$message}\n";
        if ($shortText) $prompt .= "Texto sugerido na imagem: {$shortText}\n";
        $prompt .= "Contexto geral do carrossel: {$caption}\n";
        if ($brand) $prompt .= "Marca: {$brand}\n";
        $prompt .= "\nREGRAS:\n";
        $prompt .= "- Imagem 1080x1080, coerente com o slide anterior e posterior\n";
        $prompt .= "- Sem logotipos de terceiros, sem rostos reais\n";
        $prompt .= "- Visual profissional para Instagram Business\n";
        if ($shortText) {
            $prompt .= "- Texto grande, legível, centralizado se incluir texto\n";
        } else {
            $prompt .= "- Preferência por sem texto ou com texto mínimo\n";
        }

        return $prompt;
    }

    public function shouldSuggestCarousel(SocialPost $post): bool
    {
        $caption = trim($post->main_caption ?? '');
        if (empty($caption)) {
            return false;
        }

        // Educational/list content signals
        $signals = [
            '/\d+\.\s/m',                          // numbered list: 1. 2.
            '/^[-•–]\s/m',                          // bullet points
            '/passo\s+a\s+passo/i',                // step by step
            '/etap[ao]/i',                         // stages
            '/primeiro[,:]|segundo[,:]|terceiro/i',// ordinal steps
            '/como\s+/i',                          // "how to"
            '/vantagens|benefícios|motivos/i',     // advantages/benefits
            '/compare|versus|vs\./i',              // comparisons
            '/framework|método|metodologia/i',     // frameworks
            '/aprend[ae]|entend[ae]/i',            // learning
        ];

        $matches = 0;
        foreach ($signals as $pattern) {
            if (preg_match($pattern, $caption)) {
                $matches++;
            }
        }

        // Also check word count — long posts lean carousel
        $wordCount = str_word_count($caption, 0, 'áàâãéèêíïóõôúçñüÁÀÂÃÉÈÊÍÏÓÕÔÚÇÑÜ');

        return $matches >= 2 || $wordCount > 120;
    }

    public function hashPostVisualSource(SocialPost $post): string
    {
        return md5(implode('|', [
            trim($post->main_caption ?? ''),
            trim($post->objective ?? ''),
            trim($post->cta ?? ''),
            trim($post->creative_brief ?? ''),
            trim($post->image_prompt ?? ''),
        ]));
    }
}
