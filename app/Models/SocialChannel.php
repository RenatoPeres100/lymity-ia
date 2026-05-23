<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialChannel extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'platform', 'account_name',
        'account_url', 'status', 'access_token', 'refresh_token',
        'token_expires_at', 'metadata',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'metadata'        => 'array',
        'token_expires_at'=> 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'instagram'  => 'Instagram',
            'facebook'   => 'Facebook',
            'linkedin'   => 'LinkedIn',
            'tiktok'     => 'TikTok',
            'threads'    => 'Threads',
            'youtube'    => 'YouTube',
            'pinterest'  => 'Pinterest',
            default      => ucfirst($this->platform ?? '—'),
        };
    }

    public function getPlatformEmojiAttribute(): string
    {
        return match ($this->platform) {
            'instagram' => '📸',
            'facebook'  => '👥',
            'linkedin'  => '💼',
            'tiktok'    => '🎵',
            'threads'   => '🧵',
            'youtube'   => '▶️',
            'pinterest' => '📌',
            default     => '📡',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'       => 'Ativo',
            'paused'       => 'Pausado',
            'disconnected' => 'Desconectado',
            default        => $this->status ?? '—',
        };
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->client?->name ?? $this->company?->name ?? 'Agência';
    }
}
