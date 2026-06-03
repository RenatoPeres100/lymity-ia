<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectAiInsight extends Model
{
    protected $fillable = [
        'prospect_lead_id', 'company_id', 'ai_employee_id',
        'insight_type', 'title', 'content', 'score', 'payload',
        'model', 'provider', 'input_tokens', 'output_tokens', 'estimated_cost_usd',
    ];

    protected $casts = [
        'payload'           => 'array',
        'estimated_cost_usd' => 'decimal:6',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(ProspectLead::class, 'prospect_lead_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdminGeral()) return $query;
        if ($user->isClientUser()) return $query->whereRaw('1=0');
        return $query->where('company_id', $user->company_id);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->insight_type) {
            'analysis'    => 'Análise',
            'qualification' => 'Qualificação',
            'next_action' => 'Próxima ação',
            'summary'     => 'Resumo',
            'diagnostic'  => 'Diagnóstico',
            default       => $this->insight_type,
        };
    }
}
