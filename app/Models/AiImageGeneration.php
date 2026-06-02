<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiImageGeneration extends Model
{
    protected $fillable = [
        'company_id', 'client_id', 'created_by',
        'related_type', 'related_id',
        'channel', 'purpose', 'generation_type',
        'title', 'subject', 'prompt_context', 'final_prompt',
        'target_audience', 'tone', 'visual_style', 'brand_context',
        'overlay_mode', 'overlay_text',
        'slides_count', 'aspect_ratio',
        'status', 'provider', 'model',
        'request_metadata', 'response_metadata',
        'estimated_cost', 'input_tokens', 'output_tokens', 'duration_ms',
        'error_message', 'generated_at',
    ];

    protected $casts = [
        'request_metadata'  => 'array',
        'response_metadata' => 'array',
        'generated_at'      => 'datetime',
        'estimated_cost'    => 'decimal:8',
        'slides_count'      => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function images(): HasMany
    {
        return $this->hasMany(AiGeneratedImage::class);
    }

    public function activeImages(): HasMany
    {
        return $this->hasMany(AiGeneratedImage::class)->where('status', 'active')->orderBy('slide_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    // ── State helpers ─────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCarousel(): bool
    {
        return $this->generation_type === 'carousel';
    }

    public function isSingle(): bool
    {
        return $this->generation_type === 'single';
    }

    public function canRegenerate(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'draft']);
    }

    public function canAttach(): bool
    {
        return $this->isCompleted() && $this->activeImages()->exists();
    }

    public function mainImage(): ?AiGeneratedImage
    {
        return $this->activeImages()->first();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeForSocial($query)
    {
        return $query->where('channel', 'social');
    }

    public function scopeForBlog($query)
    {
        return $query->where('channel', 'blog');
    }
}
