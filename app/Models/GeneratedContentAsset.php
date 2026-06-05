<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedContentAsset extends Model
{
    protected $fillable = [
        'generated_content_package_id',
        'asset_type', 'slide_order',
        'prompt', 'local_path', 'public_url',
        'provider', 'model',
        'status', 'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function generatedContentPackage(): BelongsTo
    {
        return $this->belongsTo(GeneratedContentPackage::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendente',
            'generated' => 'Gerado',
            'failed'    => 'Falhou',
            default     => $this->status ?? '—',
        };
    }

    public function isGenerated(): bool
    {
        return $this->status === 'generated' && ($this->public_url || $this->local_path);
    }
}
