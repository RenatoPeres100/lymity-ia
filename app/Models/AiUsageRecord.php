<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider', 'model', 'agent_id', 'task_type',
        'input_tokens', 'output_tokens', 'estimated_cost_usd',
        'related_type', 'related_id', 'created_at',
    ];

    protected $casts = [
        'estimated_cost_usd' => 'float',
        'created_at'         => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'agent_id');
    }
}
