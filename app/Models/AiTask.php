<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AiTask extends Model
{
    protected $fillable = [
        'ai_employee_id', 'client_id', 'created_by',
        'title', 'description', 'task_type', 'type',
        'status', 'priority',
        'requires_approval', 'sensitive_action',
        'input_payload', 'output_payload',
        'output', 'error_message', 'metadata',
        'scheduled_at', 'started_at', 'finished_at',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'input_payload'    => 'array',
        'output_payload'   => 'array',
        'metadata'         => 'array',
        'requires_approval'=> 'boolean',
        'sensitive_action' => 'boolean',
        'scheduled_at'     => 'datetime',
        'started_at'       => 'datetime',
        'finished_at'      => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AiTaskLog::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(AiFeedback::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(AiApproval::class);
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function pendingApprovalRequest(): ?ApprovalRequest
    {
        return $this->approvalRequests()->where('status', 'pending')->latest()->first();
    }

    public function getEffectiveTaskType(): string
    {
        return $this->task_type ?? $this->type ?? 'general';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'queued'           => 'Na fila',
            'draft'            => 'Rascunho',
            'running'          => 'Executando',
            'waiting_approval',
            'pending_approval' => 'Aguardando aprovação',
            'approved'         => 'Aprovado',
            'rejected'         => 'Reprovado',
            'completed'        => 'Concluído',
            'failed'           => 'Falhou',
            'canceled'         => 'Cancelado',
            default            => $this->status ?? '—',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'completed'                           => '#166534',
            'approved'                            => '#1e3a5f',
            'running'                             => '#1e3a5f',
            'waiting_approval', 'pending_approval'=> '#78350f',
            'failed', 'rejected', 'canceled'      => '#7f1d1d',
            default                               => '#1e293b',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low'          => 'Baixa',
            'normal','medium' => 'Média',
            'high'         => 'Alta',
            'urgent'       => 'Urgente',
            default        => $this->priority ?? '—',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => '#7f1d1d',
            'high'   => '#78350f',
            'low'    => '#1e293b',
            default  => '#1e3a5f',
        };
    }

    public function canRun(): bool
    {
        return in_array($this->status, ['queued', 'draft']);
    }

    public function canApprove(): bool
    {
        return in_array($this->status, ['waiting_approval', 'pending_approval']);
    }

    public function providerCalls(): HasMany
    {
        return $this->hasMany(AiProviderCall::class);
    }
}
