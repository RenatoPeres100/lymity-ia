<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageIntegration extends Model
{
    protected $fillable = [
        'provider', 'client_id', 'company_id', 'status',
        'access_token', 'refresh_token', 'token_expires_at',
        'root_folder_id', 'metadata',
    ];

    protected $casts = [
        'metadata'         => 'array',
        'token_expires_at' => 'datetime',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ExternalFile::class);
    }

    public function getProviderLabelAttribute(): string
    {
        return match ($this->provider) {
            'google_drive' => 'Google Drive',
            'local'        => 'Armazenamento Local',
            default        => $this->provider,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'       => 'Ativo',
            'disconnected' => 'Desconectado',
            'error'        => 'Erro',
            default        => $this->status,
        };
    }
}
