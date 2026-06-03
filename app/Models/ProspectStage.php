<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProspectStage extends Model
{
    protected $fillable = [
        'prospect_pipeline_id', 'name', 'key', 'description',
        'position', 'color', 'is_won', 'is_lost', 'status',
    ];

    protected $casts = [
        'is_won'  => 'boolean',
        'is_lost' => 'boolean',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(ProspectPipeline::class, 'prospect_pipeline_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ProspectLead::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->whereHas('pipeline', function ($q) use ($user) {
            $q->visibleTo($user);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
