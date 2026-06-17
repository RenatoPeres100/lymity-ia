<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class LymityCronHealthCommand extends Command
{
    protected $signature   = 'lymity:cron-health';
    protected $description = 'Verifica a integridade do cron e dos schedulers principais do Lymity.';

    private int $ok      = 0;
    private int $warn    = 0;
    private int $fail    = 0;

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║     Lymity IA — Cron & Scheduler Health         ║');
        $this->info('║     ' . now()->format('Y-m-d H:i:s') . '                       ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        $this->checkCronInstalled();
        $this->checkScheduleList();
        $this->checkRedis();
        $this->checkQueueStatus();
        $this->checkAgentTasks();
        $this->checkRecentRuns();
        $this->checkRecentFailures();
        $this->checkThreadsIsolation();
        $this->checkRecentLogs();

        $this->info('');
        $this->info('──────────────────────────────────────────────────');
        $total = $this->ok + $this->warn + $this->fail;
        $this->info("Resultado: {$this->ok} OK | {$this->warn} avisos | {$this->fail} erros | {$total} verificações");
        $this->info('──────────────────────────────────────────────────');
        $this->info('');

        return $this->fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function checkCronInstalled(): void
    {
        $this->section('Cron do Servidor');

        $crontab = shell_exec('crontab -l 2>/dev/null') ?? '';
        $hasLymity = str_contains($crontab, 'lymity-ia') && str_contains($crontab, 'schedule:run');
        $hasAny    = str_contains($crontab, 'schedule:run');

        if ($hasLymity) {
            $this->pass('Cron Laravel instalado para /var/www/lymity-ia');
        } elseif ($hasAny) {
            $line = collect(explode("\n", $crontab))
                ->first(fn($l) => str_contains($l, 'schedule:run'));
            $this->warning('Cron schedule:run encontrado mas sem /var/www/lymity-ia: ' . trim($line));
        } else {
            $this->fail_('Cron Laravel NÃO instalado. Rode: (crontab -l 2>/dev/null; echo "* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1") | crontab -');
        }
    }

    private function checkScheduleList(): void
    {
        $this->section('Comandos no Scheduler');

        try {
            $output = shell_exec('php artisan schedule:list --no-ansi 2>&1') ?? '';

            $required = [
                'agents:run-due-tasks'        => 'Motor de tarefas IA',
                'blog:publish-due'            => 'Publicação de blog',
                'social:publish-due'          => 'Publicação social/Instagram',
                'agents:run-due-routines'     => 'Rotinas de agentes',
                'approvals:send-pending-reminders' => 'Notificações de aprovação',
                'system:health-check'         => 'Health check do sistema',
            ];

            foreach ($required as $cmd => $label) {
                if (str_contains($output, $cmd)) {
                    $this->pass("{$label} ({$cmd}) registrado");
                } else {
                    $this->fail_("{$label} ({$cmd}) NÃO encontrado no scheduler");
                }
            }

            // Threads — opcional mas deve estar visível quando habilitado
            $threadsScheduled  = str_contains($output, 'threads:publish-due');
            $threadsFeature    = config('features.threads_publishing_scheduler', false);

            if ($threadsFeature && $threadsScheduled) {
                $this->pass('threads:publish-due registrado (feature habilitada)');
            } elseif (!$threadsFeature && !$threadsScheduled) {
                $this->pass('threads:publish-due não agendado — correto (threads_publishing_scheduler=false)');
            } elseif ($threadsFeature && !$threadsScheduled) {
                $this->warning('threads:publish-due deveria estar no scheduler (feature=true) mas não encontrado');
            } else {
                $this->warning('threads:publish-due no scheduler mas threads_publishing_scheduler=false — inofensivo pois o command guarda THREADS_PUBLISHING_ENABLED');
            }

        } catch (\Throwable $e) {
            $this->fail_('Falha ao executar schedule:list: ' . $e->getMessage());
        }
    }

    private function checkRedis(): void
    {
        $this->section('Redis / Queue');

        $queueConn = config('queue.default', 'sync');
        $this->line("  Queue connection: {$queueConn}");

        try {
            Redis::ping();
            $this->pass('Redis conectado');
        } catch (\Throwable $e) {
            if ($queueConn === 'redis') {
                $this->fail_('Redis INDISPONÍVEL mas QUEUE_CONNECTION=redis. Jobs não serão processados. Inicie: sudo systemctl start redis-server');
            } else {
                $this->warning('Redis indisponível, mas queue usa: ' . $queueConn);
            }
        }
    }

    private function checkQueueStatus(): void
    {
        $this->section('Fila de Jobs');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
                $pending = DB::table('jobs')->count();
                if ($pending === 0) {
                    $this->pass('Nenhum job pendente na fila');
                } else {
                    $this->warning("{$pending} job(s) pendentes na fila");
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('failed_jobs')) {
                $failed = DB::table('failed_jobs')->count();
                if ($failed === 0) {
                    $this->pass('Nenhum failed job');
                } else {
                    $this->warning("{$failed} failed job(s) — verifique: php artisan queue:failed");
                }
            }
        } catch (\Throwable $e) {
            $this->warning('Não foi possível verificar fila: ' . $e->getMessage());
        }
    }

    private function checkAgentTasks(): void
    {
        $this->section('AgentTasks Ativas');

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('agent_tasks')) {
                $this->warning('Tabela agent_tasks não encontrada');
                return;
            }

            $tasks = DB::table('agent_tasks')
                ->where('status', 'active')
                ->select('id', 'title', 'task_type', 'next_run_at', 'ai_employee_id')
                ->get();

            if ($tasks->isEmpty()) {
                $this->warning('Nenhuma AgentTask ativa encontrada');
                return;
            }

            $this->pass("{$tasks->count()} AgentTask(s) ativa(s):");
            foreach ($tasks as $t) {
                $nextRun = $t->next_run_at ? Carbon::parse($t->next_run_at)->format('d/m H:i') : 'nunca';
                $this->line("    #{$t->id} [{$t->task_type}] {$t->title} — próxima: {$nextRun}");
            }

            $due = $tasks->filter(fn($t) => $t->next_run_at && Carbon::parse($t->next_run_at)->isPast());
            if ($due->isNotEmpty()) {
                $this->warning("{$due->count()} task(s) vencida(s) e aguardando execução — verifique se o worker está rodando");
            }
        } catch (\Throwable $e) {
            $this->warning('Erro ao verificar AgentTasks: ' . $e->getMessage());
        }
    }

    private function checkRecentRuns(): void
    {
        $this->section('Últimas Execuções (AgentTaskRuns)');

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('agent_task_runs')) {
                $this->warning('Tabela agent_task_runs não encontrada');
                return;
            }

            $runs = DB::table('agent_task_runs')
                ->join('agent_tasks', 'agent_task_runs.agent_task_id', '=', 'agent_tasks.id')
                ->select('agent_task_runs.id', 'agent_task_runs.status', 'agent_task_runs.created_at', 'agent_tasks.title')
                ->orderBy('agent_task_runs.created_at', 'desc')
                ->limit(5)
                ->get();

            if ($runs->isEmpty()) {
                $this->warning('Nenhum AgentTaskRun encontrado ainda');
                return;
            }

            foreach ($runs as $r) {
                $when = Carbon::parse($r->created_at)->diffForHumans();
                $this->line("    #{$r->id} [{$r->status}] {$r->title} — {$when}");
            }
        } catch (\Throwable $e) {
            $this->warning('Erro ao verificar runs: ' . $e->getMessage());
        }
    }

    private function checkRecentFailures(): void
    {
        $this->section('Falhas Recentes (últimas 24h)');

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('agent_task_runs')) {
                return;
            }

            $failures = DB::table('agent_task_runs')
                ->join('agent_tasks', 'agent_task_runs.agent_task_id', '=', 'agent_tasks.id')
                ->where('agent_task_runs.status', 'failed')
                ->where('agent_task_runs.created_at', '>=', now()->subDay())
                ->select('agent_task_runs.id', 'agent_task_runs.error_message', 'agent_task_runs.created_at', 'agent_tasks.title')
                ->orderBy('agent_task_runs.created_at', 'desc')
                ->limit(5)
                ->get();

            if ($failures->isEmpty()) {
                $this->pass('Nenhuma falha nas últimas 24h');
            } else {
                $this->warning("{$failures->count()} falha(s) recente(s):");
                foreach ($failures as $f) {
                    $err = mb_substr($f->error_message ?? 'sem detalhe', 0, 80);
                    $this->line("    #{$f->id} {$f->title}: {$err}");
                }
            }
        } catch (\Throwable $e) {
            $this->warning('Erro ao verificar falhas: ' . $e->getMessage());
        }
    }

    private function checkThreadsIsolation(): void
    {
        $this->section('Isolamento Threads');

        $publishingEnabled   = config('threads.publishing_enabled', false);
        $textFeature         = config('features.threads_text_publishing', false);
        $schedulerFeature    = config('features.threads_publishing_scheduler', false);
        $connectionFeature   = config('features.threads_connection', false);

        $this->line("  threads_connection:           " . ($connectionFeature ? 'true' : 'false'));
        $this->line("  threads_text_publishing:      " . ($textFeature ? 'true' : 'false'));
        $this->line("  threads_publishing_scheduler: " . ($schedulerFeature ? 'true' : 'false'));
        $this->line("  THREADS_PUBLISHING_ENABLED:   " . ($publishingEnabled ? 'true' : 'false'));

        if (!$publishingEnabled && !$schedulerFeature) {
            $this->pass('Threads isolado — não impacta Blog/Social/Agents');
        } elseif ($publishingEnabled && !$schedulerFeature) {
            $this->warning('THREADS_PUBLISHING_ENABLED=true mas threads_publishing_scheduler=false — scheduler não agenda o command');
        } else {
            $this->warning('Threads scheduler habilitado — certifique-se de que o canal está conectado e publicação testada');
        }
    }

    private function checkRecentLogs(): void
    {
        $this->section('Últimos Erros de Schedule/Cron no Log');

        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            $this->warning('Arquivo de log não encontrado');
            return;
        }

        try {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $errors = array_filter($lines, fn($l) =>
                str_contains($l, 'ERROR') &&
                (str_contains($l, 'schedule') || str_contains($l, 'cron') || str_contains($l, 'threads') || str_contains($l, 'agents:run') || str_contains($l, 'blog:publish') || str_contains($l, 'social:publish'))
            );

            $recent = array_slice(array_values($errors), -5);
            if (empty($recent)) {
                $this->pass('Nenhum erro recente de schedule/cron no log');
            } else {
                $this->warning(count($recent) . ' erro(s) schedule/cron no log:');
                foreach ($recent as $l) {
                    $this->line('  ' . mb_substr(trim($l), 0, 120));
                }
            }
        } catch (\Throwable $e) {
            $this->warning('Erro ao ler log: ' . $e->getMessage());
        }
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
        $this->ok++;
    }

    private function warning(string $msg): void
    {
        $this->line("  <fg=yellow>⚠</> {$msg}");
        $this->warn++;
    }

    private function fail_(string $msg): void
    {
        $this->line("  <fg=red>✗</> {$msg}");
        $this->fail++;
    }
}
