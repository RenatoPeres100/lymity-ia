<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoKeyword extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'keyword', 'search_intent',
        'priority', 'difficulty', 'volume', 'status', 'notes',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planned'     => 'Planejado',
            'in_progress' => 'Em andamento',
            'used'        => 'Usado',
            'archived'    => 'Arquivado',
            default       => $this->status ?? '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'planned'     => 'blue',
            'in_progress' => 'amber',
            'used'        => 'green',
            'archived'    => 'slate',
            default       => 'slate',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low'    => 'Baixa',
            'medium' => 'Média',
            'high'   => 'Alta',
            default  => $this->priority ?? '—',
        };
    }

    public function getIntentLabelAttribute(): string
    {
        return match ($this->search_intent) {
            'informational'  => 'Informacional',
            'commercial'     => 'Comercial',
            'transactional'  => 'Transacional',
            'navigational'   => 'Navegacional',
            default          => $this->search_intent ?? '—',
        };
    }
}
