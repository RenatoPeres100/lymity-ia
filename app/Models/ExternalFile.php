<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExternalFile extends Model
{
    protected $fillable = [
        'client_id', 'company_id', 'storage_integration_id',
        'name', 'file_type', 'mime_type', 'size',
        'local_path', 'external_url', 'external_file_id',
        'source', 'related_type', 'related_id',
        'uploaded_by', 'notes',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function storageIntegration(): BelongsTo
    {
        return $this->belongsTo(StorageIntegration::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function related(): MorphTo
    {
        return $this->morphTo('related');
    }

    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        return app(\App\Services\Auth\AccessScopeService::class)->scopeFiles($user, $query);
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'upload'       => 'Upload',
            'google_drive' => 'Google Drive',
            'generated'    => 'Gerado por IA',
            'imported'     => 'Importado',
            default        => $this->source,
        };
    }

    public function getFileTypeLabelAttribute(): string
    {
        return match ($this->file_type) {
            'image'    => 'Imagem',
            'document' => 'Documento',
            'video'    => 'Vídeo',
            'audio'    => 'Áudio',
            'pdf'      => 'PDF',
            'other'    => 'Outro',
            default    => $this->file_type,
        };
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) return '—';
        $bytes = $this->size;
        if ($bytes < 1024)       return "{$bytes} B";
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 1) . ' GB';
    }

    public function isImage(): bool
    {
        return $this->file_type === 'image' || str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'pdf' || $this->mime_type === 'application/pdf';
    }
}
