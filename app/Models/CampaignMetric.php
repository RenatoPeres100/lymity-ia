<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMetric extends Model
{
    protected $fillable = [
        'ad_campaign_id', 'date', 'impressions', 'clicks', 'cost',
        'conversions', 'leads', 'revenue', 'ctr', 'cpc', 'cpa', 'roas',
    ];

    protected $casts = [
        'date'        => 'date',
        'cost'        => 'decimal:2',
        'conversions' => 'decimal:2',
        'leads'       => 'decimal:2',
        'revenue'     => 'decimal:2',
        'ctr'         => 'decimal:4',
        'cpc'         => 'decimal:2',
        'cpa'         => 'decimal:2',
        'roas'        => 'decimal:4',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
