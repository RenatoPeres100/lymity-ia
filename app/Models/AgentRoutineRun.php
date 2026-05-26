<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentRoutineRun extends Model
{
    protected $fillable = [
        'agent_routine_id',
        'ai_employee_id',
        'status',
        'started_at',
        'finished_at',
        'output_summary',
        'error_message',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function routine(): BelongsTo
    {
        return $this->belongsTo(AgentRoutine::class, 'agent_routine_id');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }
        $seconds = $this->started_at->diffInSeconds($this->finished_at);
        return $seconds < 60 ? "{$seconds}s" : round($seconds / 60, 1) . 'min';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'queued'    => 'Na fila',
            'running'   => 'Em execução',
            'completed' => 'Concluído',
            'failed'    => 'Falhou',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'queued'    => 'blue',
            'running'   => 'yellow',
            'completed' => 'green',
            'failed'    => 'red',
            default     => 'gray',
        };
    }
}
