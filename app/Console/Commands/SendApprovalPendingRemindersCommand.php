<?php

namespace App\Console\Commands;

use App\Jobs\SendApprovalEmailNotificationJob;
use App\Models\ApprovalRequest;
use Illuminate\Console\Command;
use Throwable;

class SendApprovalPendingRemindersCommand extends Command
{
    protected $signature   = 'approvals:send-pending-reminders';
    protected $description = 'Send reminder emails for pending approval requests approaching their deadline';

    public function handle(): int
    {
        if (!config('approval-notifications.reminder_enabled', true)) {
            $this->info('Lembretes desabilitados (APPROVAL_EMAIL_REMINDER_ENABLED=false).');
            return 0;
        }

        $maxReminders = config('approval-notifications.max_reminders', 3);

        $approvals = ApprovalRequest::where('status', 'pending')
            ->where('reminder_count', '<', $maxReminders)
            ->with(['approvable'])
            ->get();

        $dispatched = 0;
        $skipped    = 0;

        foreach ($approvals as $approval) {
            try {
                if (!$approval->needsReminder()) {
                    $skipped++;
                    continue;
                }

                SendApprovalEmailNotificationJob::dispatch($approval->id, 'reminder')
                    ->onQueue('default');

                $dispatched++;
                $this->line("  Enfileirado lembrete para approval #{$approval->id}: {$approval->title}");
            } catch (Throwable $e) {
                $this->error("  Erro ao enfileirar lembrete para #{$approval->id}: " . $e->getMessage());
            }
        }

        $this->info("Lembretes: {$dispatched} enfileirados, {$skipped} ignorados.");
        return 0;
    }
}
