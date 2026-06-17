<?php

namespace App\Console\Commands;

use App\Models\AgentTask;
use App\Services\AI\Execution\AgentTaskExecutionService;
use Illuminate\Console\Command;

class AgentRepairTaskSchedulesCommand extends Command
{
    protected $signature = 'agents:repair-task-schedules
                            {--dry-run : Apenas listar problemas sem corrigir (padrão)}
                            {--fix     : Aplicar correções de next_run_at}';

    protected $description = 'Verifica e corrige o next_run_at de AgentTasks ativas com schedule incorreto ou ausente.';

    public function handle(AgentTaskExecutionService $service): int
    {
        $fix    = $this->option('fix');
        $dryRun = !$fix;

        $mode = $dryRun ? '[DRY-RUN]' : '[FIX]';
        $this->info("=== agents:repair-task-schedules {$mode} ===");
        $this->info('');

        $problems = 0;
        $fixed    = 0;

        // 1. Active recurring tasks with null next_run_at
        $nullNext = AgentTask::where('status', 'active')
            ->whereNull('next_run_at')
            ->whereNotIn('frequency', ['manual'])
            ->get();

        if ($nullNext->isNotEmpty()) {
            $this->warn("Tasks ativas sem next_run_at ({$nullNext->count()}):");
            foreach ($nullNext as $task) {
                $this->line("  #{$task->id} [{$task->frequency}] {$task->title}");
                $problems++;

                if (!$dryRun) {
                    $service->calculateAndSaveNextRun($task);
                    $task->refresh();
                    $this->info("    → next_run_at definido para: {$task->next_run_at}");
                    $fixed++;
                } else {
                    $next = $service->calculateNextRun($task);
                    $this->line("    → seria: {$next}");
                }
            }
        } else {
            $this->line('  ✓ Nenhuma task ativa sem next_run_at');
        }

        $this->info('');

        // 2. Tasks with next_run_at in the distant past (>7 days)
        $staleNext = AgentTask::where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<', now()->subDays(7))
            ->whereNotIn('frequency', ['manual'])
            ->get();

        if ($staleNext->isNotEmpty()) {
            $this->warn("Tasks com next_run_at muito no passado (>7 dias) ({$staleNext->count()}):");
            foreach ($staleNext as $task) {
                $this->line("  #{$task->id} [{$task->frequency}] {$task->title} — next_run_at={$task->next_run_at}");
                $problems++;

                if (!$dryRun) {
                    $service->calculateAndSaveNextRun($task);
                    $task->refresh();
                    $this->info("    → recalculado para: {$task->next_run_at}");
                    $fixed++;
                } else {
                    $next = $service->calculateNextRun($task);
                    $this->line("    → seria recalculado para: {$next}");
                }
            }
        } else {
            $this->line('  ✓ Nenhuma task com next_run_at muito defasado');
        }

        $this->info('');

        // 3. Verify all active tasks show their schedule
        $this->info('Estado atual das tasks ativas:');
        AgentTask::where('status', 'active')->orderBy('next_run_at')->get()->each(function ($t) {
            $next  = $t->next_run_at?->format('d/m/Y H:i') ?? 'SEM PRÓXIMA';
            $when  = $t->next_run_at ? ' (' . $t->next_run_at->diffForHumans() . ')' : '';
            $this->line("  #{$t->id} [{$t->frequency}] {$t->title} → {$next}{$when}");
        });

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
            $this->info("Corrigidos: {$fixed}");
        }

        return self::SUCCESS;
    }
}
