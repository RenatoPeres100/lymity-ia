<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Budget extends Model
{
    protected $fillable = [
        'client_id', 'title', 'description', 'month', 'year',
        'status', 'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function recalculateTotal(): static
    {
        $total = $this->items()->where('status', 'active')->sum('amount');
        $this->update(['total_amount' => $total]);
        return $this->fresh();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando Aprovação',
            'approved'         => 'Aprovado',
            'rejected'         => 'Rejeitado',
            'active'           => 'Ativo',
            'archived'         => 'Arquivado',
            default            => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'gray',
            'pending_approval' => 'orange',
            'approved'         => 'blue',
            'rejected'         => 'red',
            'active'           => 'green',
            'archived'         => 'gray',
            default            => 'gray',
        };
    }

    public function getMonthYearLabelAttribute(): string
    {
        if (!$this->month || !$this->year) {
            return '—';
        }
        $months = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];
        return ($months[$this->month] ?? $this->month) . '/' . $this->year;
    }
}
