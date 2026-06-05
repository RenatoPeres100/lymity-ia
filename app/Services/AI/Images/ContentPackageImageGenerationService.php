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

    public function generateFeaturedImage(GeneratedContentPackage $package, string $prompt): ?GeneratedContentAsset
    {
        return $this->generateAsset($package, 'featured_image', $prompt, null);
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
            $visual['featured_image_url']    = $asset->public_url;
            $visual['featured_image_local']  = $asset->local_path;
            $visual['visual_status']         = 'generated';
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
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function generateAsset(
        GeneratedContentPackage $package,
        string $assetType,
        string $prompt,
        ?int $slideOrder
    ): ?GeneratedContentAsset {
        $asset = GeneratedContentAsset::create([
            'generated_content_package_id' => $package->id,
            'asset_type'  => $assetType,
            'slide_order' => $slideOrder,
            'prompt'      => $prompt,
            'provider'    => 'google',
            'model'       => config('ai.gemini_image_model', ''),
            'status'      => 'pending',
        ]);

        try {
            $storagePath = 'ai-images/packages/' . $package->id . '/' . $assetType . '_' . ($slideOrder ?? time()) . '.png';
            $result      = $this->gemini->generateAndStore($prompt, $storagePath);

            if (empty($result['local_path'])) {
                $asset->update([
                    'status'        => 'failed',
                    'error_message' => 'Geração retornou sem arquivo',
                ]);
                return null;
            }

            $disk      = config('ai-images.storage.disk', 'public');
            $publicUrl = Storage::disk($disk)->url($storagePath);

            $asset->update([
                'local_path' => $storagePath,
                'public_url' => $publicUrl,
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
