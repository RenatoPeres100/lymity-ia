<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdGroup extends Model
{
    protected $fillable = ['ad_campaign_id', 'name', 'targeting_summary', 'status'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(AdKeyword::class);
    }
}
