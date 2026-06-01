<?php

namespace App\Services\Social;

use App\Models\ActivityLog;
use App\Models\SocialPost;
use App\Models\SocialPostAsset;
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
        private SocialImagePromptService     $promptService,
        private BrandContextService          $brandContext,
    ) {}

    // ── Text-first guard ───────────────────────────────────────────────────────

    private function assertHasCaption(SocialPost $post): void
    {
        if (empty(trim($post->main_caption ?? ''))) {
            throw new \RuntimeException(
                'Crie ou edite o texto do post antes de gerar a imagem. '
                . 'A legenda precisa estar preenchida para gerar uma imagem coerente.'
            );
        }
    }

    // ── Single image generation ────────────────────────────────────────────────

    public function generateSingleImageFromPost(SocialPost $post, User $user): SocialPost
    {
        $this->assertHasCaption($post);
        return $this->generateWithGemini($post, $user);
    }

    public function generateWithGemini(SocialPost $post, User $user): SocialPost
    {
        if (!$post->canBeEdited()) {
            throw new \RuntimeException("Post #{$post->id} não pode ser editado no status '{$post->status}'.");
        }

        $this->assertHasCaption($post);
        $this->geminiImage->assertConfigured();

        $post->update([
            'image_status'            => 'generating',
            'image_validation_status' => null,
            'image_validation_error'  => null,
        ]);

        $prompt     = $this->promptService->buildSingleImagePrompt($post);
        $captionHash = $post->captionHash();
        $storagePath = config('social.image.path', 'social/generated') . "/{$post->id}/image";

        try {
            $result = $this->geminiImage->generateAndStore($prompt, $storagePath);

            $post->update([
                'image_path'                        => $result['path'],
                'image_url'                         => $result['public_url'],
                'public_image_url'                  => $result['public_url'],
                'image_status'                      => 'generated',
                'image_provider'                    => 'gemini',
                'image_generation_mode'             => 'ai_single',
                'image_prompt'                      => $prompt,
                'image_prompt_source_hash'          => $this->promptService->hashPostVisualSource($post),
                'image_generated_from_caption_hash' => $captionHash,
                'image_last_generated_at'           => now(),
                'image_metadata'                    => [
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

            $post = $this->validateImage($post);
        } catch (\Throwable $e) {
            $safe = $this->redactSecrets($e->getMessage());
            Log::error("[SocialImageService] generateWithGemini #{$post->id}: {$safe}");

            $post->update([
                'image_status'           => 'failed',
                'image_validation_error' => $safe,
            ]);

            $this->logActivity($post, 'social_image_generation_failed', $user, ['error' => $safe]);
            throw new \RuntimeException("Falha ao gerar imagem: {$safe}");
        }

        return $post->fresh();
    }

    // ── Carousel generation ────────────────────────────────────────────────────

    public function generateCarouselFromPost(SocialPost $post, User $user): SocialPost
    {
        $this->assertHasCaption($post);

        if (!$post->canBeEdited()) {
            throw new \RuntimeException("Post #{$post->id} não pode ser editado no status '{$post->status}'.");
        }

        $this->geminiImage->assertConfigured();

        if (!config('social.carousel.enabled', true)) {
            throw new \RuntimeException('Geração de carrossel desabilitada (AI_SOCIAL_CAROUSEL_GENERATION_ENABLED=false).');
        }

        $post->update([
            'carousel_enabled' => true,
            'carousel_status'  => 'planning',
        ]);

        try {
            $carouselPlan = $this->geminiImage->generateCarouselPlan($post, $this->promptService);
        } catch (\Throwable $e) {
            $safe = $this->redactSecrets($e->getMessage());
            $post->update(['carousel_status' => 'failed']);
            throw new \RuntimeException("Falha ao planejar carrossel: {$safe}");
        }

        if (empty($carouselPlan['slides'])) {
            $post->update(['carousel_status' => 'failed']);
            throw new \RuntimeException('Gemini não retornou plano de slides válido.');
        }

        $slides  = $carouselPlan['slides'];
        $max     = config('social.carousel.max_slides', 5);
        $slides  = array_slice($slides, 0, $max);

        $post->update(['carousel_status' => 'generating', 'carousel_slide_count' => count($slides)]);

        // Delete existing assets
        $post->assets()->delete();

        $failures = [];

        foreach ($slides as $index => $slide) {
            $position = $index + 1;
            $asset    = SocialPostAsset::create([
                'social_post_id' => $post->id,
                'type'           => 'image',
                'source'         => 'generated',
                'provider'       => 'gemini',
                'position'       => $position,
                'status'         => 'generating',
                'public_url'     => '',
            ]);

            $slidePrompt = $this->promptService->buildCarouselSlidePrompt($post, $slides, $position);
            $storagePath = config('social.image.path', 'social/generated') . "/{$post->id}/slide_{$position}";

            try {
                $result = $this->geminiImage->generateAndStore($slidePrompt, $storagePath);

                $asset->update([
                    'path'       => $result['path'],
                    'public_url' => $result['public_url'],
                    'prompt'     => $slidePrompt,
                    'status'     => 'generated',
                    'metadata'   => ['bytes' => $result['bytes'], 'mime' => $result['mime']],
                ]);

                // Validate asset
                $validationResult = $this->validator->validateUrl($result['public_url']);
                if ($validationResult->valid) {
                    $asset->markValid();
                } else {
                    $asset->markInvalid($validationResult->error ?? 'Validação falhou');
                    $failures[] = "Slide {$position}: " . $validationResult->error;
                }
            } catch (\Throwable $e) {
                $safe = $this->redactSecrets($e->getMessage());
                $asset->update(['status' => 'failed', 'validation_error' => $safe]);
                $failures[] = "Slide {$position}: {$safe}";
                Log::error("[SocialImageService] Carousel slide {$position} failed for post #{$post->id}: {$safe}");
            }
        }

        $validCount = $post->validAssets()->count();
        $min        = config('social.carousel.min_slides', 3);

        if ($validCount >= $min) {
            $post->update([
                'carousel_status'                   => 'valid',
                'carousel_slide_count'              => $validCount,
                'image_generation_mode'             => 'ai_carousel',
                'image_generated_from_caption_hash' => $post->captionHash(),
                'image_last_generated_at'           => now(),
            ]);
        } else {
            $errorMsg = "Apenas {$validCount}/{$min} slides válidos. " . implode('; ', $failures);
            $post->update(['carousel_status' => 'failed']);
            throw new \RuntimeException($errorMsg);
        }

        $this->logActivity($post, 'social_carousel_generated', $user, [
            'valid_slides' => $validCount,
            'total_slides' => count($slides),
        ]);

        return $post->fresh();
    }

    // ── Upload & URL replacement ───────────────────────────────────────────────

    public function replaceImageFromUpload(SocialPost $post, UploadedFile $file, User $user): SocialPost
    {
        if (!$post->canBeEdited()) {
            throw new \RuntimeException("Post #{$post->id} não pode ser editado no status '{$post->status}'.");
        }

        $this->validateUploadedFile($file);

        $storagePath = config('social.image.path', 'social/generated') . "/{$post->id}";
        $ext         = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) $ext = 'jpg';
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
            'image_generation_mode'  => 'upload',
            'image_validation_status'=> null,
            'image_validation_error' => null,
            'image_last_generated_at'=> now(),
            'image_metadata'         => [
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getMimeType(),
                'size'          => $file->getSize(),
                'replaced_at'   => now()->toIso8601String(),
            ],
        ]);

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
            'image_generation_mode'  => 'external_url',
            'image_validation_status'=> null,
            'image_validation_error' => null,
            'image_last_generated_at'=> now(),
        ]);

        if ($post->status === 'pending_approval') {
            $post->update(['status' => 'draft']);
        }

        $this->logActivity($post, 'social_image_replaced_url', $user, ['url' => $url]);

        return $this->validateImage($post);
    }

    // ── Validation ─────────────────────────────────────────────────────────────

    public function validateImage(SocialPost $post): SocialPost
    {
        $post->update(['image_status' => 'validating']);

        $result = $this->validator->validateSocialPostImage($post);

        $post->update([
            'image_status'           => $result->valid ? 'valid' : 'invalid',
            'image_validation_status'=> $result->valid ? 'valid' : 'invalid',
            'image_validation_error' => $result->valid ? null : $result->error,
        ]);

        $this->logActivity(
            $post,
            $result->valid ? 'social_image_validated' : 'social_image_validation_failed',
            null,
            ['url' => $post->public_image_url, 'error' => $result->error]
        );

        return $post->fresh();
    }

    public function validateCarouselAssets(SocialPost $post): SocialPost
    {
        $assets   = $post->assets;
        $allValid = true;

        foreach ($assets as $asset) {
            if (!$asset->hasPublicUrl()) {
                $asset->markInvalid('URL pública ausente.');
                $allValid = false;
                continue;
            }
            $result = $this->validator->validateUrl($asset->public_url);
            if ($result->valid) {
                $asset->markValid();
            } else {
                $asset->markInvalid($result->error ?? 'Inválida');
                $allValid = false;
            }
        }

        $validCount = $post->validAssets()->count();
        $min        = config('social.carousel.min_slides', 3);
        $status     = ($allValid && $validCount >= $min) ? 'valid' : 'invalid';

        $post->update(['carousel_status' => $status]);

        return $post->fresh();
    }

    // ── Outdated image detection ───────────────────────────────────────────────

    public function markImageOutdatedIfTextChanged(SocialPost $post): void
    {
        if (empty($post->image_generated_from_caption_hash)) {
            return;
        }
        if ($post->isImageOutdated()) {
            $meta = (array) ($post->image_metadata ?? []);
            $meta['image_outdated'] = true;
            $meta['outdated_since'] = now()->toIso8601String();
            $post->update(['image_metadata' => $meta]);
        }
    }

    // ── Cleanup ────────────────────────────────────────────────────────────────

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

    // ── Private helpers ────────────────────────────────────────────────────────

    private function validateUploadedFile(UploadedFile $file): void
    {
        $maxBytes     = (int) (config('social.image.max_size_mb', 8) * 1024 * 1024);
        $allowedMimes = config('social.image.allowed_mimes', ['image/jpeg', 'image/png']);

        if ($file->getSize() > $maxBytes) {
            throw new \RuntimeException('Arquivo muito grande. Máximo: ' . config('social.image.max_size_mb', 8) . 'MB.');
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \RuntimeException('Tipo de arquivo inválido. Use JPEG ou PNG.');
        }

        $imageInfo = @getimagesize($file->getPathname());
        if (!$imageInfo) {
            throw new \RuntimeException('Arquivo não é uma imagem válida.');
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
