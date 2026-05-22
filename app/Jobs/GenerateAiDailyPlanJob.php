<?php

namespace App\Jobs;

use App\Models\AiEmployee;
use App\Services\Ai\AiTaskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiDailyPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(AiTaskService $service): void
    {
        $employees = AiEmployee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            try {
                $task = $service->createManualTask($employee, [
                    'title'     => 'Planejamento diário automático',
                    'task_type' => 'project_plan',
                    'metadata'  => ['triggered_by' => 'daily_plan_job', 'date' => now()->toDateString()],
                ]);

                RunAiTaskJob::dispatch($task->id);
            } catch (\Throwable $e) {
                Log::error("GenerateAiDailyPlanJob: erro para employee {$employee->id}: " . $e->getMessage());
            }
        }
    }
}
