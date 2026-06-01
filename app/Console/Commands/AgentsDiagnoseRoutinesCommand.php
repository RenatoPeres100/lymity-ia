<?php

namespace App\Console\Commands;

use App\Models\AgentRoutine;
use App\Models\AgentRoutineRun;
use App\Models\AiEmployee;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\SocialPost;
use App\Services\Agents\AgentRoutineExecutionService;
use Illuminate\Console\Command;

class AgentsDiagnoseRoutinesCommand extends Command
{
    protected $signature   = 'agents:diagnose-routines';
    protected $description = 'Diagnóstico do motor de rotinas de agentes IA.';

    public function handle(AgentRoutineExecutionService $engine): int
    {
        $this->info('=== DIAGNÓSTICO: Motor de Rotinas de Agentes IA ===');
        $this->line('');

        // Provider
        $provider    = config('ai.provider', 'mock');
        $key         = !empty(config('ai.gemini_api_key'));
        $textModel   = config('ai.gemini_text_model', '—');
        $this->comment('[ Provider de IA ]');
        $this->line("  AI_PROVIDER      = {$provider}");
        $this->line("  GEMINI_API_KEY   = " . ($key ? 'configurado ✓' : '❌ ausente'));
        $this->line("  TEXT_MODEL       = {$textModel}");
        $providerOk = ($provider === 'google' && $key);
        $this->line("  STATUS           = " . ($providerOk ? '✓ Configurado' : '❌ Não configurado'));
        $this->line('');

        // Cron / Scheduler
        $this->comment('[ Cron & Scheduler ]');
        $crontab = shell_exec('crontab -l 2>/dev/null');
        $hasCron = str_contains($crontab ?? '', 'schedule:run');
        $this->line("  crontab schedule:run = " . ($hasCron ? '✓ Encontrado' : '❌ Não encontrado'));
        $this->line("  Scheduled commands:");
        $scheduledDue = [
            'agents:run-due-routines',
            'blog:publish-due',
            'social:publish-due',
            'content:run-publishing-cycle',
        ];
        foreach ($scheduledDue as $cmd) {
            $this->line("    - php artisan {$cmd}");
        }
        $this->line('');

        // Agents
        $this->comment('[ Agentes IA ]');
        $agents = AiEmployee::get();
        $active = $agents->where('status', 'active');
        $this->line("  Total agentes: {$agents->count()} | Ativos: {$active->count()}");
        foreach ($active as $a) {
            $this->line("  ✓ #{$a->id} {$a->name}");
        }
        $this->line('');

        // Routines
        $this->comment('[ Rotinas ]');
        $routines     = AgentRoutine::with('aiEmployee')->get();
        $activeR      = $routines->filter(fn($r) => $r->isActive());
        $pausedR      = $routines->filter(fn($r) => $r->isPaused());
        $withErrors   = $routines->whereNotNull('last_error');
        $this->line("  Total: {$routines->count()} | Ativas: {$activeR->count()} | Pausadas: {$pausedR->count()} | Com erro: {$withErrors->count()}");
        $this->line('');

        // Due now
        $dueNow = $engine->getDueRoutines(now());
        $this->comment('[ Devidas agora ]');
        $this->line("  Rotinas devidas: {$dueNow->count()}");
        foreach ($dueNow as $r) {
            $this->line("  → #{$r->id} {$r->title} (type={$r->routine_type} qty={$r->getEffectiveQuantity()})");
        }
        $this->line('');

        // Next 10 runs
        $this->comment('[ Próximas 10 execuções ]');
        $upcoming = AgentRoutine::where('active', true)
            ->whereNotNull('next_run_at')
            ->orderBy('next_run_at')
            ->limit(10)
            ->get();
        foreach ($upcoming as $r) {
            $this->line("  " . $r->next_run_at->format('d/m/Y H:i') . "  #{$r->id} {$r->title}");
        }
        $this->line('');

        // Error routines
        if ($withErrors->isNotEmpty()) {
            $this->comment('[ Rotinas com erro ]');
            foreach ($withErrors as $r) {
                $this->error("  #{$r->id} {$r->title}: " . mb_substr($r->last_error, 0, 100));
            }
            $this->line('');
        }

        // Content stats
        $this->comment('[ Conteúdo aguardando aprovação ]');
        $blogPending   = BlogPost::where('status', 'pending_approval')->count();
        $socialPending = SocialPost::where('status', 'pending_approval')->count();
        $approvalsPend = ApprovalRequest::where('status', 'pending')->count();
        $this->line("  BlogPosts pending_approval  : {$blogPending}");
        $this->line("  SocialPosts pending_approval: {$socialPending}");
        $this->line("  ApprovalRequests pending    : {$approvalsPend}");
        $this->line('');

        // Runs
        $this->comment('[ Execuções recentes ]');
        $runs = AgentRoutineRun::latest()->limit(5)->get();
        foreach ($runs as $run) {
            $icon = $run->status === 'completed' ? '✓' : ($run->status === 'failed' ? '✗' : '→');
            $this->line("  {$icon} #{$run->id} routine={$run->agent_routine_id} status={$run->status} items={$run->items_created} " . ($run->started_at?->diffForHumans() ?? ''));
        }
        $this->line('');

        // Issues summary
        $this->comment('[ Possíveis problemas ]');
        $issues = 0;
        if (!$providerOk)         { $this->error('  ❌ AI provider não configurado → rotinas de conteúdo falharão'); $issues++; }
        if (!$hasCron)            { $this->warn('  ⚠ crontab sem schedule:run → automações não executarão'); $issues++; }
        if ($withErrors->count()) { $this->warn('  ⚠ ' . $withErrors->count() . ' rotina(s) com erro'); $issues++; }
        if ($issues === 0)        { $this->info('  ✓ Nenhum problema detectado'); }

        $this->line('');
        $this->info('=== FIM DO DIAGNÓSTICO ===');

        return self::SUCCESS;
    }
}
