<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectNote extends Model
{
    protected $fillable = [
        'prospect_lead_id', 'company_id', 'user_id', 'note', 'visibility',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(ProspectLead::class, 'prospect_lead_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdminGeral()) return $query;
        if ($user->isClientUser()) return $query->whereRaw('1=0');
        return $query->where('company_id', $user->company_id);
    }
}
