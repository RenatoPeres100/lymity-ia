<?php

namespace App\Console\Commands\Blog;

use App\Models\User;
use App\Services\Blog\BlogBriefGenerationService;
use Illuminate\Console\Command;

class GenerateBlogBriefCommand extends Command
{
    protected $signature = 'blog:generate-brief
        {--topic= : Tópico do artigo}
        {--keyword= : Palavra-chave principal}
        {--goal= : Objetivo do artigo}';

    protected $description = 'Gera um briefing de blog com o Blog Planner IA (Gemini).';

    public function handle(BlogBriefGenerationService $service): int
    {
        $topic   = $this->option('topic')   ?? '';
        $keyword = $this->option('keyword') ?? '';
        $goal    = $this->option('goal')    ?? '';

        if (empty($topic)) {
            $this->error('--topic é obrigatório.');
            return self::FAILURE;
        }

        $admin = User::where('email', 'admin@lymity.local')->first()
            ?? User::first();

        if (!$admin) {
            $admin = User::first();
        }

        if (!$admin) {
            $this->error('Nenhum usuário encontrado no sistema.');
            return self::FAILURE;
        }

        $this->info("Gerando briefing...");
        $this->line("Tópico: {$topic}");
        $this->line("Keyword: {$keyword}");
        $this->line("Objetivo: {$goal}");

        try {
            $brief = $service->generate([
                'topic'   => $topic,
                'keyword' => $keyword,
                'goal'    => $goal,
            ], $admin);

            $this->newLine();
            $this->info("BRIEF_ID={$brief->id}");
            $this->info("STATUS={$brief->status}");
            $this->info("TITLE={$brief->title}");
            $this->info("KEYWORD={$brief->primary_keyword}");
            $this->info("OUTLINE=" . count($brief->outline ?? []) . " seções");
            $this->newLine();
            $this->line("Briefing criado com sucesso! Gere o artigo com:");
            $this->line("php artisan blog:generate-draft {$brief->id}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erro: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
