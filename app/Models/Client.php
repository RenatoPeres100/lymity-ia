<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    protected $fillable = [
        'company_id', 'name', 'legal_name', 'tax_id', 'segment',
        'website', 'instagram', 'facebook', 'linkedin', 'tiktok',
        'google_business_profile', 'status', 'brand_voice',
        'target_audience', 'offer_description', 'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
