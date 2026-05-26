<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyBrandContext extends Model
{
    protected $fillable = [
        'company_id',
        'brand_name',
        'positioning',
        'tone_of_voice',
        'target_audience',
        'main_services',
        'forbidden_terms',
        'preferred_terms',
        'cta_examples',
        'content_guidelines',
        'visual_guidelines',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isComplete(): bool
    {
        return !empty($this->brand_name)
            && !empty($this->positioning)
            && !empty($this->tone_of_voice)
            && !empty($this->target_audience);
    }
}
