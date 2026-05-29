<?php

namespace App\Services\Social;

use App\Models\ActivityLog;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\AI\GeminiImageGenerationService;
use App\Services\Brand\BrandContextService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SocialImageService
{
    public function __construct(
        private GeminiImageGenerationService $geminiImage,
        private PublicImageValidatorService  $validator,
        private BrandContextService          $brandContext,
    ) {}

    public function generateWithGemini(SocialPost $post, User $user): SocialPost
    {
        if (!$post->canBeEdited()) {
            throw new \RuntimeException("Post #{$post->id} não pode ser editado no status '{$post->status}'.");
        }

        $this->geminiImage->assertConfigured();

        $post->update(['image_status' => 'generating', 'image_validation_status' => null, 'image_validation_error' => null]);

        $prompt = $this->buildImagePrompt($post);

        $storagePath = config('social.image.path', 'social/generated') . "/{$post->id}/image";

        try {
            $result = $this->geminiImage->generateAndStore($prompt, $storagePath);

            $post->update([
                'image_path'      => $result['path'],
                'image_url'       => $result['public_url'],
                'public_image_url'=> $result['public_url'],
                'image_status'    => 'generated',
                'image_provider'  => 'gemini',
                'image_prompt'    => $prompt,
                'image_metadata'  => [
                    'bytes'       => $result['bytes'],
                    'mime'        => $result['mime'],
                    'model'       => config('ai.gemini_image_model'),
                    'generated_at'=> now()->toIso8601String(),
                ],
            ]);

            $this->logActivity($post, 'social_image_generated', $user, [
                'provider'   => 'gemini',
                'public_url' => $result['public_url'],
            ]);

            // Auto-validate after generation
            $post = $this->validateImage($post);
        } catch (\Throwable $e) {
            $safe = $this->redactSecrets($e->getMessage());
            Log::error("[SocialImageService] generateWithGemini #{$post->id}: {$safe}");

            $post->update([
                'image_status'          => 'failed',
                'image_validation_error' => $safe,
            ]);

            $this->logActivity($post, 'social_image_generation_failed', $user, ['error' => $safe]);

            throw new \RuntimeException("Falha ao gerar imagem: {$safe}");
        }

        return $post->fresh();
    }

    public function replaceImageFromUpload(SocialPost $post, UploadedFile $file, User $user): SocialPost
    {
        if (!$post->canBeEdited()) {
            throw new \RuntimeException("Post #{$post->id} não pode ser editado no status '{$post->status}'.");
        }

        $this->validateUploadedFile($file);

        $storagePath = config('social.image.path', 'social/generated') . "/{$post->id}";
        $ext         = $file->getClientOriginalExtension() ?: 'jpg';
        $filename    = "image.{$ext}";
        $disk        = config('social.image.disk', 'public');

        Storage::disk($disk)->putFileAs($storagePath, $file, $filename);

        $baseUrl   = config('social.image.public_base_url', config('app.url') . '/storage');
        $publicUrl = rtrim($baseUrl, '/') . '/' . $storagePath . '/' . $filename;

        $post->update([
            'image_path'             => $storagePath . '/' . $filename,
            'image_url'              => $publicUrl,
            'public_image_url'       => $publicUrl,
            'image_status'           => 'replaced',
            'image_provider'         => 'upload',
            'image_validation_status'=> null,
            'image_validation_error' => null,
            'image_metadata'         => [
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getMimeType(),
                'size'          => $file->getSize(),
                'replaced_at'   => now()->toIso8601String(),
            ],
        ]);

        // If post was pending_approval, go back to draft since image changed
        if ($post->status === 'pending_approval') {
            $post->update(['status' => 'draft']);
        }

        $this->logActivity($post, 'social_image_replaced_upload', $user, ['public_url' => $publicUrl]);

        return $this->validateImage($post);
    }

    public function replaceImageFromUrl(SocialPost $post, string $url, User $user): SocialPost
    {
        if (!$post->canBeEdited()) {
            throw new \RuntimeException("Post #{$post->id} não pode ser editado no status '{$post->status}'.");
        }

        $post->update([
            'public_image_url'       => $url,
            'image_url'              => $url,
            'image_status'           => 'replaced',
            'image_provider'         => 'url',
            'image_validation_status'=> null,
            'image_validation_error' => null,
        ]);

        if ($post->status === 'pending_approval') {
            $post->update(['status' => 'draft']);
        }

        $this->logActivity($post, 'social_image_replaced_url', $user, ['url' => $url]);

        return $this->validateImage($post);
    }

    public function validateImage(SocialPost $post): SocialPost
    {
        $post->update(['image_status' => 'validating']);

        $result = $this->validator->validateSocialPostImage($post);

        $post->update([
            'image_status'           => $result->valid ? 'valid' : 'invalid',
            'image_validation_status'=> $result->valid ? 'valid' : 'invalid',
            'image_validation_error' => $result->valid ? null : $result->error,
        ]);

        $this->logActivity($post, $result->valid ? 'social_image_validated' : 'social_image_validation_failed', null, [
            'url'   => $post->public_image_url,
            'error' => $result->error,
        ]);

        return $post->fresh();
    }

    public function deleteGeneratedImage(SocialPost $post): void
    {
        $disk = config('social.image.disk', 'public');
        if ($post->image_path && Storage::disk($disk)->exists($post->image_path)) {
            Storage::disk($disk)->delete($post->image_path);
        }

        $post->update([
            'image_path'             => null,
            'image_url'              => null,
            'public_image_url'       => null,
            'image_status'           => 'missing',
            'image_validation_status'=> null,
            'image_validation_error' => null,
        ]);
    }

    private function buildImagePrompt(SocialPost $post): string
    {
        if (!empty($post->image_prompt)) {
            return $post->image_prompt;
        }

        $brandContext = $this->brandContext->getCompactContext();
        $objective    = $post->objective_label;
        $caption      = mb_substr($post->main_caption ?? $post->creative_brief ?? $post->title, 0, 200);

        $prompt = "Crie uma imagem quadrada 1080x1080 pixels para um post institucional da Lymity IA — agência de inteligência artificial aplicada ao crescimento de negócios digitais.

Estilo: premium, moderno, tecnológico e sofisticado. Visual limpo com fundo elegante escuro ou gradiente profissional. Elementos abstratos que remetam a inteligência artificial, automação e crescimento digital. Paleta: tons de azul profundo, roxo digital, branco e preto com detalhes luminosos.

Tema do post: {$caption}
Objetivo: {$objective}

Regras obrigatórias:
- Sem texto pequeno ou ilegível
- Sem logotipos de terceiros
- Sem pessoas reais identificáveis
- Sem conteúdo sensível
- Arte de alta qualidade, adequada para feed profissional no Instagram
- Proporção exatamente quadrada 1:1";

        if (!empty($brandContext)) {
            $prompt .= "\n\nContexto da marca: " . mb_substr($brandContext, 0, 300);
        }

        return $prompt;
    }

    private function validateUploadedFile(UploadedFile $file): void
    {
        $maxBytes  = (int) (config('social.image.max_size_mb', 8) * 1024 * 1024);
        $allowedMimes = config('social.image.allowed_mimes', ['image/jpeg', 'image/png']);

        if ($file->getSize() > $maxBytes) {
            throw new \RuntimeException("Arquivo muito grande. Máximo: " . config('social.image.max_size_mb', 8) . "MB.");
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \RuntimeException("Tipo de arquivo inválido. Use JPEG ou PNG.");
        }

        $imageInfo = @getimagesize($file->getPathname());
        if (!$imageInfo) {
            throw new \RuntimeException("Arquivo não é uma imagem válida.");
        }

        $minW = config('social.image.min_width', 320);
        $minH = config('social.image.min_height', 320);
        if ($imageInfo[0] < $minW || $imageInfo[1] < $minH) {
            throw new \RuntimeException("Imagem muito pequena. Mínimo: {$minW}x{$minH} pixels.");
        }
    }

    private function redactSecrets(string $msg): string
    {
        $msg = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $msg);
        $msg = preg_replace('/key=[A-Za-z0-9_\-]+/', 'key=[REDACTED]', $msg);
        return $msg;
    }

    private function logActivity(SocialPost $post, string $action, ?User $user, array $extra = []): void
    {
        if (!class_exists(ActivityLog::class)) return;
        try {
            ActivityLog::create([
                'user_id'     => $user?->id ?? Auth::id(),
                'action'      => $action,
                'module'      => 'social_media',
                'description' => "SocialPost #{$post->id} — {$action}",
                'metadata'    => array_merge(['social_post_id' => $post->id], $extra),
            ]);
        } catch (\Throwable) {}
    }
}
