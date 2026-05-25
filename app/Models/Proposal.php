<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Proposal extends Model
{
    protected $fillable = [
        'client_id', 'created_by', 'ai_employee_id', 'title', 'description',
        'status', 'total_amount', 'valid_until', 'terms', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'total_amount' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function recalculateTotal(): static
    {
        $total = $this->items()->sum('total_price');
        $this->update(['total_amount' => $total]);
        return $this->fresh();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando Aprovação',
            'approved'         => 'Aprovado',
            'rejected'         => 'Rejeitado',
            'sent'             => 'Enviado ao Cliente',
            'accepted'         => 'Aceito',
            'archived'         => 'Arquivado',
            default            => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'gray',
            'pending_approval' => 'orange',
            'approved'         => 'blue',
            'rejected'         => 'red',
            'sent'             => 'purple',
            'accepted'         => 'green',
            'archived'         => 'gray',
            default            => 'gray',
        };
    }
}
