<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiApproval extends Model
{
    protected $fillable = [
        'ai_task_id', 'requested_by', 'approved_by',
        'status', 'approval_type', 'reason', 'decided_at',
    ];

    protected $casts = ['decided_at' => 'datetime'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'ai_task_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
