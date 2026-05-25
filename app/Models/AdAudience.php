<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdAudience extends Model
{
    protected $fillable = ['ad_campaign_id', 'name', 'description', 'targeting_data', 'status'];

    protected $casts = ['targeting_data' => 'array'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
