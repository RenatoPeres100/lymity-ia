<?php

namespace App\Services\Files;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientFolder;
use App\Models\ExternalFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    public function uploadLocal(UploadedFile $file, array $data, User $user): ExternalFile
    {
        $clientId  = $data['client_id']  ?? null;
        $companyId = $data['company_id'] ?? null;

        $dir = $clientId
            ? "client-files/{$clientId}"
            : "company-files/{$companyId}";

        $filename  = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '_' . time()
            . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($dir, $filename, 'public');

        $fileType = $this->detectFileType($file->getMimeType(), $file->getClientOriginalExtension());

        $record = ExternalFile::create([
            'client_id'   => $clientId,
            'company_id'  => $companyId,
            'name'        => $data['name'] ?? $file->getClientOriginalName(),
            'file_type'   => $fileType,
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
            'local_path'  => $path,
            'source'      => 'upload',
            'uploaded_by' => $user->id,
            'notes'       => $data['notes'] ?? null,
        ]);

        $this->log('file_uploaded', "Arquivo '{$record->name}' enviado", $user);

        return $record;
    }

    public function listForAdmin(array $filters = [])
    {
        $query = ExternalFile::with(['client', 'uploader'])
            ->orderByDesc('created_at');

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }
        if (!empty($filters['file_type'])) {
            $query->where('file_type', $filters['file_type']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate(20)->withQueryString();
    }

    public function listForClient(Client $client, array $filters = [])
    {
        $query = ExternalFile::with(['uploader'])
            ->where('client_id', $client->id)
            ->orderByDesc('created_at');

        if (!empty($filters['file_type'])) {
            $query->where('file_type', $filters['file_type']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate(20)->withQueryString();
    }

    public function deleteFile(ExternalFile $file, User $user): bool
    {
        if ($file->local_path) {
            Storage::disk('public')->delete($file->local_path);
        }

        $this->log('file_deleted', "Arquivo '{$file->name}' deletado", $user);

        return $file->delete();
    }

    public function createClientFolder(Client $client, array $data): ClientFolder
    {
        $localPath = 'client-files/' . $client->id . '/' . Str::slug($data['name']);

        Storage::disk('public')->makeDirectory($localPath);

        $folder = ClientFolder::create([
            'client_id'   => $client->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'local_path'  => $localPath,
        ]);

        $this->log('folder_created', "Pasta '{$folder->name}' criada para cliente #{$client->id}");

        return $folder;
    }

    public function getPublicUrl(ExternalFile $file): ?string
    {
        if ($file->external_url) {
            return $file->external_url;
        }

        if ($file->local_path) {
            return Storage::disk('public')->url($file->local_path);
        }

        return null;
    }

    public function relateFile(ExternalFile $file, string $relatedType, int $relatedId): ExternalFile
    {
        $file->update([
            'related_type' => $relatedType,
            'related_id'   => $relatedId,
        ]);

        $this->log('file_related', "Arquivo '{$file->name}' vinculado a {$relatedType}#{$relatedId}");

        return $file->fresh();
    }

    private function detectFileType(string $mime, string $ext): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if ($mime === 'application/pdf' || $ext === 'pdf') return 'pdf';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        if (in_array($ext, ['doc', 'docx', 'odt', 'txt', 'rtf', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'])) return 'document';
        return 'other';
    }

    private function log(string $action, string $description, ?User $user = null): void
    {
        try {
            ActivityLog::create([
                'action'      => $action,
                'level'       => 'info',
                'description' => $description,
                'user_id'     => $user?->id,
            ]);
        } catch (\Throwable) {
        }
    }
}
