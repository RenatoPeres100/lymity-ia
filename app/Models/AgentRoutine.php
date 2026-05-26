<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class AgentRoutine extends Model
{
    protected $fillable = [
        'ai_employee_id',
        'company_id',
        'client_id',
        'routine_type',
        'title',
        'description',
        'frequency',
        'days_of_week',
        'time_of_day',
        'content_quantity',
        'active',
        'requires_approval',
        'next_run_at',
        'last_run_at',
        'metadata',
    ];

    protected $casts = [
        'days_of_week'     => 'array',
        'metadata'         => 'array',
        'active'           => 'boolean',
        'requires_approval'=> 'boolean',
        'next_run_at'      => 'datetime',
        'last_run_at'      => 'datetime',
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

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    public function isDue(): bool
    {
        if (!$this->active) {
            return false;
        }
        if ($this->next_run_at === null) {
            return true;
        }
        return $this->next_run_at->lte(now());
    }

    public function getRoutineTypeLabelAttribute(): string
    {
        return match ($this->routine_type) {
            'social_post_creation' => 'Criação de Post Social',
            'blog_post_creation'   => 'Criação de Post Blog',
            'copy_improvement'     => 'Revisão de Copy',
            'content_review'       => 'Revisão de Conteúdo',
            default                => $this->routine_type,
        };
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily'   => 'Diário',
            'weekly'  => 'Semanal',
            'monthly' => 'Mensal',
            default   => $this->frequency,
        };
    }

    public function getDaysLabelAttribute(): string
    {
        if (empty($this->days_of_week)) {
            return '—';
        }
        $map = [
            'monday'    => 'Seg',
            'tuesday'   => 'Ter',
            'wednesday' => 'Qua',
            'thursday'  => 'Qui',
            'friday'    => 'Sex',
            'saturday'  => 'Sáb',
            'sunday'    => 'Dom',
        ];
        return implode(', ', array_map(fn($d) => $map[$d] ?? $d, $this->days_of_week));
    }
}
