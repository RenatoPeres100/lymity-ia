<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientBrandProfile extends Model
{
    protected $fillable = [
        'client_id', 'brand_name', 'brand_positioning', 'tone_of_voice',
        'target_audience', 'main_offer', 'objections', 'competitors',
        'visual_style', 'forbidden_terms', 'preferred_terms', 'cta_examples', 'notes',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
