<?php

namespace App\Services\AiImages;

use App\Models\AiGeneratedImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AiGeneratedImageValidatorService
{
    public function validate(AiGeneratedImage $image): array
    {
        $errors = [];

        // 1. Storage file exists
        $disk = config('ai-images.storage.disk', 'public');
        if (!Storage::disk($disk)->exists($image->storage_path)) {
            $errors[] = "Arquivo não encontrado em storage: {$image->storage_path}";
        }

        // 2. Public URL accessible
        if ($image->public_url) {
            try {
                $response = Http::timeout(10)->head($image->public_url);
                if ($response->status() !== 200) {
                    $errors[] = "URL pública retornou HTTP {$response->status()}: {$image->public_url}";
                }
            } catch (\Throwable $e) {
                $errors[] = "URL pública inacessível: {$e->getMessage()}";
            }
        }

        // 3. MIME type
        $allowedMimes = config('ai-images.image.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp']);
        if ($image->mime_type && !in_array($image->mime_type, $allowedMimes)) {
            $errors[] = "MIME type inválido: {$image->mime_type}";
        }

        // 4. File size
        if (Storage::disk($disk)->exists($image->storage_path)) {
            $maxBytes = config('ai-images.image.max_size_mb', 8) * 1024 * 1024;
            $size     = Storage::disk($disk)->size($image->storage_path);
            if ($size > $maxBytes) {
                $errors[] = "Arquivo excede tamanho máximo: " . round($size / 1024 / 1024, 2) . " MB";
            }

            // 5. getimagesize check
            $absolutePath = Storage::disk($disk)->path($image->storage_path);
            $imageInfo = @getimagesize($absolutePath);
            if (!$imageInfo) {
                $errors[] = 'Arquivo não é uma imagem válida (getimagesize falhou).';
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    public function validateGeneration(int $generationId): array
    {
        $generation = \App\Models\AiImageGeneration::find($generationId);
        if (!$generation) {
            return ['valid' => false, 'errors' => ["Geração ID {$generationId} não encontrada."]];
        }

        $images = $generation->activeImages;
        if ($images->isEmpty()) {
            return ['valid' => false, 'errors' => ['Nenhuma imagem ativa encontrada nesta geração.']];
        }

        $allErrors = [];
        foreach ($images as $image) {
            $result = $this->validate($image);
            if (!$result['valid']) {
                foreach ($result['errors'] as $err) {
                    $allErrors[] = "Imagem #{$image->id} (slide {$image->slide_number}): {$err}";
                }
            }
        }

        return [
            'valid'  => empty($allErrors),
            'errors' => $allErrors,
            'total'  => $images->count(),
        ];
    }
}
