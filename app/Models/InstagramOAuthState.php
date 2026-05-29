<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstagramOAuthState extends Model
{
    protected $table = 'instagram_oauth_states';

    protected $fillable = [
        'state_hash',
        'user_id',
        'provider',
        'redirect_uri',
        'scopes',
        'auth_mode',
        'ip_address',
        'user_agent',
        'used_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'scopes'     => 'array',
        'metadata'   => 'array',
        'used_at'    => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public static function findValid(string $stateHash): ?self
    {
        return static::where('state_hash', $stateHash)
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->first();
    }
}
