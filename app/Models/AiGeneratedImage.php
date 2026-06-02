<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneratedImage extends Model
{
    protected $fillable = [
        'ai_image_generation_id',
        'slide_number', 'label',
        'storage_path', 'public_url',
        'mime_type', 'width', 'height',
        'prompt_used', 'alt_text',
        'status', 'metadata',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'slide_number' => 'integer',
        'width'        => 'integer',
        'height'       => 'integer',
    ];

    public function generation(): BelongsTo
    {
        return $this->belongsTo(AiImageGeneration::class, 'ai_image_generation_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
