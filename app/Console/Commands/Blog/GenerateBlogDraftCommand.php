<?php

namespace App\Console\Commands\Blog;

use App\Models\ContentBrief;
use App\Models\User;
use App\Services\Blog\BlogDraftGenerationService;
use Illuminate\Console\Command;

class GenerateBlogDraftCommand extends Command
{
    protected $signature = 'blog:generate-draft {brief_id : ID do ContentBrief}';
    protected $description = 'Gera um rascunho de artigo de blog a partir de um briefing (Blog Writer IA).';

    public function handle(BlogDraftGenerationService $service): int
    {
        $briefId = (int) $this->argument('brief_id');
        $brief   = ContentBrief::find($briefId);

        if (!$brief) {
            $this->error("ContentBrief #{$briefId} não encontrado.");
            return self::FAILURE;
        }

        $admin = User::where('email', 'admin@lymity.local')->first()
            ?? User::first();

        if (!$admin) {
            $this->error('Nenhum usuário encontrado no sistema.');
            return self::FAILURE;
        }

        $this->info("Gerando artigo a partir do briefing #{$briefId}: {$brief->title}");

        try {
            $post = $service->generateFromBrief($brief, $admin);
            $approval = \App\Models\ApprovalRequest::where('approvable_type', \App\Models\BlogPost::class)
                ->where('approvable_id', $post->id)
                ->latest()
                ->first();

            $this->newLine();
            $this->info("BLOG_POST_ID={$post->id}");
            $this->info("APPROVAL_ID=" . ($approval?->id ?? 'N/A'));
            $this->info("STATUS={$post->status}");
            $this->info("TITLE={$post->title}");
            $this->info("SLUG={$post->slug}");
            $this->info("SEO_TITLE=" . ($post->seo_title ?? 'N/A'));
            $this->info("CONTENT_LEN=" . strlen($post->content ?? ''));
            $this->newLine();
            $this->line("Artigo criado e aguardando aprovação em /admin/blog/approvals");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erro: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
