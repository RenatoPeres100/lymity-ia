<?php

namespace App\Jobs;

use App\Models\AiWorkSchedule;
use App\Services\Ai\AiWorkSchedulerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiWorkScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $scheduleId) {}

    public function handle(AiWorkSchedulerService $scheduler): void
    {
        $schedule = AiWorkSchedule::find($this->scheduleId);

        if (!$schedule) {
            Log::warning("ProcessAiWorkScheduleJob: schedule {$this->scheduleId} not found.");
            return;
        }

        $scheduler->processSchedule($schedule);
    }
}
