<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoRecommendation extends Model
{
    protected $fillable = [
        'seo_audit_id', 'title', 'description', 'priority', 'status',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(SeoAudit::class, 'seo_audit_id');
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low'      => 'Baixa',
            'medium'   => 'Média',
            'high'     => 'Alta',
            'critical' => 'Crítica',
            default    => $this->priority ?? '—',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low'      => 'slate',
            'medium'   => 'blue',
            'high'     => 'amber',
            'critical' => 'red',
            default    => 'slate',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'Pendente',
            'in_progress' => 'Em andamento',
            'done'        => 'Concluído',
            'ignored'     => 'Ignorado',
            default       => $this->status ?? '—',
        };
    }
}
