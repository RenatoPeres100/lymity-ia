<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Company extends Model
{
    protected $fillable = [
        'name', 'legal_name', 'tax_id', 'email',
        'phone', 'website', 'logo', 'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function socialChannels(): HasMany
    {
        return $this->hasMany(SocialChannel::class);
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    public function socialCalendars(): HasMany
    {
        return $this->hasMany(SocialCalendar::class);
    }

    public function socialContentBriefs(): HasMany
    {
        return $this->hasMany(SocialContentBrief::class);
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

    public function externalFiles(): HasMany
    {
        return $this->hasMany(ExternalFile::class);
    }

    public function storageIntegrations(): HasMany
    {
        return $this->hasMany(StorageIntegration::class);
    }
}
