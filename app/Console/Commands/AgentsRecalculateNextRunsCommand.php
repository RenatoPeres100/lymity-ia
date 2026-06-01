<?php

namespace App\Console\Commands;

use App\Models\AgentRoutine;
use App\Services\Agents\AgentRoutineExecutionService;
use Illuminate\Console\Command;

class AgentsRecalculateNextRunsCommand extends Command
{
    protected $signature = 'agents:recalculate-next-runs
        {--routine= : Recalcular apenas esta rotina}
        {--dry-run : Mostrar sem salvar}';

    protected $description = 'Recalcula next_run_at para rotinas ativas.';

    public function handle(AgentRoutineExecutionService $engine): int
    {
        $routineId = $this->option('routine') ? (int)$this->option('routine') : null;
        $dryRun    = (bool) $this->option('dry-run');

        $query = AgentRoutine::where('active', true)->whereNotIn('status', ['paused', 'disabled']);
        if ($routineId) $query->where('id', $routineId);
        $routines = $query->get();

        $this->line("Recalculando next_run_at para {$routines->count()} rotina(s)...");

        foreach ($routines as $routine) {
            $next = $engine->calculateNextRunAt($routine, now());
            $label = $next?->format('Y-m-d H:i') ?? '—';

            if (!$dryRun) {
                $routine->update(['next_run_at' => $next]);
            }

            $this->line("  #{$routine->id} {$routine->title} → {$label}" . ($dryRun ? ' [dry-run]' : ' ✓'));
        }

        if (!$dryRun) {
            $this->info('next_run_at recalculado com sucesso.');
        }

        return self::SUCCESS;
    }
}
