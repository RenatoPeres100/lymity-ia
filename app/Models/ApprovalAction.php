<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'approval_request_id', 'user_id', 'action', 'notes', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created'           => 'Criado',
            'approved'          => 'Aprovado',
            'rejected'          => 'Rejeitado',
            'requested_changes' => 'Alterações Solicitadas',
            'commented'         => 'Comentado',
            'canceled'          => 'Cancelado',
            default             => $this->action ?? '—',
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'approved'          => '#166534',
            'rejected'          => '#7f1d1d',
            'requested_changes' => '#78350f',
            'canceled'          => '#334155',
            'commented'         => '#1e3a5f',
            'created'           => '#1e293b',
            default             => '#1e293b',
        };
    }
}
