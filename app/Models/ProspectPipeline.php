<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProspectPipeline extends Model
{
    protected $fillable = [
        'company_id', 'name', 'description', 'is_default', 'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProspectStage::class)->orderBy('position');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ProspectLead::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdminGeral()) {
            return $query;
        }
        return $query->where('company_id', $user->company_id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
