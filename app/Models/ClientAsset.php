<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAsset extends Model
{
    protected $fillable = [
        'client_id', 'name', 'type', 'path', 'external_url', 'source', 'notes',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'image'      => 'Imagem',
            'video'      => 'Vídeo',
            'document'   => 'Documento',
            'logo'       => 'Logo',
            'brand_file' => 'Arquivo de Marca',
            'other'      => 'Outro',
            default      => $this->type ?? '—',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'upload'       => 'Upload',
            'google_drive' => 'Google Drive',
            'generated'    => 'Gerado por IA',
            'external'     => 'Externo',
            default        => $this->source ?? '—',
        };
    }

    public function getUrlAttribute(): ?string
    {
        return $this->external_url ?? ($this->path ? asset('storage/' . $this->path) : null);
    }
}
