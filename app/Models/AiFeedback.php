<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedback extends Model
{
    protected $table = 'ai_feedback';

    protected $fillable = [
        'ai_task_id', 'ai_employee_id', 'user_id', 'rating', 'feedback', 'approved',
    ];

    protected $casts = ['approved' => 'boolean'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'ai_task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
