<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AdCampaign extends Model
{
    protected $fillable = [
        'ad_account_id', 'client_id', 'company_id', 'created_by', 'ai_employee_id',
        'platform', 'name', 'objective', 'status', 'daily_budget', 'total_budget',
        'start_date', 'end_date', 'strategy_summary', 'requires_approval',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'approved_at'      => 'datetime',
        'requires_approval' => 'boolean',
        'daily_budget'     => 'decimal:2',
        'total_budget'     => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class, 'ad_account_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(AdGroup::class);
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(AdKeyword::class);
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(AdAudience::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(CampaignMetric::class);
    }

    public function budgetChanges(): HasMany
    {
        return $this->hasMany(CampaignBudgetChange::class);
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function getObjectiveLabelAttribute(): string
    {
        return match ($this->objective) {
            'leads'       => 'Captação de Leads',
            'sales'       => 'Vendas',
            'traffic'     => 'Tráfego',
            'awareness'   => 'Alcance/Awareness',
            'engagement'  => 'Engajamento',
            'app'         => 'Instalações de App',
            'remarketing' => 'Remarketing',
            default       => $this->objective,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando Aprovação',
            'approved'         => 'Aprovada',
            'scheduled'        => 'Agendada',
            'active'           => 'Ativa',
            'paused'           => 'Pausada',
            'completed'        => 'Concluída',
            'rejected'         => 'Rejeitada',
            'archived'         => 'Arquivada',
            default            => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'secondary',
            'pending_approval' => 'warning',
            'approved'         => 'success',
            'scheduled'        => 'info',
            'active'           => 'success',
            'paused'           => 'warning',
            'completed'        => 'primary',
            'rejected'         => 'danger',
            'archived'         => 'secondary',
            default            => 'secondary',
        };
    }

    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'google_ads'   => 'Google Ads',
            'meta_ads'     => 'Meta Ads',
            'linkedin_ads' => 'LinkedIn Ads',
            'tiktok_ads'   => 'TikTok Ads',
            default        => $this->platform,
        };
    }
}
