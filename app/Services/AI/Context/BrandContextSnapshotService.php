<?php

namespace App\Services\AI\Context;

use App\Models\AgencyBrandContext;
use App\Services\Logs\ActivityLogService;

class BrandContextSnapshotService
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function getActiveBrandContextForScope(?int $companyId, ?int $clientId): ?AgencyBrandContext
    {
        $query = AgencyBrandContext::where('active', true);

        // 1. Client-specific context (most specific)
        if ($clientId) {
            $ctx = (clone $query)->where('client_id', $clientId)->first();
            if ($ctx) return $ctx;
        }

        // 2. Company-specific context
        if ($companyId) {
            $ctx = (clone $query)->where('company_id', $companyId)->whereNull('client_id')->first();
            if ($ctx) return $ctx;
        }

        // 3. Global context (no company/client)
        $ctx = (clone $query)->whereNull('company_id')->whereNull('client_id')->first();
        if ($ctx) return $ctx;

        // 4. Any active context (last resort fallback — takes whatever exists)
        return (clone $query)->first();
    }

    public function generateContentHash(AgencyBrandContext $ctx): string
    {
        $data = implode('|', array_filter([
            $ctx->brand_name, $ctx->positioning, $ctx->tone_of_voice,
            $ctx->target_audience, $ctx->main_services, $ctx->forbidden_terms,
            $ctx->preferred_terms, $ctx->cta_examples, $ctx->cta_default,
            $ctx->content_guidelines, $ctx->seo_rules, $ctx->visual_guidelines,
        ]));
        return hash('sha256', $data);
    }

    public function needsCompactRefresh(AgencyBrandContext $ctx): bool
    {
        if (!$ctx->compact_context) return true;
        if (!$ctx->content_hash) return true;
        $currentHash = $this->generateContentHash($ctx);
        return $ctx->content_hash !== $currentHash;
    }

    public function buildCompactContext(AgencyBrandContext $ctx): string
    {
        $parts = [];
        if ($ctx->brand_name)       $parts[] = "Marca: {$ctx->brand_name}";
        if ($ctx->positioning)      $parts[] = "Posicionamento: {$ctx->positioning}";
        if ($ctx->tone_of_voice)    $parts[] = "Tom: {$ctx->tone_of_voice}";
        if ($ctx->target_audience)  $parts[] = "Público: {$ctx->target_audience}";
        if ($ctx->main_services)    $parts[] = "Serviços: {$ctx->main_services}";

        $cta = $ctx->cta_default ?: $ctx->cta_examples;
        if ($cta) $parts[] = "CTA: {$cta}";

        if ($ctx->content_guidelines) $parts[] = "Regras de conteúdo: {$ctx->content_guidelines}";
        if ($ctx->seo_rules)          $parts[] = "Regras SEO: {$ctx->seo_rules}";
        if ($ctx->visual_guidelines)  $parts[] = "Visual: {$ctx->visual_guidelines}";
        if ($ctx->forbidden_terms)    $parts[] = "Evitar: {$ctx->forbidden_terms}";
        if ($ctx->preferred_terms)    $parts[] = "Preferir: {$ctx->preferred_terms}";

        return implode('. ', array_map(fn($p) => rtrim($p, '.'), $parts)) . '.';
    }

    public function refreshCompactContext(AgencyBrandContext $ctx): AgencyBrandContext
    {
        $hash    = $this->generateContentHash($ctx);
        $compact = $this->buildCompactContext($ctx);

        $ctx->update([
            'content_hash'                   => $hash,
            'compact_context'                => $compact,
            'compact_context_generated_at'   => now(),
        ]);

        return $ctx->fresh();
    }

    public function getCompactContext(AgencyBrandContext $ctx): string
    {
        if ($this->needsCompactRefresh($ctx)) {
            $ctx = $this->refreshCompactContext($ctx);
        }
        return $ctx->compact_context ?: $ctx->toCompactString();
    }

    public function onBrandContextSaved(AgencyBrandContext $ctx): void
    {
        $newVersion = ($ctx->version ?: 0) + 1;
        $ctx->update(['version' => $newVersion]);
        $this->refreshCompactContext($ctx);

        $this->activityLog->info(
            'brand_context.updated',
            "Brand Context v{$newVersion} atualizado para " . ($ctx->brand_name ?: 'Sem nome'),
            ['version' => $newVersion, 'company_id' => $ctx->company_id, 'module' => 'brand_context']
        );
    }
}
