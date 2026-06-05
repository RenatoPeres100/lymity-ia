<?php

namespace App\Console\Commands;

use App\Services\AI\Execution\AgentTaskExecutionService;
use Illuminate\Console\Command;
use App\Models\AgentTask;
use App\Jobs\RunAgentTaskJob;

class RunDueAgentTasksCommand extends Command
{
    protected $signature = 'agents:run-due-tasks
                            {--dry-run : List tasks that would run without executing}
                            {--task=  : Run a specific task by ID}
                            {--force  : Force run even if not due}
                            {--now=   : Override current time (YYYY-MM-DD HH:MM:SS)}';

    protected $description = 'Run all due agent tasks (recurring AI employee tasks)';

    public function handle(AgentTaskExecutionService $service): int
    {
        $dryRun = $this->option('dry-run');
        $taskId = $this->option('task');
        $force  = $this->option('force');
        $now    = $this->option('now') ? \Carbon\Carbon::parse($this->option('now')) : now();

        $this->info('[agents:run-due-tasks] Starting at ' . $now->toDateTimeString());

        if ($taskId) {
            $task = AgentTask::find($taskId);
            if (!$task) {
                $this->error("Task ID {$taskId} not found.");
                return 1;
            }

            if ($dryRun) {
                $this->line("Would run: [{$task->id}] {$task->title}");
                return 0;
            }

            if (!$force && !$task->isActive()) {
                $this->warn("Task is not active. Use --force to run anyway.");
                return 1;
            }

            $this->line("Dispatching task: [{$task->id}] {$task->title}...");
            RunAgentTaskJob::dispatch($task);
            $this->info("Task dispatched to queue.");
            return 0;
        }

        $tasks = AgentTask::active()
            ->where(function ($q) use ($now, $force) {
                if ($force) return;
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            })
            ->whereNotIn('frequency', ['manual'])
            ->with('aiEmployee')
            ->get();

        if ($tasks->isEmpty()) {
            $this->line("No tasks due at this time.");
            return 0;
        }

        foreach ($tasks as $task) {
            if ($task->hasReachedDailyLimit()) {
                $this->line("  [SKIP daily limit] [{$task->id}] {$task->title}");
                continue;
            }

            if ($dryRun) {
                $this->line("  [WOULD RUN] [{$task->id}] {$task->title} (next: {$task->next_run_at?->format('d/m H:i')})");
                continue;
            }

            $this->line("  [DISPATCH] [{$task->id}] {$task->title}");
            RunAgentTaskJob::dispatch($task);

            // Recalculate next_run_at immediately
            $service->calculateAndSaveNextRun($task);
        }

        $this->info('[agents:run-due-tasks] Done.');
        return 0;
    }
}
