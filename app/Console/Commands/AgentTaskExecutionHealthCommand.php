<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class AgentTaskExecutionHealthCommand extends Command
{
    protected $signature   = 'agents:task-execution-health';
    protected $description = 'Verifica a saúde completa do motor de execução de tarefas dos Funcionários IA.';

    private int $okCount   = 0;
    private int $warnCount = 0;
    private int $failCount = 0;

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║  Lymity IA — Agent Task Execution Health        ║');
        $this->info('║  ' . now()->format('Y-m-d H:i:s') . '                         ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        $this->checkCron();
        $this->checkScheduler();
        $this->checkQueueWorker();
        $this->checkRedis();
        $this->checkActiveTasks();
        $this->checkDueTasks();
        $this->checkMissingEmployee();
        $this->checkBrandContext();
        $this->checkRecentRuns();
        $this->checkStaleRuns();
        $this->checkRecentErrors();
        $this->checkNextExecution();
        $this->printRecommendations();

        $this->info('');
        $this->info('──────────────────────────────────────────────────');
        $this->info("Resultado: {$this->okCount} OK | {$this->warnCount} avisos | {$this->failCount} erros");
        $this->info('──────────────────────────────────────────────────');
        $this->info('');

        return $this->failCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function checkCron(): void
    {
        $this->section('Cron do Servidor');

        $crontab   = shell_exec('crontab -l 2>/dev/null') ?? '';
        $hasLymity = str_contains($crontab, 'lymity-ia') && str_contains($crontab, 'schedule:run');

        if ($hasLymity) {
            $this->pass('Cron instalado para /var/www/lymity-ia');
        } else {
            $this->err('Cron schedule:run NÃO instalado para lymity-ia. Rode: (crontab -l 2>/dev/null | grep -v "schedule:run"; echo "* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1") | crontab -');
        }
    }

    private function checkScheduler(): void
    {
        $this->section('Laravel Scheduler');

        try {
            $output = shell_exec('php artisan schedule:list --no-ansi 2>&1') ?? '';

            if (str_contains($output, 'agents:run-due-tasks')) {
                $this->pass('agents:run-due-tasks registrado no scheduler');
            } else {
                $this->err('agents:run-due-tasks NÃO está no scheduler — verifique routes/console.php');
            }

            foreach (['blog:publish-due', 'social:publish-due', 'agents:run-due-routines'] as $cmd) {
                if (str_contains($output, $cmd)) {
                    $this->pass("{$cmd} registrado");
                } else {
                    $this->caution("{$cmd} não encontrado no scheduler");
                }
            }
        } catch (\Throwable $e) {
            $this->err('Falha ao executar schedule:list: ' . $e->getMessage());
        }
    }

    private function checkQueueWorker(): void
    {
        $this->section('Queue Worker (Supervisor)');

        $queueConn = config('queue.default', 'sync');
        $this->line("  QUEUE_CONNECTION: {$queueConn}");

        if ($queueConn === 'sync') {
            $this->pass('Modo sync: jobs executam imediatamente, sem worker necessário');
            return;
        }

        $supervisorOutput = shell_exec('sudo supervisorctl status 2>/dev/null') ?? '';
        $psOutput         = shell_exec('ps aux 2>/dev/null | grep "queue:work" | grep -v grep') ?? '';

        if (str_contains($supervisorOutput, 'RUNNING')) {
            $this->pass('Supervisor workers RUNNING');
        } elseif (!empty(trim($psOutput))) {
            $this->pass('Queue worker em execução (processo direto)');
        } else {
            if (str_contains($supervisorOutput, 'FATAL')) {
                $this->err('Supervisor workers em FATAL — rode: sudo supervisorctl restart lymity-worker:*');
            } else {
                $this->err('Nenhum queue worker em execução — Jobs serão enfileirados mas não processados');
            }
        }

        if (Schema::hasTable('failed_jobs')) {
            $failed = DB::table('failed_jobs')->count();
            if ($failed === 0) {
                $this->pass('Nenhum failed job na fila');
            } else {
                $this->caution("{$failed} failed job(s) — verifique: php artisan queue:failed");
            }
        }

        if (Schema::hasTable('jobs')) {
            $pending = DB::table('jobs')->count();
            if ($pending === 0) {
                $this->pass('Nenhum job pendente na fila');
            } elseif ($pending <= 5) {
                $this->caution("{$pending} job(s) pendente(s) na fila — worker deve processar em breve");
            } else {
                $this->err("{$pending} jobs pendentes — worker pode estar travado");
            }
        }
    }

    private function checkRedis(): void
    {
        $this->section('Redis');

        try {
            Redis::ping();
            $this->pass('Redis conectado');
        } catch (\Throwable $e) {
            if (config('queue.default') === 'redis') {
                $this->err('Redis OFFLINE e QUEUE_CONNECTION=redis. Rode: sudo systemctl start redis-server');
            } else {
                $this->caution('Redis offline, mas queue não usa Redis');
            }
        }
    }

    private function checkActiveTasks(): void
    {
        $this->section('AgentTasks Ativas');

        if (!Schema::hasTable('agent_tasks')) {
            $this->caution('Tabela agent_tasks não encontrada');
            return;
        }

        $total  = DB::table('agent_tasks')->count();
        $active = DB::table('agent_tasks')->where('status', 'active')->count();
        $paused = DB::table('agent_tasks')->where('status', 'paused')->count();

        $this->line("  Total: {$total} | Ativas: {$active} | Pausadas: {$paused}");

        if ($active === 0) {
            $this->caution('Nenhuma AgentTask ativa — nada será executado automaticamente');
        } else {
            $this->pass("{$active} AgentTask(s) ativa(s)");

            $tasks = DB::table('agent_tasks')
                ->where('status', 'active')
                ->get(['id', 'title', 'task_type', 'frequency', 'next_run_at', 'last_run_at', 'ai_employee_id']);

            foreach ($tasks as $t) {
                $next = $t->next_run_at ? Carbon::parse($t->next_run_at)->format('d/m H:i') : 'nunca';
                $last = $t->last_run_at ? Carbon::parse($t->last_run_at)->format('d/m H:i') : 'nunca';
                $this->line("    #{$t->id} [{$t->task_type}] {$t->title} | freq={$t->frequency} | próxima={$next} | última={$last}");
            }
        }
    }

    private function checkDueTasks(): void
    {
        $this->section('Tasks Vencidas Agora');

        if (!Schema::hasTable('agent_tasks')) return;

        $due = DB::table('agent_tasks')
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->whereNotIn('frequency', ['manual'])
            ->count();

        if ($due === 0) {
            $this->pass('Nenhuma task vencida no momento — próximas execuções no horário correto');
        } else {
            $this->caution("{$due} task(s) vencida(s) — serão executadas na próxima chamada do scheduler");

            DB::table('agent_tasks')
                ->where('status', 'active')
                ->whereNotNull('next_run_at')
                ->where('next_run_at', '<=', now())
                ->whereNotIn('frequency', ['manual'])
                ->get(['id', 'title', 'next_run_at'])
                ->each(fn($t) => $this->line("    #{$t->id} {$t->title} — vencida em {$t->next_run_at}"));
        }

        $nullNext = DB::table('agent_tasks')
            ->where('status', 'active')
            ->whereNull('next_run_at')
            ->whereNotIn('frequency', ['manual'])
            ->count();

        if ($nullNext > 0) {
            $this->err("{$nullNext} task(s) ativa(s) com next_run_at nulo — rode: php artisan agents:repair-task-schedules --fix");
        }
    }

    private function checkMissingEmployee(): void
    {
        $this->section('Funcionário IA vinculado');

        if (!Schema::hasTable('agent_tasks') || !Schema::hasTable('ai_employees')) return;

        $orphan = DB::table('agent_tasks')
            ->where('agent_tasks.status', 'active')
            ->leftJoin('ai_employees', 'agent_tasks.ai_employee_id', '=', 'ai_employees.id')
            ->whereNull('ai_employees.id')
            ->count();

        $inactive = DB::table('agent_tasks')
            ->where('agent_tasks.status', 'active')
            ->join('ai_employees', 'agent_tasks.ai_employee_id', '=', 'ai_employees.id')
            ->whereIn('ai_employees.status', ['inactive', 'paused', 'disabled'])
            ->count();

        if ($orphan === 0 && $inactive === 0) {
            $this->pass('Todas as tasks ativas têm funcionário ativo vinculado');
        } else {
            if ($orphan > 0) $this->err("{$orphan} task(s) sem funcionário IA vinculado");
            if ($inactive > 0) $this->caution("{$inactive} task(s) com funcionário IA inativo/pausado");
        }
    }

    private function checkBrandContext(): void
    {
        $this->section('Brand Context');

        if (!Schema::hasTable('agency_brand_contexts')) return;

        $active = DB::table('agency_brand_contexts')
            ->where('active', true)
            ->count();

        if ($active > 0) {
            $this->pass("{$active} Brand Context(s) ativo(s)");
        } else {
            $this->err('Nenhum Brand Context ativo — execução de tasks será bloqueada');
        }
    }

    private function checkRecentRuns(): void
    {
        $this->section('Últimas Execuções (10 runs)');

        if (!Schema::hasTable('agent_task_runs')) return;

        $runs = DB::table('agent_task_runs')
            ->join('agent_tasks', 'agent_task_runs.agent_task_id', '=', 'agent_tasks.id')
            ->select('agent_task_runs.id', 'agent_task_runs.status', 'agent_task_runs.started_at', 'agent_task_runs.finished_at', 'agent_task_runs.error_message', 'agent_tasks.title')
            ->orderByDesc('agent_task_runs.id')
            ->limit(10)
            ->get();

        if ($runs->isEmpty()) {
            $this->caution('Nenhum AgentTaskRun encontrado ainda');
            return;
        }

        $statusMap = [
            'waiting_approval' => '<fg=green>✓</>',
            'failed'           => '<fg=red>✗</>',
            'running'          => '<fg=yellow>⟳</>',
            'queued'           => '<fg=gray>⌛</>',
        ];

        foreach ($runs as $r) {
            $icon = $statusMap[$r->status] ?? '○';
            $when = $r->started_at ? Carbon::parse($r->started_at)->format('d/m H:i') : '?';
            $err  = $r->error_message ? ' — ' . mb_substr($r->error_message, 0, 60) : '';
            $this->line("  {$icon} #{$r->id} [{$r->status}] {$r->title}{$err} ({$when})");
        }

        $lastFive    = $runs->take(5);
        $failedCount = $lastFive->where('status', 'failed')->count();
        $successCount= $lastFive->whereIn('status', ['waiting_approval', 'completed'])->count();

        if ($failedCount >= 3) {
            $this->err("Muitas falhas recentes: {$failedCount}/5 nos últimos runs");
        } elseif ($failedCount > 0) {
            $this->caution("{$failedCount}/5 runs recentes com falha");
        } else {
            $this->pass("{$successCount}/5 runs recentes com sucesso");
        }
    }

    private function checkStaleRuns(): void
    {
        $this->section('Runs Travados (>2h em running/queued)');

        if (!Schema::hasTable('agent_task_runs')) return;

        $stale = DB::table('agent_task_runs')
            ->whereIn('status', ['queued', 'running', 'preparing_context', 'generating_text', 'generating_image'])
            ->where('started_at', '<', now()->subHours(2))
            ->count();

        if ($stale === 0) {
            $this->pass('Nenhum run travado detectado');
        } else {
            $this->err("{$stale} run(s) em estado in-progress há mais de 2h — rode: php artisan agents:repair-task-execution --fix");
        }
    }

    private function checkRecentErrors(): void
    {
        $this->section('Erros Recentes (últimas 24h)');

        if (!Schema::hasTable('agent_task_runs')) return;

        $errors = DB::table('agent_task_runs')
            ->join('agent_tasks', 'agent_task_runs.agent_task_id', '=', 'agent_tasks.id')
            ->where('agent_task_runs.status', 'failed')
            ->where('agent_task_runs.started_at', '>=', now()->subDay())
            ->select('agent_task_runs.id', 'agent_task_runs.error_message', 'agent_task_runs.started_at', 'agent_tasks.title')
            ->orderByDesc('agent_task_runs.id')
            ->limit(5)
            ->get();

        if ($errors->isEmpty()) {
            $this->pass('Nenhuma falha nas últimas 24h');
        } else {
            $this->caution("{$errors->count()} falha(s) recente(s):");
            foreach ($errors as $e) {
                $err  = mb_substr($e->error_message ?? '?', 0, 80);
                $when = Carbon::parse($e->started_at)->format('H:i');
                $this->line("    [{$when}] #{$e->id} {$e->title}: {$err}");
            }
        }
    }

    private function checkNextExecution(): void
    {
        $this->section('Próximas Execuções');

        if (!Schema::hasTable('agent_tasks')) return;

        $next = DB::table('agent_tasks')
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->whereNotIn('frequency', ['manual'])
            ->orderBy('next_run_at')
            ->limit(5)
            ->get(['id', 'title', 'task_type', 'next_run_at']);

        if ($next->isEmpty()) {
            $this->caution('Nenhuma task com próxima execução calculada');
        } else {
            foreach ($next as $t) {
                $when = Carbon::parse($t->next_run_at)->diffForHumans();
                $this->line("  ⏰ #{$t->id} {$t->title} → {$t->next_run_at} ({$when})");
            }
            $this->pass("{$next->count()} task(s) com próxima execução agendada");
        }
    }

    private function printRecommendations(): void
    {
        $this->info('');
        $this->info(' Comandos Úteis');
        $this->line(' ' . str_repeat('─', 56));
        $this->line('  php artisan agents:run-due-tasks --dry-run                 # Ver tasks devidas');
        $this->line('  php artisan agents:run-due-tasks --task=ID --force --sync  # Executar agora');
        $this->line('  php artisan agents:repair-task-schedules --dry-run         # Ver schedules errados');
        $this->line('  php artisan agents:repair-task-schedules --fix             # Recalcular next_run_at');
        $this->line('  php artisan agents:repair-task-execution --dry-run         # Ver runs travados');
        $this->line('  php artisan agents:repair-task-execution --fix             # Corrigir runs travados');
        $this->line('  php artisan agents:diagnose-task ID                        # Diagnóstico por task');
        $this->line('  sudo supervisorctl restart lymity-worker:*                 # Reiniciar workers');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function section(string $title): void
    {
        $this->info('');
        $this->info(" {$title}");
        $this->line(' ' . str_repeat('─', 50));
    }

    private function pass(string $msg): void
    {
        $this->line("  <fg=green>✓</> {$msg}");
        $this->okCount++;
    }

    private function caution(string $msg): void
    {
        $this->line("  <fg=yellow>⚠</> {$msg}");
        $this->warnCount++;
    }

    private function err(string $msg): void
    {
        $this->line("  <fg=red>✗</> {$msg}");
        $this->failCount++;
    }
}
