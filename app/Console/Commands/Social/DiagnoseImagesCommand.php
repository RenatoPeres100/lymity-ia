<?php

namespace App\Console\Commands\Social;

use App\Models\SocialPost;
use App\Services\Social\SocialPostImageResolver;
use App\Services\Social\SocialPostReadinessService;
use Illuminate\Console\Command;

class DiagnoseImagesCommand extends Command
{
    protected $signature   = 'social:diagnose-images {--limit=10} {--post=}';
    protected $description = 'Diagnose social post image status and Instagram readiness';

    public function __construct(
        private SocialPostImageResolver $resolver,
        private SocialPostReadinessService $readiness,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $postId = $this->option('post');
        $limit  = (int) $this->option('limit');

        $this->info('=== Social Post Image Diagnose ===');
        $this->newLine();

        if ($postId) {
            $posts = SocialPost::whereKey($postId)->get();
            if ($posts->isEmpty()) {
                $this->error("Post #{$postId} não encontrado.");
                return self::FAILURE;
            }
        } else {
            $posts = SocialPost::latest('id')->limit($limit)->get();
        }

        $headers = [
            'ID', 'Title', 'Status', 'Sched', 'Has_PUB_IMG',
            'Resolved_URL', 'HTTPS', 'PKG_ID', 'Asset_ID', 'Asset_St',
            'Can_Sched', 'Can_Pub',
        ];
        $rows        = [];
        $needRepair  = [];

        foreach ($posts as $post) {
            $resolvedUrl = $this->resolver->resolveUrl($post);
            $isHttps     = $this->resolver->isValidPublicHttps($resolvedUrl);
            $package     = $this->resolver->findPackage($post);
            $asset       = null;

            if ($package) {
                $asset = $package->assets()
                    ->whereIn('asset_type', ['feed_image', 'featured_image'])
                    ->latest('id')
                    ->first();
            }

            $canSched = $this->readiness->canBeScheduled($post);
            $canPub   = $this->readiness->canBePublished($post);

            $rows[] = [
                $post->id,
                mb_substr($post->title ?? '—', 0, 30),
                $post->status,
                $post->scheduled_at?->format('d/m H:i') ?? '—',
                $post->public_image_url ? 'YES' : 'NO',
                $resolvedUrl ? mb_substr($resolvedUrl, 0, 40) : '—',
                $isHttps ? 'YES' : ($resolvedUrl ? 'NO' : '—'),
                $package?->id ?? '—',
                $asset?->id ?? '—',
                $asset?->status ?? '—',
                $canSched['ready'] ? 'YES' : 'NO',
                $canPub['ready']   ? 'YES' : 'NO',
            ];

            if (!$post->public_image_url && $resolvedUrl) {
                $needRepair[] = $post->id;
            }
        }

        $this->table($headers, $rows);
        $this->newLine();

        if (!empty($needRepair)) {
            $ids = implode(',', $needRepair);
            $this->warn('  Posts com imagem disponível mas não sincronizada:');
            foreach ($needRepair as $id) {
                $this->line("    php artisan social:repair-generated-images --fix --post={$id}");
            }
        }

        return self::SUCCESS;
    }
}
