<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoAudit extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'website_url', 'title',
        'summary', 'score', 'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(SeoRecommendation::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Rascunho',
            'completed' => 'Concluída',
            'archived'  => 'Arquivada',
            default     => $this->status ?? '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'slate',
            'completed' => 'green',
            'archived'  => 'slate',
            default     => 'slate',
        };
    }

    public function getScoreLabelAttribute(): string
    {
        if ($this->score === null) {
            return '—';
        }

        return match (true) {
            $this->score >= 80 => 'Bom',
            $this->score >= 60 => 'Regular',
            $this->score >= 40 => 'Baixo',
            default            => 'Crítico',
        };
    }

    public function getScoreColorAttribute(): string
    {
        if ($this->score === null) {
            return 'slate';
        }

        return match (true) {
            $this->score >= 80 => 'green',
            $this->score >= 60 => 'amber',
            $this->score >= 40 => 'orange',
            default            => 'red',
        };
    }
}
