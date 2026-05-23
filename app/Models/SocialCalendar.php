<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialCalendar extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'month', 'year',
        'strategy_summary', 'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function posts()
    {
        $query = SocialPost::where('month', $this->month)->where('year', $this->year);

        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        } elseif ($this->company_id) {
            $query->where('company_id', $this->company_id);
        }

        return $query;
    }

    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->where('month', $month)->where('year', $year);
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

    public function getOwnerNameAttribute(): string
    {
        return $this->client?->name ?? $this->company?->name ?? 'Agência';
    }
}
