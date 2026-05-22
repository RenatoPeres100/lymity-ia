<?php

namespace App\Services\Ai;

use App\Jobs\RunAiTaskJob;
use App\Models\AiWorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AiWorkSchedulerService
{
    public function __construct(private AiTaskService $taskService) {}

    public function runDueSchedules(): int
    {
        $dispatched = 0;

        AiWorkSchedule::with('employee')
            ->where('active', true)
            ->get()
            ->filter(fn($s) => $s->isDue())
            ->each(function (AiWorkSchedule $schedule) use (&$dispatched) {
                try {
                    $this->processSchedule($schedule);
                    $dispatched++;
                } catch (\Throwable $e) {
                    Log::error("AiWorkScheduler: erro ao processar schedule {$schedule->id}: " . $e->getMessage());
                }
            });

        return $dispatched;
    }

    public function processSchedule(AiWorkSchedule $schedule): void
    {
        $employee = $schedule->employee;
        if (!$employee || $employee->status !== 'active') {
            return;
        }

        $task = $this->taskService->createManualTask($employee, [
            'title'     => "Execução agendada — {$employee->title}",
            'task_type' => 'general',
            'client_id' => $schedule->client_id,
            'metadata'  => ['schedule_id' => $schedule->id, 'triggered_by' => 'scheduler'],
        ]);

        RunAiTaskJob::dispatch($task->id);

        $schedule->update([
            'last_run_at' => now(),
            'next_run_at' => $this->calculateNextRun($schedule),
        ]);
    }

    private function calculateNextRun(AiWorkSchedule $schedule): ?Carbon
    {
        return match ($schedule->frequency) {
            'hourly'  => now()->addHour(),
            'daily'   => now()->addDay(),
            'weekly'  => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default   => null,
        };
    }
}
