<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdAccount extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'platform', 'account_name',
        'external_account_id', 'status', 'access_token', 'refresh_token',
        'token_expires_at', 'metadata',
    ];

    protected $casts = [
        'metadata'         => 'array',
        'token_expires_at' => 'datetime',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(AdCampaign::class);
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
