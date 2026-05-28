<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTaskLog extends Model
{
    protected $fillable = [
        'ai_task_id', 'ai_employee_id', 'client_id', 'level', 'message', 'context',
        'task_type', 'status', 'provider', 'model',
        'input_tokens', 'output_tokens', 'estimated_cost_usd',
        'started_at', 'finished_at', 'payload_snapshot', 'error_message',
        'related_type', 'related_id',
    ];

    protected $casts = [
        'context'          => 'array',
        'payload_snapshot' => 'array',
        'started_at'       => 'datetime',
        'finished_at'      => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'ai_task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getLevelBadgeAttribute(): string
    {
        return match ($this->level) {
            'success' => 'badge-green',
            'warning' => 'badge-yellow',
            'error'   => 'badge-danger',
            default   => 'badge-blue',
        };
    }
}
