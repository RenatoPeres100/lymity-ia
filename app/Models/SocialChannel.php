<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialChannel extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'platform', 'account_name', 'account_url',
        'profile_picture_url',
        'instagram_user_id', 'threads_user_id', 'facebook_page_id', 'external_account_id',
        'status', 'access_token', 'refresh_token', 'token_expires_at',
        'refresh_due_at', 'last_refreshed_at',
        'permissions', 'metadata', 'last_checked_at', 'last_error',
        'disconnected_at',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'access_token'      => 'encrypted',
        'refresh_token'     => 'encrypted',
        'permissions'       => 'array',
        'metadata'          => 'array',
        'token_expires_at'  => 'datetime',
        'refresh_due_at'    => 'datetime',
        'last_refreshed_at' => 'datetime',
        'last_checked_at'   => 'datetime',
        'disconnected_at'   => 'datetime',
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

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeInstagram($query)
    {
        return $query->where('platform', 'instagram');
    }

    public function scopeThreads($query)
    {
        return $query->where('platform', 'threads');
    }

    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        if ($user->company_id) {
            $query->where('company_id', $user->company_id);
        }
        if ($user->client_id) {
            $query->where('client_id', $user->client_id);
        }
        return $query;
    }

    // ── Status helpers ─────────────────────────────────────────────────────────

    public function isInstagram(): bool
    {
        return $this->platform === 'instagram';
    }

    public function isThreads(): bool
    {
        return $this->platform === 'threads';
    }

    public function canPublishThreads(): bool
    {
        return $this->isThreads()
            && $this->isConnected()
            && $this->hasValidToken()
            && !empty($this->threads_user_id)
            && config('threads.publishing_enabled', false);
    }

    public function getSafeAccountLabel(): string
    {
        return $this->account_name ?? $this->external_account_id ?? 'Canal ' . ucfirst($this->platform ?? 'desconhecido');
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function isExpired(): bool
    {
        return in_array($this->status, ['expired', 'needs_reconnect'])
            || ($this->token_expires_at && $this->token_expires_at->isPast());
    }

    public function needsReconnect(): bool
    {
        return $this->status === 'needs_reconnect';
    }

    public function shouldRefresh(): bool
    {
        if (!$this->isConnected() || empty($this->getRawOriginal('access_token'))) {
            return false;
        }
        if ($this->refresh_due_at) {
            return $this->refresh_due_at->isPast();
        }
        // Fallback: refresh if token expires in less than 15 days
        return $this->token_expires_at && $this->token_expires_at->diffInDays(now()) <= 15
            && $this->token_expires_at->isFuture();
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
            'refresh_due_at'  => null,
            'last_checked_at' => now(),
            'disconnected_at' => now(),
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

    public function markNeedsReconnect(string $reason = 'Token expirado ou revogado pela Meta.'): static
    {
        $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $reason);
        $this->update([
            'status'          => 'needs_reconnect',
            'last_error'      => $safe,
            'last_checked_at' => now(),
        ]);
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
            'not_configured'  => 'Não configurado',
            'disconnected'    => 'Desconectado',
            'connected'       => 'Conectado',
            'expired'         => 'Token expirado',
            'needs_reconnect' => 'Reconexão necessária',
            'error'           => 'Erro de conexão',
            'active'          => 'Ativo',
            'inactive'        => 'Inativo',
            default           => $this->status ?? '—',
        };
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->client?->name ?? $this->company?->name ?? 'Agência';
    }
}
