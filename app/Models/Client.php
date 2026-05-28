<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    protected $fillable = [
        'company_id', 'name', 'legal_name', 'tax_id', 'segment',
        'website', 'instagram', 'facebook', 'linkedin', 'tiktok',
        'youtube', 'whatsapp', 'email', 'phone',
        'google_business_profile', 'status',
        'brand_voice', 'target_audience', 'offer_description', 'notes',
    ];

    // ── Relations ────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function activeUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('status', 'active');
    }

    public function brandProfile(): HasOne
    {
        return $this->hasOne(ClientBrandProfile::class);
    }

    public function websites(): HasMany
    {
        return $this->hasMany(ClientWebsite::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ClientAsset::class);
    }

    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(ClientKnowledgeBase::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class)->where('type', 'client');
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    public function socialChannels(): HasMany
    {
        return $this->hasMany(SocialChannel::class);
    }

    public function socialCalendars(): HasMany
    {
        return $this->hasMany(SocialCalendar::class);
    }

    public function socialContentBriefs(): HasMany
    {
        return $this->hasMany(SocialContentBrief::class);
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function seoKeywords(): HasMany
    {
        return $this->hasMany(SeoKeyword::class);
    }

    public function seoClusters(): HasMany
    {
        return $this->hasMany(SeoCluster::class);
    }

    public function seoContentPlans(): HasMany
    {
        return $this->hasMany(SeoContentPlan::class);
    }

    public function seoAudits(): HasMany
    {
        return $this->hasMany(SeoAudit::class);
    }

    public function adAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class);
    }

    public function adCampaigns(): HasMany
    {
        return $this->hasMany(AdCampaign::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ClientContract::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(ClientFolder::class);
    }

    public function externalFiles(): HasMany
    {
        return $this->hasMany(ExternalFile::class);
    }

    public function storageIntegrations(): HasMany
    {
        return $this->hasMany(StorageIntegration::class);
    }

    public function aiProviderCalls(): HasMany
    {
        return $this->hasMany(AiProviderCall::class);
    }

    // ── Status helpers ───────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Ativo',
            'inactive' => 'Inativo',
            'archived' => 'Arquivado',
            default    => $this->status ?? '—',
        };
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeVisibleTo($query, User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeClients($user, $query);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('legal_name', 'like', "%{$term}%")
              ->orWhere('segment', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('instagram', 'like', "%{$term}%");
        });
    }

    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdminGeral() || in_array($user->role, ['agencia_admin', 'agencia_operador'])) {
            return $query;
        }

        if ($user->client_id) {
            return $query->where('id', $user->client_id);
        }

        return $query->whereRaw('0 = 1');
    }
}
