<?php

namespace App\Console\Commands;

use App\Models\AgentRoutine;
use App\Services\Agents\AgentRoutineExecutionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RunDueAgentRoutinesCommand extends Command
{
    protected $signature = 'agents:run-due-routines
        {--dry-run : Mostra o que executaria sem criar conteúdo}
        {--routine= : Executa apenas a rotina com este ID}
        {--force : Executa mesmo que next_run_at esteja no futuro}
        {--now= : Simula data/hora (ex: "2026-06-02 09:00")}
        {--sync : Compatibilidade retroativa (ignorado, sempre executa sync)}';

    protected $description = 'Executa rotinas de agentes IA com next_run_at <= now()';

    public function handle(AgentRoutineExecutionService $engine): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $routineId = $this->option('routine') ? (int) $this->option('routine') : null;
        $force     = (bool) $this->option('force');
        $nowRaw    = $this->option('now');

        $now = $nowRaw ? Carbon::parse($nowRaw) : now();

        $this->line("AGENTS_RUN_DUE_ROUTINES_STARTED");
        $this->line("NOW=" . $now->format('Y-m-d H:i:s'));
        if ($dryRun) $this->warn("MODE=dry-run (nada será criado)");

        // --force: set next_run_at to past so routine is picked up as due
        if ($force) {
            $q = AgentRoutine::where('active', true)->whereNotIn('status', ['paused', 'disabled']);
            if ($routineId) $q->where('id', $routineId);
            $q->update(['next_run_at' => $now->copy()->subMinute()]);
        }

        $due = $engine->getDueRoutines($now);
        if ($routineId) {
            $due = $due->filter(fn($r) => $r->id === $routineId)->values();
        }

        $this->line("DUE_ROUTINES=" . $due->count());

        if ($due->isEmpty()) {
            $this->line("Nenhuma rotina devida encontrada.");
            $this->line("AGENTS_RUN_DUE_ROUTINES_FINISHED");
            return self::SUCCESS;
        }

        $totalItems     = 0;
        $totalApprovals = 0;
        $failed         = 0;

        foreach ($due as $routine) {
            $this->line("");
            $this->info("ROUTINE #{$routine->id} [{$routine->aiEmployee?->name}] — {$routine->title}");
            $this->line("  type={$routine->routine_type} qty={$routine->getEffectiveQuantity()} freq={$routine->frequency}");

            $run = $engine->runRoutine($routine, $now, $dryRun);

            $this->line("  STATUS=" . ($run->status ?? '?'));
            $this->line("  ITEMS_CREATED=" . ($run->items_created ?? 0));
            $this->line("  APPROVALS_CREATED=" . ($run->approvals_created ?? 0));
            $this->line("  NEXT_RUN_AT=" . ($routine->fresh()->next_run_at?->format('Y-m-d H:i') ?? '—'));

            if ($run->error_message) {
                $this->error("  ERROR=" . $run->error_message);
                $failed++;
            }

            if ($run->output_summary) {
                foreach (array_slice(explode("\n", $run->output_summary), 0, 5) as $l) {
                    if (trim($l)) $this->line("  → {$l}");
                }
            }

            $totalItems     += ($run->items_created ?? 0);
            $totalApprovals += ($run->approvals_created ?? 0);
        }

        $this->line("");
        $this->line("TOTAL_ITEMS_CREATED={$totalItems}");
        $this->line("TOTAL_APPROVALS_CREATED={$totalApprovals}");
        $this->line("FAILED_ROUTINES={$failed}");
        $this->line("AGENTS_RUN_DUE_ROUTINES_FINISHED");

        return self::SUCCESS;
    }
}
