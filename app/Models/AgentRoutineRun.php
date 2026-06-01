<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentRoutineRun extends Model
{
    protected $fillable = [
        'agent_routine_id', 'run_key', 'routine_type',
        'ai_employee_id', 'company_id', 'client_id',
        'scheduled_for', 'status',
        'started_at', 'finished_at',
        'output_summary', 'error_message',
        'items_requested', 'items_created',
        'tasks_created', 'approvals_created',
        'metadata',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'finished_at'   => 'datetime',
        'scheduled_for' => 'datetime',
        'metadata'      => 'array',
    ];

    public function routine(): BelongsTo
    {
        return $this->belongsTo(AgentRoutine::class, 'agent_routine_id');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->finished_at) return null;
        $s = $this->started_at->diffInSeconds($this->finished_at);
        return $s < 60 ? "{$s}s" : round($s / 60, 1) . 'min';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendente',
            'running'   => 'Em execução',
            'completed' => 'Concluído',
            'failed'    => 'Falhou',
            'skipped'   => 'Ignorado (duplicado)',
            'canceled'  => 'Cancelado',
            'queued'    => 'Na fila',
            default     => $this->status ?? '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => '#166534',
            'running'   => '#1d4ed8',
            'pending', 'queued' => '#78350f',
            'failed'    => '#7f1d1d',
            'skipped'   => '#475569',
            default     => '#334155',
        };
    }
}
