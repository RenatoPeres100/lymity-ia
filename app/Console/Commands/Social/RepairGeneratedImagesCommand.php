<?php

namespace App\Console\Commands\Social;

use App\Models\SocialPost;
use App\Services\Social\SocialPostImageResolver;
use Illuminate\Console\Command;

class RepairGeneratedImagesCommand extends Command
{
    protected $signature   = 'social:repair-generated-images {--dry-run} {--fix} {--post=} {--limit=50}';
    protected $description = 'Sync generated image from GeneratedContentPackage/Asset to SocialPost';

    public function __construct(private SocialPostImageResolver $resolver)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDry  = !$this->option('fix');
        $postId = $this->option('post');
        $limit  = (int) $this->option('limit');
        $mode   = $isDry ? '[DRY-RUN]' : '[FIX]';

        $this->info("=== social:repair-generated-images {$mode} ===");
        $this->newLine();

        if ($postId) {
            $posts = SocialPost::whereKey($postId)->get();
        } else {
            $posts = SocialPost::whereNull('public_image_url')
                ->latest('id')
                ->limit($limit)
                ->get();
        }

        if ($posts->isEmpty()) {
            $this->info('Nenhum post para reparar.');
            return self::SUCCESS;
        }

        $headers = ['ID', 'Title', 'Status', 'PKG_ID', 'Asset_ID', 'Image_URL', 'Action'];
        $rows    = [];
        $fixed   = 0;

        foreach ($posts as $post) {
            $url     = $this->resolver->resolveUrl($post);
            $package = $this->resolver->findPackage($post);

            if (!$url) {
                $rows[] = [$post->id, mb_substr($post->title, 0, 35), $post->status, $package?->id ?? '—', '—', '—', 'no_image_found'];
                continue;
            }

            if (!$this->resolver->isValidPublicHttps($url)) {
                $rows[] = [$post->id, mb_substr($post->title, 0, 35), $post->status, $package?->id ?? '—', '—', mb_substr($url, 0, 45), 'not_https'];
                continue;
            }

            // Find asset id for display
            $assetId = null;
            if ($package) {
                $asset   = $package->assets()->whereIn('asset_type', ['feed_image', 'featured_image'])->where('status', 'generated')->latest('id')->first();
                $assetId = $asset?->id;
            }

            $action = $isDry ? 'would_fix' : 'fixed';

            if (!$isDry) {
                $path = $this->resolver->resolvePath($post);
                $post->update([
                    'public_image_url'        => $url,
                    'image_url'               => $url,
                    'image_path'              => $path,
                    'image_status'            => 'generated',
                    'image_validation_status' => 'valid',
                    'image_last_generated_at' => now(),
                    'image_metadata'          => array_merge($post->image_metadata ?? [], [
                        'synced_from_package_id' => $package?->id,
                        'synced_at'              => now()->toISOString(),
                        'public_url'             => $url,
                        'image_status'           => 'generated',
                    ]),
                ]);
                $fixed++;
            }

            $rows[] = [$post->id, mb_substr($post->title, 0, 35), $post->status, $package?->id ?? '—', $assetId ?? '—', mb_substr($url, 0, 50), $action];
        }

        $this->table($headers, $rows);
        $this->newLine();

        if ($isDry) {
            $toFix = collect($rows)->filter(fn($r) => $r[6] === 'would_fix')->count();
            $this->warn("Modo dry-run. {$toFix} posts seriam corrigidos.");
            $this->line('  Rode com --fix para aplicar.');
        } else {
            $this->info("Corrigidos: {$fixed} posts.");
        }

        return self::SUCCESS;
    }
}
