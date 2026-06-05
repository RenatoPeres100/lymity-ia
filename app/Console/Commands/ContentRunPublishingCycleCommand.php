<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ContentRunPublishingCycleCommand extends Command
{
    protected $signature   = 'content:run-publishing-cycle';
    protected $description = 'Executa o ciclo completo de publicação: agentes IA, blog e posts sociais.';

    public function handle(): int
    {
        $this->info('=== CONTENT PUBLISHING CYCLE START ===');
        $start = now();

        // 0. Agent tasks (new engine)
        try {
            $this->info('[0/4] Executando tarefas operacionais de agentes IA...');
            Artisan::call('agents:run-due-tasks', [], $this->output);
        } catch (\Throwable $e) {
            $this->error('Erro em agents:run-due-tasks: ' . $e->getMessage());
            Log::error('[content:run-publishing-cycle] agent-tasks error: ' . $e->getMessage());
        }

        // 1. AI agent routines (legacy)
        try {
            $this->info('[1/4] Executando rotinas de agentes IA (legacy)...');
            Artisan::call('agents:run-due-routines', [], $this->output);
        } catch (\Throwable $e) {
            $this->error('Erro em agents:run-due-routines: ' . $e->getMessage());
            Log::error('[content:run-publishing-cycle] agents error: ' . $e->getMessage());
        }

        // 2. Fix approved → scheduled
        try {
            $this->info('[2/4] Corrigindo conteúdos aprovados com data futura...');
            Artisan::call('content:fix-approved-scheduled', [], $this->output);
        } catch (\Throwable $e) {
            $this->error('Erro em content:fix-approved-scheduled: ' . $e->getMessage());
            Log::error('[content:run-publishing-cycle] fix-approved-scheduled error: ' . $e->getMessage());
        }

        // 3. Blog publishing
        $blogPublished = 0;
        $blogFailed    = 0;
        try {
            $this->info('[3/4] Publicando posts de blog agendados...');
            Artisan::call('blog:publish-due', [], $this->output);
            $output = Artisan::output();
            if (preg_match('/PUBLISHED=(\d+)/', $output, $m)) $blogPublished = (int) $m[1];
            if (preg_match('/FAILED=(\d+)/', $output, $m))    $blogFailed    = (int) $m[1];
        } catch (\Throwable $e) {
            $this->error('Erro em blog:publish-due: ' . $e->getMessage());
            Log::error('[content:run-publishing-cycle] blog error: ' . $e->getMessage());
        }

        // 4. Social publishing
        $socialPublished = 0;
        $socialFailed    = 0;
        try {
            $this->info('[4/4] Publicando posts sociais agendados...');
            Artisan::call('social:publish-due', [], $this->output);
            $output = Artisan::output();
            if (preg_match('/PUBLISHED=(\d+)/', $output, $m)) $socialPublished = (int) $m[1];
            if (preg_match('/FAILED=(\d+)/', $output, $m))    $socialFailed    = (int) $m[1];
        } catch (\Throwable $e) {
            $this->error('Erro em social:publish-due: ' . $e->getMessage());
            Log::error('[content:run-publishing-cycle] social error: ' . $e->getMessage());
        }

        $elapsed = $start->diffInSeconds(now());
        $this->info("=== CONTENT PUBLISHING CYCLE FINISHED ({$elapsed}s) ===");
        $this->line("BLOG: published={$blogPublished} failed={$blogFailed}");
        $this->line("SOCIAL: published={$socialPublished} failed={$socialFailed}");

        Log::info('[content:run-publishing-cycle] cycle finished', [
            'blog_published'   => $blogPublished,
            'blog_failed'      => $blogFailed,
            'social_published' => $socialPublished,
            'social_failed'    => $socialFailed,
            'elapsed_seconds'  => $elapsed,
        ]);

        return self::SUCCESS;
    }
}
