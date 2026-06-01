<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class AgentRoutine extends Model
{
    protected $fillable = [
        'ai_employee_id', 'company_id', 'client_id',
        'routine_type', 'title', 'description',
        'frequency', 'days_of_week', 'time_of_day', 'timezone',
        'content_quantity', 'quantity_per_run',
        'approval_lead_days', 'generate_ahead_days',
        'publication_time', 'target_days_of_week', 'target_time_of_day',
        'active', 'status', 'requires_approval',
        'next_run_at', 'last_run_at', 'last_error',
        'metadata',
    ];

    protected $casts = [
        'days_of_week'        => 'array',
        'target_days_of_week' => 'array',
        'metadata'            => 'array',
        'active'              => 'boolean',
        'requires_approval'   => 'boolean',
        'next_run_at'         => 'datetime',
        'last_run_at'         => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AgentRoutineRun::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeAgentRoutines($user, $query);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->whereIn('status', ['active', null]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return (bool) $this->active && $this->status !== 'disabled';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused' || !$this->active;
    }

    public function isDue(?Carbon $now = null): bool
    {
        if (!$this->isActive()) return false;
        $now = $now ?? now();
        if ($this->next_run_at === null) return true;
        return $this->next_run_at->lte($now);
    }

    /** The effective quantity per run — prefers quantity_per_run, falls back to content_quantity */
    public function getEffectiveQuantity(): int
    {
        return (int) ($this->quantity_per_run ?: $this->content_quantity ?: 1);
    }

    public function getRoutineTypeLabelAttribute(): string
    {
        return match ($this->routine_type) {
            'social_post_creation', 'generate_social_post', 'social_post_generation' => 'Post Social',
            'blog_post_creation', 'generate_blog_post', 'blog_post_generation'        => 'Post Blog',
            'copy_improvement', 'copy_review', 'review_pending_content'               => 'Revisão de Copy',
            'content_review'                                                           => 'Revisão de Conteúdo',
            default                                                                    => $this->routine_type ?? '—',
        };
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily'   => 'Diário',
            'weekly'  => 'Semanal',
            'monthly' => 'Mensal',
            'manual'  => 'Manual',
            default   => $this->frequency ?? '—',
        };
    }

    public function getDaysLabelAttribute(): string
    {
        $days = $this->days_of_week ?? [];
        if (empty($days)) return '—';
        $map = [
            'monday' => 'Seg', 'tuesday' => 'Ter', 'wednesday' => 'Qua',
            'thursday' => 'Qui', 'friday' => 'Sex', 'saturday' => 'Sáb', 'sunday' => 'Dom',
        ];
        return implode(', ', array_map(fn($d) => $map[$d] ?? $d, $days));
    }

    public function isBlogType(): bool
    {
        return in_array($this->routine_type, [
            'blog_post_creation', 'generate_blog_post', 'blog_post_generation',
        ]);
    }

    public function isSocialType(): bool
    {
        return in_array($this->routine_type, [
            'social_post_creation', 'generate_social_post', 'social_post_generation',
        ]);
    }

    public function isCopyType(): bool
    {
        return in_array($this->routine_type, [
            'copy_improvement', 'copy_review', 'review_pending_content', 'content_review',
        ]);
    }
}
