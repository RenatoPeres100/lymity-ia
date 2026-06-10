<?php

namespace App\Console\Commands\Blog;

use App\Models\BlogPost;
use App\Models\GeneratedContentAsset;
use App\Models\GeneratedContentPackage;
use Illuminate\Console\Command;

class RepairGeneratedImagesCommand extends Command
{
    protected $signature   = 'blog:repair-generated-images {--dry-run} {--fix} {--limit=50}';
    protected $description = 'Vincula imagem gerada ao BlogPost quando featured_image está vazio mas o asset existe';

    public function handle(): int
    {
        $isDry  = !$this->option('fix');
        $limit  = (int) $this->option('limit');
        $mode   = $isDry ? '[DRY-RUN]' : '[FIX]';

        $this->info("=== blog:repair-generated-images {$mode} ===");
        $this->newLine();

        $posts = BlogPost::whereNull('featured_image')
            ->whereNotNull('ai_employee_id')
            ->latest('id')
            ->limit($limit)
            ->get();

        if ($posts->isEmpty()) {
            $this->info('Nenhum post sem imagem encontrado.');
            return self::SUCCESS;
        }

        $headers = ['ID', 'Title', 'Status', 'PKG_ID', 'Asset_ID', 'Image_URL', 'Action'];
        $rows    = [];
        $fixed   = 0;

        foreach ($posts as $post) {
            $package = $this->findPackage($post);

            if (!$package) {
                $rows[] = [$post->id, mb_substr($post->title, 0, 40), $post->status, '—', '—', '—', 'no_package'];
                continue;
            }

            // 1. Try visual_payload
            $visual   = $package->visual_payload ?? [];
            $imageUrl = $visual['featured_image_url'] ?? null;
            $imageAlt = $visual['image_alt'] ?? null;
            $assetId  = $visual['featured_image_asset_id'] ?? null;

            // 2. Try assets table
            if (!$imageUrl) {
                $asset = $package->assets()
                    ->where('asset_type', 'featured_image')
                    ->where('status', 'generated')
                    ->latest('id')
                    ->first();

                if ($asset) {
                    $imageUrl = $asset->public_url;
                    $assetId  = $asset->id;
                }
            }

            if (!$imageUrl) {
                $rows[] = [$post->id, mb_substr($post->title, 0, 40), $post->status, $package->id, '—', '—', 'no_asset'];
                continue;
            }

            $action = $isDry ? 'would_fix' : 'fixed';

            if (!$isDry) {
                $updateData = ['featured_image' => $imageUrl];
                if (!$post->featured_image_alt && $imageAlt) {
                    $updateData['featured_image_alt'] = $imageAlt;
                }
                $post->update($updateData);
                $fixed++;
            }

            $rows[] = [
                $post->id,
                mb_substr($post->title, 0, 40),
                $post->status,
                $package->id,
                $assetId ?? '—',
                mb_substr($imageUrl, 0, 55),
                $action,
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        if ($isDry) {
            $toFix = collect($rows)->filter(fn($r) => $r[6] === 'would_fix')->count();
            $this->warn("Modo dry-run. {$toFix} posts seriam corrigidos.");
            $this->line('  Rode com --fix para aplicar: php artisan blog:repair-generated-images --fix');
        } else {
            $this->info("Corrigidos: {$fixed} posts.");
        }

        return self::SUCCESS;
    }

    private function findPackage(BlogPost $post): ?GeneratedContentPackage
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
