<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMemory extends Model
{
    protected $fillable = [
        'ai_employee_id', 'client_id', 'memory_type', 'title', 'content', 'weight',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getMemoryTypeLabelAttribute(): string
    {
        return match ($this->memory_type) {
            'brand'       => 'Marca',
            'preference'  => 'Preferência',
            'feedback'    => 'Feedback',
            'performance' => 'Desempenho',
            'rule'        => 'Regra',
            'insight'     => 'Insight',
            default       => $this->memory_type ?? '—',
        };
    }
}
