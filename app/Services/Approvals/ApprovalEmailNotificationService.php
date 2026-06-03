<?php

namespace App\Services\Approvals;

use App\Mail\ApprovalRequestCreatedMail;
use App\Mail\ApprovalRequestReminderMail;
use App\Models\ActivityLog;
use App\Models\ApprovalEmailNotification;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class ApprovalEmailNotificationService
{
    // ── Public API ─────────────────────────────────────────────────────────────

    public function sendCreatedNotification(ApprovalRequest $approval, bool $force = false): void
    {
        if (!$this->isEnabled()) {
            $this->logActivity('approval_email_skipped', $approval, null, ['reason' => 'disabled']);
            return;
        }

        $recipients = $this->resolveRecipients($approval);

        if ($recipients->isEmpty()) {
            Log::warning("[ApprovalEmail] Nenhum destinatário encontrado para approval #{$approval->id}");
            $this->logActivity('approval_email_skipped', $approval, null, ['reason' => 'no_recipients']);
            $approval->update(['notification_status' => 'failed']);
            return;
        }

        $sentCount = 0;

        foreach ($recipients as $user) {
            if (!$force && !$this->shouldSendEmail($approval, $user, 'created')) {
                continue;
            }

            $record = $this->createRecord($approval, $user, 'created');

            try {
                $subject = "Nova aprovação pendente: {$approval->title}";
                $record->update(['subject' => $subject]);

                $panelUrl  = $this->createPanelUrl($approval, $user);
                $actionUrls = [
                    'approve'         => $this->createSignedActionUrl($approval, $user, 'approve'),
                    'request_changes' => $this->createSignedActionUrl($approval, $user, 'request_changes'),
                    'reject'          => $this->createSignedActionUrl($approval, $user, 'reject'),
                ];

                Mail::to($user->email)->send(
                    new ApprovalRequestCreatedMail($approval, $user, $panelUrl, $actionUrls)
                );

                $this->markSent($record);
                $sentCount++;

                $this->logActivity('approval_email_sent', $approval, $user, [
                    'notification_type' => 'created',
                    'email'             => $user->email,
                ]);
            } catch (Throwable $e) {
                $this->markFailed($record, $e->getMessage());
                $this->logActivity('approval_email_failed', $approval, $user, [
                    'notification_type' => 'created',
                    'email'             => $user->email,
                    'error'             => $this->sanitizeError($e->getMessage()),
                ]);
            }
        }

        if ($sentCount > 0) {
            $approval->update([
                'notified_at'         => now(),
                'notification_status' => 'sent',
            ]);
        } elseif ($approval->notification_status !== 'sent') {
            $approval->update(['notification_status' => 'failed']);
        }
    }

    public function sendReminderNotification(ApprovalRequest $approval): void
    {
        if (!$this->isEnabled() || !config('approval-notifications.reminder_enabled')) {
            return;
        }

        if (!$approval->isPending()) {
            return;
        }

        $recipients = $this->resolveRecipients($approval);

        if ($recipients->isEmpty()) {
            return;
        }

        $sentCount = 0;

        foreach ($recipients as $user) {
            if (!$this->shouldSendEmail($approval, $user, 'reminder')) {
                continue;
            }

            $record = $this->createRecord($approval, $user, 'reminder');

            try {
                $subject = "Lembrete: aprovação pendente para {$approval->title}";
                $record->update(['subject' => $subject]);

                $panelUrl   = $this->createPanelUrl($approval, $user);
                $actionUrls = [
                    'approve'         => $this->createSignedActionUrl($approval, $user, 'approve'),
                    'request_changes' => $this->createSignedActionUrl($approval, $user, 'request_changes'),
                    'reject'          => $this->createSignedActionUrl($approval, $user, 'reject'),
                ];

                Mail::to($user->email)->send(
                    new ApprovalRequestReminderMail($approval, $user, $panelUrl, $actionUrls)
                );

                $this->markSent($record);
                $sentCount++;

                $this->logActivity('approval_email_reminder_sent', $approval, $user, [
                    'email'          => $user->email,
                    'reminder_count' => ($approval->reminder_count ?? 0) + 1,
                ]);
            } catch (Throwable $e) {
                $this->markFailed($record, $e->getMessage());
            }
        }

        if ($sentCount > 0) {
            $approval->increment('reminder_count');
            $approval->update(['last_reminder_at' => now()]);
        }
    }

    public function sendStatusChangedNotification(ApprovalRequest $approval): void
    {
        // Status change notifications go through in-app AppNotification (already implemented).
        // Email status-changed is out of scope for this phase; kept as hook for future use.
    }

    // ── Recipient resolution ───────────────────────────────────────────────────

    public function resolveRecipients(ApprovalRequest $approval): Collection
    {
        if ($approval->client_id === null) {
            return $this->resolveAdminRecipients($approval);
        }

        return $this->resolveClientRecipients($approval);
    }

    public function resolveAdminRecipients(ApprovalRequest $approval): Collection
    {
        // Only the primary admin_geral (lowest id = first created = real agency owner)
        return User::where('role', 'admin_geral')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->orderBy('id')
            ->limit(1)
            ->get();
    }

    public function resolveClientRecipients(ApprovalRequest $approval): Collection
    {
        $clientId = $approval->client_id;

        // Primary: client admins for this client
        $recipients = User::where('client_id', $clientId)
            ->where('role', 'cliente_admin')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->get();

        // Also collaborators with explicit approvals.approve permission
        $collaborators = User::where('client_id', $clientId)
            ->where('role', 'colaborador')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->get()
            ->filter(fn(User $u) => $u->hasPermission('approvals.approve'));

        return $recipients->merge($collaborators)->unique('id')->values();
    }

    // ── Deduplication ──────────────────────────────────────────────────────────

    public function shouldSendEmail(ApprovalRequest $approval, User $user, string $type): bool
    {
        // Check if already sent successfully for this type
        return !ApprovalEmailNotification::where('approval_request_id', $approval->id)
            ->where('user_id', $user->id)
            ->where('notification_type', $type)
            ->where('status', 'sent')
            ->exists();
    }

    // ── URL generation ─────────────────────────────────────────────────────────

    public function createSignedActionUrl(ApprovalRequest $approval, User $user, string $action): string
    {
        $ttl = config('approval-notifications.signed_link_ttl_hours', 72);

        return URL::temporarySignedRoute(
            'approval.email.confirm',
            now()->addHours($ttl),
            [
                'approval' => $approval->id,
                'action'   => $action,
                'uid'      => $user->id,
            ]
        );
    }

    public function createPanelUrl(ApprovalRequest $approval, User $user): string
    {
        $isAdmin = in_array($user->role, ['admin_geral', 'agencia_admin', 'agencia_operador']);
        return $approval->getPanelUrl($isAdmin);
    }

    // ── Internal helpers ───────────────────────────────────────────────────────

    private function isEnabled(): bool
    {
        return (bool) config('approval-notifications.email_enabled', true);
    }

    private function createRecord(ApprovalRequest $approval, User $user, string $type): ApprovalEmailNotification
    {
        return ApprovalEmailNotification::create([
            'approval_request_id' => $approval->id,
            'user_id'             => $user->id,
            'email'               => $user->email,
            'notification_type'   => $type,
            'status'              => 'pending',
        ]);
    }

    private function markSent(ApprovalEmailNotification $record): void
    {
        $record->update(['status' => 'sent', 'sent_at' => now()]);
    }

    private function markFailed(ApprovalEmailNotification $record, string $errorMessage): void
    {
        $record->update([
            'status'        => 'failed',
            'failed_at'     => now(),
            'error_message' => $this->sanitizeError($errorMessage),
        ]);
    }

    private function sanitizeError(string $msg): string
    {
        return preg_replace('/(?:password|secret|key|token)=[^\s&]+/i', '***', $msg);
    }

    private function logActivity(string $action, ApprovalRequest $approval, ?User $user, array $extra = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'client_id'   => $approval->client_id,
                'action'      => $action,
                'module'      => 'approvals',
                'level'       => str_contains($action, 'fail') ? 'error' : 'info',
                'description' => "Email de aprovação: {$action} — #{$approval->id} {$approval->title}",
                'metadata'    => array_merge([
                    'approval_request_id' => $approval->id,
                    'email'               => $user?->email,
                ], $extra),
            ]);
        } catch (Throwable) {}
    }
}
