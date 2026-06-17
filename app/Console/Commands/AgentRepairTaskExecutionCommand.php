<?php

namespace App\Console\Commands;

use App\Models\AgentTaskRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AgentRepairTaskExecutionCommand extends Command
{
    protected $signature = 'agents:repair-task-execution
                            {--dry-run : Apenas listar problemas sem corrigir (padrão)}
                            {--fix     : Aplicar correções}';

    protected $description = 'Corrige runs travados, limpa cache locks e repara inconsistências de execução.';

    public function handle(): int
    {
        $fix    = $this->option('fix');
        $dryRun = !$fix;

        $mode = $dryRun ? '[DRY-RUN]' : '[FIX]';
        $this->info("=== agents:repair-task-execution {$mode} ===");
        $this->info('');

        $problems = 0;
        $fixed    = 0;

        // 1. Stale runs in-progress for more than 2 hours
        $staleStatuses = ['queued', 'running', 'preparing_context', 'generating_text', 'generating_image', 'researching', 'creating_approval'];
        $staleRuns = AgentTaskRun::whereIn('status', $staleStatuses)
            ->where('started_at', '<', now()->subHours(2))
            ->get();

        if ($staleRuns->isNotEmpty()) {
            $this->warn("Runs travados (>2h em status in-progress) — {$staleRuns->count()}:");
            foreach ($staleRuns as $run) {
                $age = now()->diffForHumans($run->started_at, true);
                $this->line("  #{$run->id} task={$run->agent_task_id} status={$run->status} — há {$age}");
                $problems++;

                if (!$dryRun) {
                    $run->update([
                        'status'        => 'failed',
                        'finished_at'   => now(),
                        'error_message' => "Run marcado como failed por repair-task-execution: status={$run->status} por {$age} sem finalizar.",
                    ]);
                    $this->info("    → marcado como failed");
                    $fixed++;
                }
            }
        } else {
            $this->line('  ✓ Nenhum run travado detectado (>2h in-progress)');
        }

        $this->info('');

        // 2. Clear scheduler cache locks
        $this->info('Cache Locks do Scheduler:');

        if (!$dryRun) {
            try {
                $result = \Artisan::call('schedule:clear-cache');
                $this->info("  ✓ Cache do scheduler limpo (schedule:clear-cache)");
                $fixed++;
            } catch (\Throwable $e) {
                $this->warn("  ⚠ schedule:clear-cache falhou: " . $e->getMessage());
            }

            // Also clear application cache keys related to agent task overlapping
            try {
                $keys = [
                    'framework/schedule-' . sha1('agents:run-due-tasks'),
                    'framework/schedule-' . sha1('content:run-publishing-cycle'),
                    'framework/schedule-' . sha1('blog:publish-due'),
                    'framework/schedule-' . sha1('social:publish-due'),
                ];
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
                $this->info("  ✓ Cache keys de overlap limpos");
            } catch (\Throwable $e) {
                $this->warn("  ⚠ Não foi possível limpar cache keys: " . $e->getMessage());
            }
        } else {
            $this->line('  [dry-run] seria executado: schedule:clear-cache + limpeza de overlap keys');
        }

        $this->info('');

        // 3. Summary of failed jobs in queue
        if (\Illuminate\Support\Facades\Schema::hasTable('failed_jobs')) {
            $failedCount = DB::table('failed_jobs')->count();
            if ($failedCount > 0) {
                $this->warn("Failed Jobs na fila: {$failedCount}");
                $this->line("  → Para ver: php artisan queue:failed");
                $this->line("  → Para limpar antigos: php artisan queue:flush");
                $this->line("  → Para retentar: php artisan queue:retry all");
                $problems++;
            } else {
                $this->line('  ✓ Nenhum failed job na fila');
            }
        }

        $this->info('');
        $this->info("=== RESULTADO ===");
        $this->info("Problemas encontrados: {$problems}");

        if ($dryRun) {
            if ($problems > 0) {
                $this->warn("Rode com --fix para corrigir.");
            } else {
                $this->info("Tudo OK — nenhuma correção necessária.");
            }
        } else {
            $this->info("Corrigidos/executados: {$fixed}");
        }

        return self::SUCCESS;
    }
}
