<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id', 'category', 'name', 'description', 'amount', 'status',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'media'      => 'Mídia',
            'production' => 'Produção',
            'service'    => 'Serviço',
            'tool'       => 'Ferramenta',
            'other'      => 'Outro',
            default      => $this->category,
        };
    }
}
