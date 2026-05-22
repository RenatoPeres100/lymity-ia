<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientWebsite extends Model
{
    protected $fillable = [
        'client_id', 'domain', 'platform', 'status',
        'primary_color', 'secondary_color', 'logo', 'notes',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ClientWebsitePage::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planning' => 'Planejamento',
            'active'   => 'Ativo',
            'paused'   => 'Pausado',
            'archived' => 'Arquivado',
            default    => $this->status ?? '—',
        };
    }

    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'internal'  => 'Interno (Lymity)',
            'wordpress' => 'WordPress',
            'external'  => 'Externo',
            default     => $this->platform ?? '—',
        };
    }
}
