<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'company_id', 'client_id', 'ai_employee_id', 'agent_task_id', 'agent_task_run_id',
        'provider', 'model', 'agent_id', 'task_type',
        'input_tokens', 'output_tokens', 'estimated_cost_usd',
        'status', 'metadata',
        'related_type', 'related_id', 'created_at',
    ];

    protected $casts = [
        'estimated_cost_usd' => 'float',
        'created_at'         => 'datetime',
        'metadata'           => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'agent_id');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function agentTask(): BelongsTo
    {
        return $this->belongsTo(AgentTask::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->role === 'admin_geral') return $query;
        if ($user->company_id) return $query->where('company_id', $user->company_id);
        return $query->whereRaw('1=0');
    }
}
