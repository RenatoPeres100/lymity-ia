<?php

namespace App\Console\Commands\Blog;

use App\Models\BlogPost;
use Illuminate\Console\Command;

class DiagnoseGeneratedPostsCommand extends Command
{
    protected $signature   = 'blog:diagnose-generated-posts {--limit=10}';
    protected $description = 'Show last AI-generated blog posts with field completeness check';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("=== Diagnose: últimos {$limit} posts gerados por IA ===");
        $this->newLine();

        $posts = BlogPost::whereNotNull('ai_employee_id')
            ->orWhereNotNull('generated_by_agent_id')
            ->latest()
            ->limit($limit)
            ->get();

        if ($posts->isEmpty()) {
            $this->warn('Nenhum post gerado por IA encontrado.');
            return self::SUCCESS;
        }

        $headers = ['ID', 'Título', 'Status', 'Imagem', 'Tags', 'Cats', 'MetaDesc', 'AltText', 'SEO', 'Conteúdo'];
        $rows    = [];

        foreach ($posts as $post) {
            $rows[] = [
                $post->id,
                mb_substr($post->title ?? '—', 0, 35),
                $post->status,
                $post->featured_image ? '✓' : '✗',
                !empty($post->tags) ? count((array)$post->tags) . ' tags' : '✗',
                !empty($post->categories) ? count((array)$post->categories) . ' cats' : '✗',
                $post->meta_description ? '✓' : '✗',
                $post->featured_image_alt ? '✓' : '✗',
                $post->seo_title ? '✓' : '✗',
                $post->content ? mb_strlen($post->content) . ' chars' : '✗',
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $withImage    = $posts->filter(fn($p) => $p->featured_image)->count();
        $withTags     = $posts->filter(fn($p) => !empty($p->tags))->count();
        $withCats     = $posts->filter(fn($p) => !empty($p->categories))->count();
        $withMeta     = $posts->filter(fn($p) => $p->meta_description)->count();
        $withContent  = $posts->filter(fn($p) => $p->content)->count();

        $this->line("  Posts com imagem:        {$withImage}/{$posts->count()}");
        $this->line("  Posts com tags:          {$withTags}/{$posts->count()}");
        $this->line("  Posts com categorias:    {$withCats}/{$posts->count()}");
        $this->line("  Posts com meta_desc:     {$withMeta}/{$posts->count()}");
        $this->line("  Posts com conteúdo:      {$withContent}/{$posts->count()}");

        return self::SUCCESS;
    }
}
