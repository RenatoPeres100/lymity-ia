<?php

namespace App\Jobs;

use App\Models\ApprovalRequest;
use App\Services\Approvals\ApprovalEmailNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendApprovalEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int    $approvalRequestId,
        public readonly string $notificationType = 'created',
        public readonly bool   $force            = false,
    ) {}

    public function handle(ApprovalEmailNotificationService $service): void
    {
        if (!config('approval-notifications.email_enabled', true)) {
            Log::info("[ApprovalEmailJob] Skipped — notifications disabled. approval_id={$this->approvalRequestId}");
            return;
        }

        $approval = ApprovalRequest::find($this->approvalRequestId);

        if (!$approval) {
            Log::warning("[ApprovalEmailJob] ApprovalRequest #{$this->approvalRequestId} not found.");
            return;
        }

        try {
            match ($this->notificationType) {
                'created' => $service->sendCreatedNotification($approval, $this->force),
                'reminder' => $service->sendReminderNotification($approval),
                default    => Log::warning("[ApprovalEmailJob] Unknown type: {$this->notificationType}"),
            };
        } catch (Throwable $e) {
            Log::error("[ApprovalEmailJob] Failed for approval #{$this->approvalRequestId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("[ApprovalEmailJob] Job permanently failed for approval #{$this->approvalRequestId}: " . $exception->getMessage());
    }
}
