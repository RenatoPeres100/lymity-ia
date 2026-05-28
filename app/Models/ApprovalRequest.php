<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'client_id', 'requested_by', 'ai_employee_id',
        'approvable_type', 'approvable_id',
        'title', 'description',
        'approval_type', 'status', 'sensitive_level',
        'payload', 'due_at',
        'approved_by', 'approved_at',
        'rejected_by', 'rejected_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'due_at'      => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class)->orderBy('created_at', 'asc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ApprovalComment::class)->orderBy('created_at', 'asc');
    }

    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeApprovals($user, $query);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCritical(): bool
    {
        return $this->sensitive_level === 'critical';
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast() && $this->status === 'pending';
    }

    public function getApprovalTypeLabelAttribute(): string
    {
        return match ($this->approval_type) {
            'post'            => 'Post',
            'campaign'        => 'Campanha',
            'budget'          => 'Orçamento',
            'blog'            => 'Blog',
            'website_page'    => 'Página Website',
            'proposal'        => 'Proposta',
            'external_action' => 'Ação Externa',
            'ai_task'         => 'Tarefa IA',
            'other'           => 'Outro',
            default           => $this->approval_type ?? '—',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'           => 'Pendente',
            'approved'          => 'Aprovado',
            'rejected'          => 'Rejeitado',
            'changes_requested' => 'Alterações Solicitadas',
            'canceled'          => 'Cancelado',
            default             => $this->status ?? '—',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'approved'          => '#166534',
            'pending'           => '#78350f',
            'changes_requested' => '#1e3a5f',
            'rejected'          => '#7f1d1d',
            'canceled'          => '#334155',
            default             => '#1e293b',
        };
    }

    public function getSensitiveLevelLabelAttribute(): string
    {
        return match ($this->sensitive_level) {
            'low'      => 'Baixo',
            'medium'   => 'Médio',
            'high'     => 'Alto',
            'critical' => 'Crítico',
            default    => $this->sensitive_level ?? '—',
        };
    }

    public function getSensitiveBadgeColorAttribute(): string
    {
        return match ($this->sensitive_level) {
            'critical' => '#7f1d1d',
            'high'     => '#78350f',
            'medium'   => '#1e3a5f',
            'low'      => '#1e293b',
            default    => '#1e293b',
        };
    }
}
