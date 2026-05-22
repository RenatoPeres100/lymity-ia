<?php

namespace App\Jobs;

use App\Models\AiTask;
use App\Services\Ai\AiTaskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAiTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(public int $taskId) {}

    public function handle(AiTaskService $service): void
    {
        $task = AiTask::find($this->taskId);

        if (!$task) {
            Log::warning("RunAiTaskJob: task {$this->taskId} not found.");
            return;
        }

        $service->runTask($task);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("RunAiTaskJob: task {$this->taskId} failed permanently: " . $e->getMessage());

        $task = AiTask::find($this->taskId);
        if ($task && !in_array($task->status, ['failed', 'canceled'])) {
            $task->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'finished_at' => now()]);
        }
    }
}
