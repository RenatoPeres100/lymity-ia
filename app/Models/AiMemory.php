<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMemory extends Model
{
    protected $fillable = [
        'company_id', 'client_id', 'ai_employee_id', 'agent_task_id',
        'memory_type', 'title', 'content', 'compact_content',
        'weight', 'relevance_score', 'active',
        'source_type', 'source_id', 'expires_at', 'metadata',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'expires_at' => 'datetime',
        'metadata'   => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agentTask(): BelongsTo
    {
        return $this->belongsTo(AgentTask::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->role === 'admin_geral') return $query;
        if ($user->company_id) return $query->where('company_id', $user->company_id);
        return $query->whereRaw('1=0');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getMemoryTypeLabelAttribute(): string
    {
        return match ($this->memory_type) {
            'brand_rule'          => 'Regra de Marca',
            'task_rule'           => 'Regra de Tarefa',
            'approved_pattern'    => 'Padrão Aprovado',
            'rejected_pattern'    => 'Padrão Rejeitado',
            'feedback'            => 'Feedback',
            'content_preference'  => 'Preferência de Conteúdo',
            'visual_preference'   => 'Preferência Visual',
            'performance_insight' => 'Insight de Performance',
            'source_preference'   => 'Fonte Preferida',
            'warning'             => 'Aviso',
            'general'             => 'Geral',
            // Legacy types
            'brand'               => 'Marca',
            'preference'          => 'Preferência',
            'performance'         => 'Desempenho',
            'rule'                => 'Regra',
            'insight'             => 'Insight',
            default               => $this->memory_type ?? '—',
        };
    }

    public function getEffectiveContent(): string
    {
        return $this->compact_content ?: $this->content;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
