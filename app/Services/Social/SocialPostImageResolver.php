<?php

namespace App\Services\Social;

use App\Models\GeneratedContentPackage;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SocialPostImageResolver
{
    public function resolveUrl(SocialPost $post): ?string
    {
        // 1. Direct columns (order matters: prefer validated public URL)
        foreach (['public_image_url', 'image_url'] as $col) {
            if ($url = $this->normalizeUrlOrPath($post->$col ?? null)) {
                return $url;
            }
        }

        // 2. image_path converted to public URL
        if ($url = $this->pathToUrl($post->image_path ?? null)) {
            return $url;
        }

        // 3. metadata fields
        $meta = $post->metadata ?? [];
        foreach (['public_image_url', 'generated_image_url'] as $key) {
            if ($url = $this->normalizeUrlOrPath($meta[$key] ?? null)) {
                return $url;
            }
        }

        // 4. image_metadata fields
        $imgMeta = $post->image_metadata ?? [];
        foreach (['public_url', 'public_image_url'] as $key) {
            if ($url = $this->normalizeUrlOrPath($imgMeta[$key] ?? null)) {
                return $url;
            }
        }

        // 5. GeneratedContentPackage
        return $this->resolveFromGeneratedPackage($post);
    }

    public function resolvePath(SocialPost $post): ?string
    {
        if ($post->image_path) {
            return $post->image_path;
        }

        $package = $this->findPackage($post);
        if (!$package) {
            return null;
        }

        $visual = $package->visual_payload ?? [];
        return $visual['feed_image_local'] ?? $visual['featured_image_local'] ?? null;
    }

    public function resolvePrompt(SocialPost $post): ?string
    {
        if ($post->image_prompt) {
            return $post->image_prompt;
        }

        $package = $this->findPackage($post);
        if (!$package) {
            return null;
        }

        return ($package->visual_payload ?? [])['image_prompt'] ?? null;
    }

    public function resolveStatus(SocialPost $post): string
    {
        if ($post->image_status) {
            return $post->image_status;
        }

        if ($this->resolveUrl($post)) {
            return 'generated';
        }

        return 'missing';
    }

    public function normalizeUrlOrPath(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'https://') || str_starts_with($value, 'http://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return rtrim(config('app.url'), '/') . $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return rtrim(config('app.url'), '/') . '/' . $value;
        }

        if (str_contains($value, 'app/public/')) {
            $relative = substr($value, strpos($value, 'app/public/') + 11);
            return $this->pathToUrl($relative);
        }

        return $this->pathToUrl($value);
    }

    public function resolveFromGeneratedPackage(SocialPost $post): ?string
    {
        $package = $this->findPackage($post);
        if (!$package) {
            return null;
        }

        $visual = $package->visual_payload ?? [];

        // Try feed_image_url first (social), then featured_image_url (blog fallback)
        foreach (['feed_image_url', 'featured_image_url'] as $key) {
            if ($url = $this->normalizeUrlOrPath($visual[$key] ?? null)) {
                return $url;
            }
        }

        return $this->resolveFromAssets($post, $package);
    }

    public function resolveFromAssets(SocialPost $post, ?GeneratedContentPackage $package = null): ?string
    {
        $package ??= $this->findPackage($post);
        if (!$package) {
            return null;
        }

        $asset = $package->assets()
            ->whereIn('asset_type', ['feed_image', 'featured_image'])
            ->where('status', 'generated')
            ->latest('id')
            ->first();

        if (!$asset) {
            return null;
        }

        if ($url = $this->normalizeUrlOrPath($asset->public_url)) {
            return $url;
        }

        return $this->pathToUrl($asset->local_path);
    }

    public function isValidPublicHttps(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        if (!str_starts_with($url, 'https://')) {
            return false;
        }

        if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1') || str_contains($url, '::1')) {
            return false;
        }

        return true;
    }

    public function findPackage(SocialPost $post): ?GeneratedContentPackage
    {
        if ($post->generated_content_package_id) {
            return GeneratedContentPackage::find($post->generated_content_package_id);
        }

        return GeneratedContentPackage::where('generated_entity_type', SocialPost::class)
            ->where('generated_entity_id', $post->id)
            ->whereIn('package_type', ['instagram_post', 'instagram_carousel', 'social_post'])
            ->latest('id')
            ->first();
    }

    private function pathToUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            $disk = config('ai-images.storage.disk', 'public');
            if (Storage::disk($disk)->exists($path)) {
                $url = Storage::disk($disk)->url($path);
                // Make absolute if relative
                if (str_starts_with($url, '/')) {
                    $url = rtrim(config('app.url'), '/') . $url;
                }
                return $url;
            }
        } catch (\Throwable $e) {
            Log::debug("[SocialPostImageResolver] pathToUrl failed: " . $e->getMessage());
        }

        return null;
    }
}
