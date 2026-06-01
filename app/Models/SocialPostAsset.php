<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPostAsset extends Model
{
    protected $fillable = [
        'social_post_id', 'type', 'source', 'provider', 'path', 'public_url',
        'position', 'prompt', 'status', 'validation_error',
        'instagram_container_id', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function hasPublicUrl(): bool
    {
        return !empty($this->public_url) && str_starts_with($this->public_url, 'https://');
    }

    public function markValid(): static
    {
        $this->update(['status' => 'valid', 'validation_error' => null]);
        return $this;
    }

    public function markInvalid(string $error): static
    {
        $this->update(['status' => 'invalid', 'validation_error' => $error]);
        return $this;
    }
}
