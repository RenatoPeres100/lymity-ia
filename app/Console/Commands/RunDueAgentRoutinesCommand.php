<?php

namespace App\Console\Commands;

use App\Jobs\RunAgentRoutineJob;
use App\Services\Agents\AgentRoutineService;
use Illuminate\Console\Command;

class RunDueAgentRoutinesCommand extends Command
{
    protected $signature   = 'agents:run-due-routines {--sync : Run synchronously without queue}';
    protected $description = 'Enfileira e executa rotinas de agentes IA com next_run_at <= now()';

    public function handle(AgentRoutineService $service): int
    {
        $this->info('[agents:run-due-routines] Verificando rotinas vencidas...');

        $routines = $service->getDueRoutines();

        if ($routines->isEmpty()) {
            $this->line('  Nenhuma rotina vencida encontrada.');
            return self::SUCCESS;
        }

        $this->info("  Rotinas encontradas: {$routines->count()}");

        $queued = 0;
        $errors = 0;

        foreach ($routines as $routine) {
            try {
                if ($this->option('sync')) {
                    $run = $service->runRoutine($routine);
                    $icon = $run->status === 'completed' ? '✓' : '✗';
                    $this->line("  {$icon} [{$routine->id}] {$routine->title} → {$run->status}");
                } else {
                    RunAgentRoutineJob::dispatch($routine->id);
                    $this->line("  ⏳ [{$routine->id}] {$routine->title} → job enfileirado");
                }
                $queued++;
            } catch (\Throwable $e) {
                $errors++;
                $this->error("  ✗ [{$routine->id}] {$routine->title} → ERRO: " . $e->getMessage());
            }
        }

        $mode = $this->option('sync') ? 'executadas' : 'enfileiradas';
        $this->info("  Rotinas {$mode}: {$queued} | Erros: {$errors}");
        $this->info('[agents:run-due-routines] Concluído.');

        return self::SUCCESS;
    }
}
