<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContract extends Model
{
    protected $fillable = [
        'client_id', 'title', 'description', 'file_path',
        'status', 'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'             => 'Rascunho',
            'pending_signature' => 'Aguardando Assinatura',
            'signed'            => 'Assinado',
            'canceled'          => 'Cancelado',
            'archived'          => 'Arquivado',
            default             => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'             => 'gray',
            'pending_signature' => 'orange',
            'signed'            => 'green',
            'canceled'          => 'red',
            'archived'          => 'gray',
            default             => 'gray',
        };
    }
}
