<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialChannel extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'platform', 'account_name', 'account_url',
        'instagram_user_id', 'facebook_page_id', 'external_account_id',
        'status', 'access_token', 'refresh_token', 'token_expires_at',
        'permissions', 'metadata', 'last_checked_at', 'last_error',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'access_token'    => 'encrypted',
        'refresh_token'   => 'encrypted',
        'permissions'     => 'array',
        'metadata'        => 'array',
        'token_expires_at'=> 'datetime',
        'last_checked_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ── Status helpers ─────────────────────────────────────────────────────────

    public function isInstagram(): bool
    {
        return $this->platform === 'instagram';
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->token_expires_at && $this->token_expires_at->isPast());
    }

    public function hasValidToken(): bool
    {
        return !empty($this->getRawOriginal('access_token'))
            && !$this->isExpired()
            && $this->isConnected();
    }

    public function canPublish(): bool
    {
        return $this->isConnected()
            && $this->hasValidToken()
            && !empty($this->instagram_user_id)
            && config('meta.instagram_publishing_enabled', false);
    }

    public function markConnected(array $data = []): static
    {
        $this->update(array_merge([
            'status'          => 'connected',
            'last_error'      => null,
            'last_checked_at' => now(),
        ], $data));
        return $this;
    }

    public function markDisconnected(): static
    {
        $this->update([
            'status'          => 'disconnected',
            'access_token'    => null,
            'refresh_token'   => null,
            'last_checked_at' => now(),
        ]);
        return $this;
    }

    public function markError(string $error): static
    {
        // Never store token in error message
        $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $error);
        $this->update(['status' => 'error', 'last_error' => $safe, 'last_checked_at' => now()]);
        return $this;
    }

    public function markExpired(): static
    {
        $this->update(['status' => 'expired', 'last_checked_at' => now()]);
        return $this;
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'connected']);
    }

    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'instagram' => 'Instagram',
            'facebook'  => 'Facebook',
            'linkedin'  => 'LinkedIn',
            'tiktok'    => 'TikTok',
            'threads'   => 'Threads',
            'youtube'   => 'YouTube',
            'pinterest' => 'Pinterest',
            default     => ucfirst($this->platform ?? '—'),
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
            'not_configured' => 'Não configurado',
            'disconnected'   => 'Desconectado',
            'connected'      => 'Conectado',
            'expired'        => 'Token expirado',
            'error'          => 'Erro de conexão',
            'active'         => 'Ativo',
            'inactive'       => 'Inativo',
            default          => $this->status ?? '—',
        };
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->client?->name ?? $this->company?->name ?? 'Agência';
    }
}
