<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use App\Models\GeneratedContentPackage;
use Illuminate\Support\Facades\Storage;

class BlogPostImageResolver
{
    public function resolveUrl(BlogPost $post): ?string
    {
        if ($url = $this->normalizeUrlOrPath($post->featured_image)) {
            return $url;
        }

        $meta = $post->ai_metadata ?? [];
        foreach (['featured_image_url', 'image_url'] as $key) {
            if ($url = $this->normalizeUrlOrPath($meta[$key] ?? null)) {
                return $url;
            }
        }

        return $this->resolveFromGeneratedPackage($post);
    }

    public function resolveAlt(BlogPost $post): string
    {
        if ($alt = $post->featured_image_alt) {
            return $alt;
        }

        $meta = $post->ai_metadata ?? [];
        if ($alt = $meta['image_alt'] ?? null) {
            return $alt;
        }

        $package = $this->findPackage($post);
        if ($package) {
            $visual = $package->visual_payload ?? [];
            if ($alt = $visual['image_alt'] ?? null) {
                return $alt;
            }
        }

        return $post->title ?? 'Imagem destacada';
    }

    public function resolveCaption(BlogPost $post): ?string
    {
        if ($caption = $post->featured_image_caption) {
            return $caption;
        }

        $meta = $post->ai_metadata ?? [];
        if ($caption = $meta['image_caption'] ?? null) {
            return $caption;
        }

        $package = $this->findPackage($post);
        if ($package) {
            $visual = $package->visual_payload ?? [];
            return $visual['image_caption'] ?? null;
        }

        return null;
    }

    public function normalizeUrlOrPath(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return '/' . $value;
        }

        // app/public path (e.g. /var/www/.../storage/app/public/foo.jpg)
        if (str_contains($value, 'app/public/')) {
            $relative = substr($value, strpos($value, 'app/public/') + 11);
            return Storage::disk('public')->url($relative);
        }

        // Relative path on public disk
        if (Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        return null;
    }

    public function resolveFromGeneratedPackage(BlogPost $post): ?string
    {
        $package = $this->findPackage($post);
        if (!$package) {
            return null;
        }

        $visual = $package->visual_payload ?? [];

        if ($url = $this->normalizeUrlOrPath($visual['featured_image_url'] ?? null)) {
            return $url;
        }

        return $this->resolveFromAssets($post, $package);
    }

    public function resolveFromAssets(BlogPost $post, ?GeneratedContentPackage $package = null): ?string
    {
        $package ??= $this->findPackage($post);
        if (!$package) {
            return null;
        }

        $asset = $package->assets()
            ->where('asset_type', 'featured_image')
            ->where('status', 'generated')
            ->latest('id')
            ->first();

        if (!$asset) {
            return null;
        }

        if ($url = $this->normalizeUrlOrPath($asset->public_url)) {
            return $url;
        }

        return $this->normalizeUrlOrPath($asset->local_path);
    }

    public function findPackage(BlogPost $post): ?GeneratedContentPackage
    {
        if ($post->generated_content_package_id) {
            return GeneratedContentPackage::find($post->generated_content_package_id);
        }

        return GeneratedContentPackage::where('generated_entity_type', BlogPost::class)
            ->where('generated_entity_id', $post->id)
            ->where('package_type', 'blog_post')
            ->latest('id')
            ->first();
    }
}
