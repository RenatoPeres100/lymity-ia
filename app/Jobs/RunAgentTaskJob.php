<?php

namespace App\Jobs;

use App\Models\AgentTask;
use App\Services\AI\Execution\AgentTaskExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunAgentTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(
        private AgentTask $task,
    ) {}

    public function handle(AgentTaskExecutionService $service): void
    {
        Log::info("[RunAgentTaskJob] Starting task #{$this->task->id}: {$this->task->title}");

        try {
            $run = $service->runTaskNow($this->task);
            Log::info("[RunAgentTaskJob] Completed task #{$this->task->id} → run #{$run->id} status: {$run->status}");
        } catch (Throwable $e) {
            Log::error("[RunAgentTaskJob] Failed task #{$this->task->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("[RunAgentTaskJob] Job failed for task #{$this->task->id}: " . $exception->getMessage());

        $this->task->update([
            'last_failure_at' => now(),
            'failure_count'   => $this->task->failure_count + 1,
        ]);
    }
}
