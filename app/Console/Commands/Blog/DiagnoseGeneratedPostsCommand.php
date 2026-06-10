<?php

namespace App\Console\Commands\Blog;

use App\Models\BlogPost;
use App\Models\GeneratedContentPackage;
use App\Services\Blog\BlogPostImageResolver;
use Illuminate\Console\Command;

class DiagnoseGeneratedPostsCommand extends Command
{
    protected $signature   = 'blog:diagnose-generated-posts {--limit=10}';
    protected $description = 'Show last AI-generated blog posts with full image/package completeness check';

    public function __construct(private BlogPostImageResolver $imageResolver)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("=== Diagnose: últimos {$limit} posts gerados por IA ===");
        $this->newLine();

        $posts = BlogPost::whereNotNull('ai_employee_id')
            ->orWhereNotNull('generated_by_agent_id')
            ->latest('id')
            ->limit($limit)
            ->get();

        if ($posts->isEmpty()) {
            $this->warn('Nenhum post gerado por IA encontrado.');
            return self::SUCCESS;
        }

        $headers = [
            'ID', 'Título', 'Status', 'Has_Img', 'Resolved_URL',
            'PKG_ID', 'Asset_ID', 'Asset_Status', 'Asset_URL',
        ];
        $rows          = [];
        $noImageNeedFix = 0;

        foreach ($posts as $post) {
            $resolvedUrl = $this->imageResolver->resolveUrl($post);

            $package = $this->imageResolver->findPackage($post);
            $asset   = null;
            if ($package) {
                $asset = $package->assets()
                    ->where('asset_type', 'featured_image')
                    ->latest('id')
                    ->first();
            }

            $hasImg  = $post->featured_image ? 'YES' : 'NO';
            if (!$post->featured_image && $resolvedUrl) {
                $hasImg = 'NO*'; // no column but resolver found it
                $noImageNeedFix++;
            } elseif (!$post->featured_image && !$resolvedUrl) {
                $noImageNeedFix++;
            }

            $rows[] = [
                $post->id,
                mb_substr($post->title ?? '—', 0, 35),
                $post->status,
                $hasImg,
                $resolvedUrl ? mb_substr($resolvedUrl, 0, 45) : '—',
                $package?->id ?? '—',
                $asset?->id ?? '—',
                $asset?->status ?? '—',
                $asset?->public_url ? mb_substr($asset->public_url, 0, 45) : '—',
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        $withCol      = $posts->filter(fn($p) => $p->featured_image)->count();
        $withResolved = collect($rows)->filter(fn($r) => $r[4] !== '—')->count();

        $this->line("  Posts com featured_image (coluna): {$withCol}/{$posts->count()}");
        $this->line("  Posts com imagem resolvida:        {$withResolved}/{$posts->count()}");

        if ($noImageNeedFix > 0) {
            $this->newLine();
            $this->warn("  {$noImageNeedFix} posts sem imagem. Rode:");
            $this->line('    php artisan blog:repair-generated-images --dry-run');
            $this->line('    php artisan blog:repair-generated-images --fix');
        }

        return self::SUCCESS;
    }
}
