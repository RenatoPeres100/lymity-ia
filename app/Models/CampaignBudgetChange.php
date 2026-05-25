<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CampaignBudgetChange extends Model
{
    protected $fillable = [
        'ad_campaign_id', 'requested_by', 'ai_employee_id',
        'old_budget', 'new_budget', 'reason', 'status',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'old_budget'  => 'decimal:2',
        'new_budget'  => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_approval' => 'Aguardando Aprovação',
            'approved'         => 'Aprovado',
            'rejected'         => 'Rejeitado',
            'applied'          => 'Aplicado',
            default            => $this->status,
        };
    }
}
