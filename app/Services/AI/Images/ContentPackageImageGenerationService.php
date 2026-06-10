<?php

namespace App\Services\AI\Images;

use App\Models\GeneratedContentAsset;
use App\Models\GeneratedContentPackage;
use App\Services\AI\GeminiImageGenerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContentPackageImageGenerationService
{
    public function __construct(
        private GeminiImageGenerationService $gemini,
    ) {}

    public function canGenerateImages(): bool
    {
        return $this->gemini->isConfigured()
            && config('ai-images.enabled', false);
    }

    public function generateFeaturedImage(GeneratedContentPackage $package, string $prompt, ?string $imageAlt = null): ?GeneratedContentAsset
    {
        return $this->generateAsset($package, 'featured_image', $prompt, null, $imageAlt);
    }

    public function generateFeedImage(GeneratedContentPackage $package, string $prompt): ?GeneratedContentAsset
    {
        return $this->generateAsset($package, 'feed_image', $prompt, null);
    }

    public function generateCarouselSlides(GeneratedContentPackage $package, array $slides): array
    {
        $assets = [];
        foreach ($slides as $slide) {
            $prompt = $slide['image_prompt'] ?? $slide['visual_direction'] ?? "Slide {$slide['slide_number']}";
            $asset  = $this->generateAsset($package, 'carousel_slide', $prompt, $slide['slide_number'] ?? null);
            if ($asset) {
                $assets[] = $asset;
            }
        }
        return $assets;
    }

    public function saveImageAsset(
        GeneratedContentPackage $package,
        string $assetType,
        string $prompt,
        ?int $slideOrder,
        string $localPath,
        string $publicUrl,
        string $provider,
        string $model
    ): GeneratedContentAsset {
        return GeneratedContentAsset::create([
            'generated_content_package_id' => $package->id,
            'asset_type'   => $assetType,
            'slide_order'  => $slideOrder,
            'prompt'       => $prompt,
            'local_path'   => $localPath,
            'public_url'   => $publicUrl,
            'provider'     => $provider,
            'model'        => $model,
            'status'       => 'generated',
        ]);
    }

    public function attachToContentPackage(GeneratedContentPackage $package, GeneratedContentAsset $asset): void
    {
        $visual = $package->visual_payload ?? [];

        if ($asset->asset_type === 'featured_image') {
            $visual['featured_image_url']      = $asset->public_url;
            $visual['featured_image_local']    = $asset->local_path;
            $visual['featured_image_asset_id'] = $asset->id;
            $visual['visual_status']           = 'generated';
        } elseif ($asset->asset_type === 'feed_image') {
            $visual['feed_image_url']    = $asset->public_url;
            $visual['feed_image_local']  = $asset->local_path;
            $visual['visual_status']     = 'generated';
        } elseif ($asset->asset_type === 'carousel_slide') {
            if (!isset($visual['slides'])) $visual['slides'] = [];
            $visual['slides'][] = [
                'order'      => $asset->slide_order,
                'url'        => $asset->public_url,
                'local_path' => $asset->local_path,
            ];
            $visual['visual_status'] = 'generated';
        }

        $package->update(['visual_payload' => $visual]);

        // Propagate image URL to the generated entity (BlogPost / SocialPost)
        if ($asset->public_url && in_array($asset->asset_type, ['featured_image', 'feed_image'])) {
            $this->propagateImageToEntity($package, $asset);
        }
    }

    private function propagateImageToEntity(GeneratedContentPackage $package, GeneratedContentAsset $asset): void
    {
        try {
            if (!$package->generated_entity_type || !$package->generated_entity_id) return;

            $entity = $package->generatedEntity;
            if (!$entity) return;

            if ($entity instanceof \App\Models\BlogPost) {
                $updates = ['featured_image' => $asset->public_url];
                if ($asset->metadata['alt'] ?? null) {
                    $updates['featured_image_alt'] = $asset->metadata['alt'];
                }
                $entity->update($updates);
                Log::info("[ContentPackageImageGen] BlogPost #{$entity->id} updated with featured_image.");
            } elseif ($entity instanceof \App\Models\SocialPost) {
                $updates = [
                    'public_image_url'  => $asset->public_url,
                    'image_url'         => $asset->public_url,
                    'image_path'        => $asset->local_path,
                    'image_status'      => 'generated',
                    'image_provider'    => $asset->provider ?? 'google',
                    'image_last_generated_at' => now(),
                    'image_metadata'    => array_merge($entity->image_metadata ?? [], [
                        'generated_content_asset_id'     => $asset->id,
                        'generated_content_package_id'   => $package->id,
                        'public_url'                     => $asset->public_url,
                        'local_path'                     => $asset->local_path,
                        'provider'                       => $asset->provider ?? 'google',
                        'image_status'                   => 'generated',
                    ]),
                ];
                $entity->update($updates);
                Log::info("[ContentPackageImageGen] SocialPost #{$entity->id} updated with public_image_url from {$asset->asset_type}.");
            }
        } catch (\Throwable $e) {
            Log::warning("[ContentPackageImageGen] Failed to propagate image to entity: " . $e->getMessage());
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function generateAsset(
        GeneratedContentPackage $package,
        string $assetType,
        string $prompt,
        ?int $slideOrder,
        ?string $imageAlt = null
    ): ?GeneratedContentAsset {
        $metadata = $imageAlt ? ['alt' => $imageAlt] : null;

        $asset = GeneratedContentAsset::create([
            'generated_content_package_id' => $package->id,
            'asset_type'  => $assetType,
            'slide_order' => $slideOrder,
            'prompt'      => $prompt,
            'provider'    => 'google',
            'model'       => config('ai.gemini_image_model', ''),
            'status'      => 'pending',
            'metadata'    => $metadata,
        ]);

        try {
            $storagePath = 'ai-images/packages/' . $package->id . '/' . $assetType . '_' . ($slideOrder ?? time());
            $result      = $this->gemini->generateAndStore($prompt, $storagePath);

            // generateAndStore returns 'path' (with extension), 'public_url', 'bytes', 'mime', 'model'
            $savedPath = $result['path'] ?? $result['local_path'] ?? null;
            $publicUrl = $result['public_url'] ?? null;

            if (empty($savedPath)) {
                $asset->update([
                    'status'        => 'failed',
                    'error_message' => 'Geração retornou sem arquivo. Result keys: ' . implode(',', array_keys($result)),
                ]);
                return null;
            }

            if (empty($publicUrl)) {
                $disk      = config('ai-images.storage.disk', 'public');
                $publicUrl = Storage::disk($disk)->url($savedPath);
            }

            $asset->update([
                'local_path' => $savedPath,
                'public_url' => $publicUrl,
                'model'      => $result['model'] ?? $asset->model,
                'status'     => 'generated',
            ]);

            $this->attachToContentPackage($package, $asset->fresh());

            return $asset->fresh();

        } catch (\Throwable $e) {
            Log::error("[ContentPackageImageGen] Failed for asset {$asset->id}: " . $e->getMessage());
            $asset->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
