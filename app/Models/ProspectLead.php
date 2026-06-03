<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProspectLead extends Model
{
    protected $fillable = [
        'company_id', 'client_id', 'prospect_pipeline_id', 'prospect_stage_id',
        'owner_id', 'name', 'company_name', 'segment', 'website',
        'instagram', 'linkedin', 'whatsapp', 'email', 'phone',
        'city', 'state', 'source', 'interest_level', 'fit_score',
        'potential_value', 'estimated_budget', 'status', 'next_action',
        'next_follow_up_at', 'last_contacted_at', 'pain_points',
        'opportunity_summary', 'ai_summary', 'lost_reason', 'notes',
    ];

    protected $casts = [
        'next_follow_up_at'  => 'datetime',
        'last_contacted_at'  => 'datetime',
        'potential_value'    => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(ProspectPipeline::class, 'prospect_pipeline_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ProspectStage::class, 'prospect_stage_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProspectActivity::class)->orderByDesc('created_at');
    }

    public function noteEntries(): HasMany
    {
        return $this->hasMany(ProspectNote::class)->orderByDesc('created_at');
    }

    public function insights(): HasMany
    {
        return $this->hasMany(ProspectAiInsight::class)->orderByDesc('created_at');
    }

    public function messageSuggestions(): HasMany
    {
        return $this->hasMany(ProspectMessageSuggestion::class)->orderByDesc('created_at');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdminGeral()) {
            return $query;
        }
        if ($user->isClientUser()) {
            return $query->whereRaw('1=0');
        }
        return $query->where('company_id', $user->company_id);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function isOverdue(): bool
    {
        return $this->next_follow_up_at && $this->next_follow_up_at->isPast() && $this->status === 'open';
    }

    public function getInterestLabelAttribute(): string
    {
        return match($this->interest_level) {
            'cold' => 'Frio',
            'warm' => 'Morno',
            'hot'  => 'Quente',
            default => '—',
        };
    }

    public function getInterestColorAttribute(): string
    {
        return match($this->interest_level) {
            'cold' => 'blue',
            'warm' => 'yellow',
            'hot'  => 'red',
            default => 'slate',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open'     => 'Aberto',
            'won'      => 'Fechado',
            'lost'     => 'Perdido',
            'archived' => 'Arquivado',
            default    => $this->status,
        };
    }
}
