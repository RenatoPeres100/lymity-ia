<?php

namespace App\Jobs;

use App\Models\AgentRoutine;
use App\Services\Agents\AgentRoutineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunAgentRoutineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries    = 1;
    public int $timeout  = 120;

    public function __construct(public readonly int $agentRoutineId) {}

    public function handle(AgentRoutineService $service): void
    {
        $routine = AgentRoutine::with('aiEmployee', 'company')->find($this->agentRoutineId);

        if (!$routine) {
            Log::warning("[RunAgentRoutineJob] Rotina #{$this->agentRoutineId} não encontrada.");
            return;
        }

        if (!$routine->isActive()) {
            Log::info("[RunAgentRoutineJob] Rotina #{$routine->id} está pausada, ignorando.");
            return;
        }

        try {
            $run = $service->runRoutine($routine);
            Log::info("[RunAgentRoutineJob] Rotina #{$routine->id} finalizada. Status: {$run->status}");
        } catch (Throwable $e) {
            Log::error("[RunAgentRoutineJob] Erro crítico na rotina #{$routine->id}: " . $e->getMessage());
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("[RunAgentRoutineJob] Job falhou para rotina #{$this->agentRoutineId}: " . $exception->getMessage());
    }
}
