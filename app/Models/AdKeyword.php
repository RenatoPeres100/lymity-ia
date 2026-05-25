<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdKeyword extends Model
{
    protected $fillable = ['ad_campaign_id', 'ad_group_id', 'keyword', 'match_type', 'status'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AdGroup::class, 'ad_group_id');
    }

    public function getMatchTypeLabelAttribute(): string
    {
        return match ($this->match_type) {
            'broad'    => 'Ampla',
            'phrase'   => 'Frase',
            'exact'    => 'Exata',
            'negative' => 'Negativa',
            default    => $this->match_type,
        };
    }
}
