<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SocialContentBrief extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'created_by', 'title', 'description',
        'instructions', 'target_audience', 'offer', 'tone', 'references',
        'objective', 'content_type', 'due_date', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'       => 'Rascunho',
            'in_progress' => 'Em andamento',
            'completed'   => 'Concluído',
            default       => $this->status ?? '—',
        };
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->client?->name ?? $this->company?->name ?? 'Agência';
    }
}
