<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdCreative extends Model
{
    protected $fillable = [
        'ad_campaign_id', 'ad_group_id', 'title', 'headline', 'description',
        'primary_text', 'cta', 'creative_brief', 'destination_url', 'status',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AdGroup::class, 'ad_group_id');
    }
}
