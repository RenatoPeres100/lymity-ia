<?php

namespace App\Services\AiImages;

use App\Models\AiImageGeneration;
use App\Models\Client;
use App\Models\Company;

class AiImagePromptBuilderService
{
    public function buildPrompt(AiImageGeneration $generation): string
    {
        return $generation->isCarousel()
            ? implode("\n\n---\n\n", $this->buildCarouselPrompts($generation))
            : $this->buildSinglePrompt($generation);
    }

    public function buildSinglePrompt(AiImageGeneration $generation): string
    {
        $base = $this->basePromptForChannel($generation->channel);

        $parts = [$base];

        if ($generation->subject) {
            $parts[] = "Tema central: {$generation->subject}.";
        }
        if ($generation->title) {
            $parts[] = "Título do conteúdo: {$generation->title}.";
        }
        if ($generation->prompt_context) {
            $parts[] = "Contexto adicional: {$generation->prompt_context}.";
        }
        if ($generation->target_audience) {
            $parts[] = "Público-alvo: {$generation->target_audience}.";
        }
        if ($generation->tone) {
            $parts[] = "Tom visual/emocional: {$generation->tone}.";
        }
        if ($generation->visual_style) {
            $parts[] = "Estilo visual: {$generation->visual_style}.";
        }
        if ($generation->brand_context) {
            $parts[] = "Contexto da marca: {$generation->brand_context}.";
        }
        if ($generation->aspect_ratio) {
            $parts[] = "Proporção da imagem: {$generation->aspect_ratio}.";
        }

        $overlayInstruction = $this->buildOverlayInstruction(
            $generation->overlay_mode,
            $generation->overlay_text
        );
        if ($overlayInstruction) {
            $parts[] = $overlayInstruction;
        }

        return implode(' ', $parts);
    }

    public function buildCarouselPrompts(AiImageGeneration $generation): array
    {
        $slides = max(1, (int) $generation->slides_count);
        $prompts = [];

        $slideStructure = $this->carouselSlideStructure($slides);

        foreach ($slideStructure as $i => $slideDesc) {
            $slideNum = $i + 1;
            $base = $this->basePromptForChannel($generation->channel);

            $parts = [$base];
            $parts[] = "Este é o SLIDE {$slideNum} de {$slides} de um carrossel.";
            $parts[] = "Função deste slide: {$slideDesc}.";

            if ($generation->title) {
                $parts[] = "Tema geral do carrossel: {$generation->title}.";
            }
            if ($generation->subject) {
                $parts[] = "Assunto: {$generation->subject}.";
            }
            if ($generation->prompt_context) {
                $parts[] = "Contexto: {$generation->prompt_context}.";
            }
            if ($generation->tone) {
                $parts[] = "Tom: {$generation->tone}.";
            }
            if ($generation->visual_style) {
                $parts[] = "Estilo visual: {$generation->visual_style}.";
            }
            if ($generation->brand_context) {
                $parts[] = "Marca: {$generation->brand_context}.";
            }
            $parts[] = "Cada slide deve ter identidade visual coerente com os demais do carrossel, mas composição única.";

            $overlayInstruction = $this->buildOverlayInstruction(
                $generation->overlay_mode,
                $generation->overlay_text
            );
            if ($overlayInstruction) {
                $parts[] = $overlayInstruction;
            }

            $prompts[] = implode(' ', $parts);
        }

        return $prompts;
    }

    public function buildBrandContext(?Company $company, ?Client $client): string
    {
        $parts = [];

        if ($client && $client->brandProfile) {
            $bp = $client->brandProfile;
            if ($bp->brand_name) {
                $parts[] = "Marca: {$bp->brand_name}.";
            }
            if ($bp->brand_positioning) {
                $parts[] = "Posicionamento: {$bp->brand_positioning}.";
            }
            if ($bp->tone_of_voice) {
                $parts[] = "Tom de voz: {$bp->tone_of_voice}.";
            }
            if ($bp->visual_style) {
                $parts[] = "Estilo visual: {$bp->visual_style}.";
            }
            if ($bp->target_audience) {
                $parts[] = "Público: {$bp->target_audience}.";
            }
            return implode(' ', $parts);
        }

        if ($company) {
            $parts[] = "Empresa: {$company->name}.";
            $parts[] = "Segmento: tecnologia aplicada a crescimento, IA, automação, marketing, SEO, conteúdo e operação digital.";
            return implode(' ', $parts);
        }

        return 'Empresa de tecnologia aplicada a crescimento, IA, automação, marketing e operação digital.';
    }

    public function buildOverlayInstruction(?string $overlayMode, ?string $overlayText): string
    {
        if (!$overlayMode || $overlayMode === 'none') {
            return '';
        }

        $textHint = $overlayText ? " O texto que será sobreposto é: \"{$overlayText}\"." : '';

        return match($overlayMode) {
            'safe_top'     => "Deixe a área superior da imagem limpa e com baixa complexidade visual para receber texto sobreposto.{$textHint}",
            'safe_bottom'  => "Deixe a área inferior da imagem limpa e com baixa complexidade visual para receber texto sobreposto.{$textHint}",
            'safe_left'    => "Deixe a área esquerda da imagem limpa e com baixa complexidade visual para receber texto sobreposto.{$textHint}",
            'safe_right'   => "Deixe a área direita da imagem limpa e com baixa complexidade visual para receber texto sobreposto.{$textHint}",
            'center_free'  => "Deixe o centro da imagem com área clara e de baixa complexidade visual para receber texto sobreposto.{$textHint}",
            default        => '',
        };
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function basePromptForChannel(string $channel): string
    {
        return match($channel) {
            'social' => 'Crie uma imagem profissional para Instagram, visual premium, moderno, tecnológico e estratégico, coerente com o contexto fornecido. A imagem deve representar a ideia central do conteúdo, sem parecer genérica ou placeholder. Usar linguagem visual relacionada a IA, automação, dados, crescimento, estratégia e operação digital somente quando fizer sentido. Não usar logotipos de terceiros. Não usar rostos reais identificáveis. Evitar texto pequeno ou ilegível.',
            'blog'   => 'Crie uma imagem de capa para artigo de blog, visual premium, tecnológico e editorial, coerente com o tema do artigo. A imagem deve funcionar como featured image, com composição limpa, profissional e adequada para uma empresa de tecnologia aplicada a crescimento, IA, automação e estratégia digital.',
            default  => 'Crie uma imagem de alta qualidade, visual premium, profissional, tecnológico e moderno. Composição limpa, coerente com o contexto fornecido. Sem logotipos de terceiros ou rostos identificáveis.',
        };
    }

    private function carouselSlideStructure(int $slides): array
    {
        if ($slides <= 1) {
            return ['imagem principal'];
        }
        if ($slides === 2) {
            return ['gancho/abertura — captura atenção imediatamente', 'conclusão/CTA — convida à ação'];
        }
        if ($slides === 3) {
            return ['gancho/abertura', 'explicação/desenvolvimento', 'conclusão/CTA'];
        }
        if ($slides === 4) {
            return ['gancho/abertura', 'problema ou contexto', 'solução ou benefício', 'CTA/conclusão'];
        }
        // 5 or more
        $structure = [
            'gancho/abertura — imagem impactante que captura atenção imediatamente',
            'problema — apresenta o desafio ou dor do público',
            'desenvolvimento/explicação — aprofunda o tema',
            'solução/benefício — apresenta a solução ou vantagem',
            'CTA/conclusão — encerra com call-to-action e engajamento',
        ];
        // If more than 5, insert middle slides
        if ($slides > 5) {
            for ($i = 5; $i < $slides; $i++) {
                array_splice($structure, 3, 0, ['desenvolvimento adicional — complementa o slide anterior']);
            }
        }
        return array_slice($structure, 0, $slides);
    }
}
