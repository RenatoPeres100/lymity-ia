<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoContentPlan extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'title', 'description',
        'month', 'year', 'status',
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
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando aprovação',
            'approved'         => 'Aprovado',
            'active'           => 'Ativo',
            'archived'         => 'Arquivado',
            default            => $this->status ?? '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'slate',
            'pending_approval' => 'amber',
            'approved'         => 'blue',
            'active'           => 'green',
            'archived'         => 'slate',
            default            => 'slate',
        };
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
            4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
            10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $months[$this->month] ?? '—';
    }
}
