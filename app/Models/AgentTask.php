<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class AgentTask extends Model
{
    protected $fillable = [
        'company_id', 'client_id', 'ai_employee_id', 'created_by_user_id',
        'title', 'task_type', 'description', 'operational_instructions', 'expected_output',
        'content_channel', 'content_format',
        'requires_external_research', 'external_research_topics', 'external_research_recency_days',
        'require_sources', 'if_research_unavailable',
        'requires_image', 'image_type', 'carousel_slides_count', 'image_instructions',
        'requires_approval', 'auto_schedule_after_approval', 'auto_publish_after_approval',
        'frequency', 'days_of_week', 'day_of_month', 'time_of_day', 'timezone',
        'content_quantity_per_run', 'max_runs_per_day',
        'status',
        'last_run_at', 'next_run_at', 'last_success_at', 'last_failure_at', 'failure_count',
        'task_context_hash', 'compact_task_context', 'compact_task_context_generated_at', 'version',
        'metadata',
    ];

    protected $casts = [
        'expected_output'                     => 'array',
        'days_of_week'                        => 'array',
        'metadata'                            => 'array',
        'requires_external_research'          => 'boolean',
        'require_sources'                     => 'boolean',
        'requires_image'                      => 'boolean',
        'requires_approval'                   => 'boolean',
        'auto_schedule_after_approval'        => 'boolean',
        'auto_publish_after_approval'         => 'boolean',
        'last_run_at'                         => 'datetime',
        'next_run_at'                         => 'datetime',
        'last_success_at'                     => 'datetime',
        'last_failure_at'                     => 'datetime',
        'compact_task_context_generated_at'   => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AgentTaskRun::class);
    }

    public function memories(): HasMany
    {
        return $this->hasMany(AiMemory::class);
    }

    public function generatedPackages(): HasMany
    {
        return $this->hasMany(GeneratedContentPackage::class);
    }

    public function executionContexts(): HasMany
    {
        return $this->hasMany(AiExecutionContext::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->role === 'admin_geral') {
            return $query;
        }
        if ($user->company_id) {
            return $query->where('company_id', $user->company_id);
        }
        return $query->whereRaw('1=0');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDue($query, ?Carbon $now = null)
    {
        $now = $now ?? now();
        return $query->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function requiresApproval(): bool
    {
        return (bool) $this->requires_approval;
    }

    public function requiresImage(): bool
    {
        return (bool) $this->requires_image;
    }

    public function canRunNow(): bool
    {
        if (!$this->isActive()) return false;
        if ($this->next_run_at && $this->next_run_at->isFuture()) return false;
        return true;
    }

    public function shouldRunAt(Carbon $date): bool
    {
        if ($this->frequency === 'manual') return false;
        if ($this->frequency === 'daily') return true;
        if ($this->frequency === 'weekly') {
            $days = $this->days_of_week ?? [];
            $dayName = strtolower($date->englishDayOfWeek);
            return in_array($dayName, $days);
        }
        if ($this->frequency === 'monthly') {
            return $this->day_of_month && $date->day === (int) $this->day_of_month;
        }
        return false;
    }

    public function getTodayRunCount(): int
    {
        return $this->runs()
            ->whereDate('created_at', today())
            ->whereNotIn('status', ['failed', 'canceled'])
            ->count();
    }

    public function hasReachedDailyLimit(): bool
    {
        return $this->getTodayRunCount() >= ($this->max_runs_per_day ?: 1);
    }

    public function getTaskTypeLabelAttribute(): string
    {
        return match ($this->task_type) {
            'blog_post_recurring'            => 'Blog Post Recorrente',
            'instagram_post_recurring'       => 'Post Instagram Recorrente',
            'instagram_carousel_recurring'   => 'Carrossel Instagram Recorrente',
            'copy_review_recurring'          => 'Revisão de Copy Recorrente',
            'prospecting_assistant_recurring'=> 'Assistente de Prospecção',
            default                          => $this->task_type ?? 'Custom',
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Ativa',
            'paused'   => 'Pausada',
            'disabled' => 'Desativada',
            'archived' => 'Arquivada',
            default    => $this->status ?? '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'green',
            'paused'   => 'yellow',
            'disabled' => 'red',
            'archived' => 'gray',
            default    => 'gray',
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
        return $this->task_type === 'blog_post_recurring';
    }

    public function isInstagramType(): bool
    {
        return in_array($this->task_type, ['instagram_post_recurring', 'instagram_carousel_recurring']);
    }

    public function isCarouselType(): bool
    {
        return $this->task_type === 'instagram_carousel_recurring';
    }
}
