<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiWorkSchedule extends Model
{
    protected $fillable = [
        'ai_employee_id', 'client_id', 'frequency',
        'work_minutes_per_run', 'max_tasks_per_run',
        'active', 'days_of_week', 'time_of_day',
        'last_run_at', 'next_run_at',
    ];

    protected $casts = [
        'active'       => 'boolean',
        'days_of_week' => 'array',
        'last_run_at'  => 'datetime',
        'next_run_at'  => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'manual'  => 'Manual',
            'hourly'  => 'De hora em hora',
            'daily'   => 'Diário',
            'weekly'  => 'Semanal',
            'monthly' => 'Mensal',
            default   => $this->frequency ?? '—',
        };
    }

    public function isDue(): bool
    {
        if (!$this->active) return false;
        if ($this->frequency === 'manual') return false;
        if (!$this->next_run_at) return true;
        return $this->next_run_at->isPast();
    }
}
