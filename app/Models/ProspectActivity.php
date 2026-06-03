<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectActivity extends Model
{
    protected $fillable = [
        'prospect_lead_id', 'company_id', 'user_id',
        'activity_type', 'title', 'description',
        'status', 'due_at', 'completed_at', 'outcome',
    ];

    protected $casts = [
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->activity_type) {
            'call'       => 'Ligação',
            'whatsapp'   => 'WhatsApp',
            'email'      => 'E-mail',
            'instagram'  => 'Instagram',
            'linkedin'   => 'LinkedIn',
            'meeting'    => 'Reunião',
            'note'       => 'Nota',
            'follow_up'  => 'Follow-up',
            'task'       => 'Tarefa',
            'other'      => 'Outro',
            default      => $this->activity_type,
        };
    }
}
