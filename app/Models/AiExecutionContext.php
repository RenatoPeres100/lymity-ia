<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiExecutionContext extends Model
{
    protected $fillable = [
        'company_id', 'client_id', 'ai_employee_id', 'agent_task_id', 'brand_context_id',
        'brand_context_version', 'brand_context_hash',
        'task_version', 'task_context_hash',
        'compact_brand_context', 'compact_task_context',
        'selected_memories', 'external_research_snapshot',
        'prompt_hash', 'prompt_preview',
        'model', 'provider',
        'input_tokens_estimated', 'output_tokens_estimated', 'estimated_cost_usd',
        'status', 'error_message',
    ];

    protected $casts = [
        'selected_memories'           => 'array',
        'external_research_snapshot'  => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function agentTask(): BelongsTo
    {
        return $this->belongsTo(AgentTask::class);
    }

    public function brandContext(): BelongsTo
    {
        return $this->belongsTo(AgencyBrandContext::class, 'brand_context_id');
    }
}
