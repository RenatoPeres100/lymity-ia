<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AgentTaskRun extends Model
{
    protected $fillable = [
        'company_id', 'client_id',
        'agent_task_id', 'ai_employee_id',
        'execution_context_id', 'approval_request_id',
        'status',
        'started_at', 'finished_at', 'scheduled_for',
        'error_message',
        'generated_entity_type', 'generated_entity_id',
        'input_tokens', 'output_tokens', 'estimated_cost_usd',
        'metadata',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'finished_at'   => 'datetime',
        'scheduled_for' => 'datetime',
        'metadata'      => 'array',
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

    public function agentTask(): BelongsTo
    {
        return $this->belongsTo(AgentTask::class);
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function executionContext(): BelongsTo
    {
        return $this->belongsTo(AiExecutionContext::class);
    }

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function generatedEntity(): MorphTo
    {
        return $this->morphTo('generated_entity');
    }

    public function contentPackages(): HasMany
    {
        return $this->hasMany(GeneratedContentPackage::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->finished_at) return null;
        $s = $this->started_at->diffInSeconds($this->finished_at);
        return $s < 60 ? "{$s}s" : round($s / 60, 1) . 'min';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'queued'             => 'Na fila',
            'preparing_context' => 'Preparando contexto',
            'researching'       => 'Pesquisando',
            'generating_text'   => 'Gerando texto',
            'generating_images' => 'Gerando imagens',
            'creating_approval' => 'Criando aprovação',
            'waiting_approval'  => 'Aguardando aprovação',
            'approved'          => 'Aprovado',
            'scheduled'         => 'Agendado',
            'published'         => 'Publicado',
            'failed'            => 'Falhou',
            'canceled'          => 'Cancelado',
            default             => $this->status ?? '—',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved', 'published', 'scheduled' => 'green',
            'generating_text', 'generating_images', 'creating_approval', 'preparing_context', 'researching' => 'blue',
            'waiting_approval', 'queued' => 'yellow',
            'failed' => 'red',
            'canceled' => 'gray',
            default => 'gray',
        ];
    }

    public function isRunning(): bool
    {
        return in_array($this->status, [
            'queued', 'preparing_context', 'researching',
            'generating_text', 'generating_images', 'creating_approval',
        ]);
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['waiting_approval', 'approved', 'scheduled', 'published']);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'canceled']);
    }
}
