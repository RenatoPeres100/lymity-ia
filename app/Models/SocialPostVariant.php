<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPostVariant extends Model
{
    protected $fillable = [
        'social_post_id', 'platform', 'caption', 'hashtags',
        'cta', 'creative_notes', 'status',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
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
}
