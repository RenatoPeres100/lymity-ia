<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTaskLog extends Model
{
    protected $fillable = ['ai_task_id', 'level', 'message', 'context'];

    protected $casts = ['context' => 'array'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'ai_task_id');
    }
}
