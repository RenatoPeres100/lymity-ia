<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoCluster extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'name', 'description',
        'main_keyword', 'status',
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
            'active'   => 'Ativo',
            'paused'   => 'Pausado',
            'archived' => 'Arquivado',
            default    => $this->status ?? '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'green',
            'paused'   => 'amber',
            'archived' => 'slate',
            default    => 'slate',
        };
    }
}
