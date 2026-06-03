<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectMessageSuggestion extends Model
{
    protected $fillable = [
        'prospect_lead_id', 'company_id', 'ai_employee_id',
        'channel', 'objective', 'message', 'status',
        'approved_by_user_id', 'approved_at', 'used_at', 'metadata',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'approved_at' => 'datetime',
        'used_at'     => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdminGeral()) return $query;
        if ($user->isClientUser()) return $query->whereRaw('1=0');
        return $query->where('company_id', $user->company_id);
    }

    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            'whatsapp'    => 'WhatsApp',
            'instagram'   => 'Instagram',
            'email'       => 'E-mail',
            'linkedin'    => 'LinkedIn',
            'call_script' => 'Script de ligação',
            default       => $this->channel,
        };
    }

    public function getObjectiveLabelAttribute(): string
    {
        return match($this->objective) {
            'first_contact'      => 'Primeiro contato',
            'follow_up'          => 'Follow-up',
            'reengagement'       => 'Reengajamento',
            'meeting_invite'     => 'Convite para reunião',
            'proposal_follow_up' => 'Follow-up de proposta',
            default              => $this->objective,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Rascunho',
            'approved' => 'Aprovada',
            'rejected' => 'Rejeitada',
            'used'     => 'Usada',
            'archived' => 'Arquivada',
            default    => $this->status,
        };
    }
}
