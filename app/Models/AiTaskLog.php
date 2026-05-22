<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTaskLog extends Model
{
    protected $fillable = ['ai_task_id', 'ai_employee_id', 'client_id', 'level', 'message', 'context'];

    protected $casts = ['context' => 'array'];

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
