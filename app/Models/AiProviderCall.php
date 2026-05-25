<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProviderCall extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ai_employee_id', 'ai_task_id', 'client_id', 'user_id',
        'provider', 'model',
        'prompt_hash', 'prompt_preview', 'response_summary',
        'status', 'input_tokens', 'output_tokens', 'total_tokens',
        'estimated_cost', 'error_message', 'metadata',
    ];

    protected $casts = [
        'metadata'       => 'array',
        'estimated_cost' => 'float',
        'created_at'     => 'datetime',
    ];

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function aiTask(): BelongsTo
    {
        return $this->belongsTo(AiTask::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'success' ? 'Sucesso' : 'Falha';
    }

    public function getFormattedCostAttribute(): string
    {
        if ($this->estimated_cost === null) return '—';
        return '$' . number_format($this->estimated_cost, 6);
    }
}
