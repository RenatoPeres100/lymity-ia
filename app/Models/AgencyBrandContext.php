<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyBrandContext extends Model
{
    protected $fillable = [
        'company_id', 'client_id',
        'brand_name', 'positioning', 'tone_of_voice',
        'target_audience', 'main_services',
        'forbidden_terms', 'preferred_terms',
        'cta_examples', 'cta_default',
        'content_guidelines', 'seo_rules',
        'visual_guidelines', 'active',
        'cached_context_key', 'cached_at',
    ];

    protected $casts = [
        'active'    => 'boolean',
        'cached_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isComplete(): bool
    {
        return !empty($this->brand_name)
            && !empty($this->positioning)
            && !empty($this->tone_of_voice)
            && !empty($this->target_audience);
    }

    // Compact context string for AI prompts
    public function toCompactString(): string
    {
        $parts = [];
        if ($this->brand_name)       $parts[] = "Marca: {$this->brand_name}";
        if ($this->positioning)      $parts[] = "Posicionamento: {$this->positioning}";
        if ($this->target_audience)  $parts[] = "Público: {$this->target_audience}";
        if ($this->tone_of_voice)    $parts[] = "Tom: {$this->tone_of_voice}";
        if ($this->main_services)    $parts[] = "Serviços: {$this->main_services}";
        if ($this->cta_default ?? $this->cta_examples) {
            $parts[] = "CTA: " . ($this->cta_default ?? $this->cta_examples);
        }
        if ($this->content_guidelines) $parts[] = "Regras de conteúdo: {$this->content_guidelines}";
        if ($this->seo_rules)          $parts[] = "Regras SEO: {$this->seo_rules}";
        if ($this->forbidden_terms)    $parts[] = "Termos proibidos: {$this->forbidden_terms}";
        if ($this->preferred_terms)    $parts[] = "Termos preferidos: {$this->preferred_terms}";

        return implode("\n", $parts);
    }
}
