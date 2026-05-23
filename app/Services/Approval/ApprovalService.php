<?php

namespace App\Services\Approval;

use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApprovalService
{
    public function createApproval(array $data): ApprovalRequest
    {
        $approval = ApprovalRequest::create([
            'client_id'       => $data['client_id'] ?? null,
            'requested_by'    => $data['requested_by'] ?? Auth::id(),
            'ai_employee_id'  => $data['ai_employee_id'] ?? null,
            'approvable_type' => $data['approvable_type'] ?? null,
            'approvable_id'   => $data['approvable_id'] ?? null,
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'approval_type'   => $data['approval_type'] ?? 'other',
            'status'          => 'pending',
            'sensitive_level' => $data['sensitive_level'] ?? 'low',
            'payload'         => $data['payload'] ?? null,
            'due_at'          => $data['due_at'] ?? null,
        ]);

        $this->logApprovalAction($approval, Auth::user(), 'created', 'Solicitação criada.');
        $this->notifyRelevantUsers($approval, 'created');

        return $approval;
    }

    public function approve(ApprovalRequest $approvalRequest, User $user, ?string $notes = null): ApprovalRequest
    {
        $approvalRequest->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->logApprovalAction($approvalRequest, $user, 'approved', $notes);
        $this->logActivity($approvalRequest, $user, 'approved');
        $this->syncApprovableStatus($approvalRequest, 'approved', $user);
        $this->notifyRelevantUsers($approvalRequest, 'approved');

        return $approvalRequest->fresh();
    }

    public function reject(ApprovalRequest $approvalRequest, User $user, ?string $notes = null): ApprovalRequest
    {
        $approvalRequest->update([
            'status'      => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
        ]);

        $this->logApprovalAction($approvalRequest, $user, 'rejected', $notes);
        $this->logActivity($approvalRequest, $user, 'rejected');
        $this->syncApprovableStatus($approvalRequest, 'rejected', $user);
        $this->notifyRelevantUsers($approvalRequest, 'rejected');

        return $approvalRequest->fresh();
    }

    public function requestChanges(ApprovalRequest $approvalRequest, User $user, ?string $notes = null): ApprovalRequest
    {
        $approvalRequest->update(['status' => 'changes_requested']);

        $this->logApprovalAction($approvalRequest, $user, 'requested_changes', $notes);
        $this->logActivity($approvalRequest, $user, 'requested_changes');
        $this->syncApprovableStatus($approvalRequest, 'changes_requested', $user);
        $this->notifyRelevantUsers($approvalRequest, 'changes_requested');

        return $approvalRequest->fresh();
    }

    public function cancel(ApprovalRequest $approvalRequest, User $user, ?string $notes = null): ApprovalRequest
    {
        $approvalRequest->update(['status' => 'canceled']);

        $this->logApprovalAction($approvalRequest, $user, 'canceled', $notes);
        $this->logActivity($approvalRequest, $user, 'canceled');
        $this->notifyRelevantUsers($approvalRequest, 'canceled');

        return $approvalRequest->fresh();
    }

    public function canUserApprove(User $user, ApprovalRequest $approvalRequest): bool
    {
        if ($user->role === 'admin_geral') {
            return true;
        }

        if ($user->role === 'agencia_admin') {
            return true;
        }

        if ($user->role === 'agencia_operador') {
            return $user->hasPermission('approvals.approve');
        }

        if ($user->role === 'cliente_admin') {
            return $approvalRequest->client_id && $approvalRequest->client_id === $user->client_id;
        }

        if ($user->role === 'cliente_colaborador') {
            return $approvalRequest->client_id === $user->client_id
                && $user->hasPermission('approvals.approve');
        }

        return false;
    }

    public function logApprovalAction(ApprovalRequest $approvalRequest, ?User $user, string $action, ?string $notes = null): ApprovalAction
    {
        return ApprovalAction::create([
            'approval_request_id' => $approvalRequest->id,
            'user_id'             => $user?->id,
            'action'              => $action,
            'notes'               => $notes,
            'created_at'          => now(),
        ]);
    }

    public function syncApprovableStatus(ApprovalRequest $approvalRequest, string $newStatus, ?User $user = null): void
    {
        if (!$approvalRequest->approvable_type || !$approvalRequest->approvable_id) {
            return;
        }

        $approvable = $approvalRequest->approvable;

        if (!$approvable) {
            return;
        }

        $modelClass = get_class($approvable);

        if ($modelClass === \App\Models\AiTask::class) {
            $this->syncAiTaskStatus($approvable, $newStatus, $user);
            return;
        }

        if (in_array($modelClass, [\App\Models\BlogPost::class, \App\Models\ClientWebsitePage::class])) {
            $this->syncContentStatus($approvable, $newStatus);
            return;
        }

        if ($modelClass === \App\Models\SocialPost::class) {
            $this->syncSocialPostStatus($approvable, $newStatus, $user);
            return;
        }
    }

    private function syncAiTaskStatus(\App\Models\AiTask $task, string $newStatus, ?User $user): void
    {
        $taskStatus = match ($newStatus) {
            'approved'          => 'approved',
            'rejected'          => 'rejected',
            'changes_requested' => 'waiting_approval',
            default             => null,
        };

        if (!$taskStatus) {
            return;
        }

        $updateData = ['status' => $taskStatus];

        if ($newStatus === 'approved') {
            $updateData['approved_by'] = $user?->id;
            $updateData['approved_at'] = now();
            $updateData['finished_at'] = now();
        } elseif ($newStatus === 'rejected') {
            $updateData['finished_at'] = now();
        }

        $task->update($updateData);

        $logLevel = match ($newStatus) {
            'approved' => 'success',
            'rejected' => 'warning',
            default    => 'info',
        };

        app(\App\Services\Ai\AiLogService::class)->$logLevel(
            $task,
            "Status atualizado para {$taskStatus} via ApprovalRequest #{$task->approvalRequests()->latest()->first()?->id}."
        );
    }

    private function syncSocialPostStatus(\App\Models\SocialPost $post, string $newStatus, ?User $user): void
    {
        $targetStatus = match ($newStatus) {
            'approved'          => 'approved',
            'rejected'          => 'rejected',
            'changes_requested' => 'draft',
            default             => null,
        };

        if (!$targetStatus) {
            return;
        }

        $updateData = ['status' => $targetStatus];

        if ($newStatus === 'approved') {
            $updateData['approved_by'] = $user?->id;
            $updateData['approved_at'] = now();
        }

        $post->update($updateData);

        $this->logActivity(
            new \App\Models\ApprovalRequest(['client_id' => $post->client_id]),
            $user ?? new User(),
            'social_post_' . $newStatus
        );
    }

    private function syncContentStatus($model, string $newStatus): void
    {
        $targetStatus = match ($newStatus) {
            'approved'          => 'approved',
            'rejected'          => 'draft',
            'changes_requested' => 'draft',
            default             => null,
        };

        if ($targetStatus) {
            $model->update(['status' => $targetStatus]);
        }
    }

    public function notifyRelevantUsers(ApprovalRequest $approvalRequest, string $event): void
    {
        $actionUrl = route('admin.approvals.show', $approvalRequest->id);

        $titles = [
            'created'           => "Nova aprovação: {$approvalRequest->title}",
            'approved'          => "Aprovado: {$approvalRequest->title}",
            'rejected'          => "Rejeitado: {$approvalRequest->title}",
            'changes_requested' => "Alterações solicitadas: {$approvalRequest->title}",
            'canceled'          => "Cancelado: {$approvalRequest->title}",
        ];

        $title = $titles[$event] ?? "Aprovação atualizada: {$approvalRequest->title}";

        $admins = User::whereIn('role', ['admin_geral', 'agencia_admin'])->where('status', 'active')->get();

        foreach ($admins as $admin) {
            AppNotification::notify(
                $admin->id,
                $title,
                $approvalRequest->description,
                'approval_' . $event,
                $actionUrl,
                $approvalRequest->client_id
            );
        }

        if ($approvalRequest->client_id && in_array($event, ['approved', 'rejected', 'changes_requested'])) {
            $clientAdmins = User::where('client_id', $approvalRequest->client_id)
                ->where('role', 'cliente_admin')
                ->where('status', 'active')
                ->get();

            $clientActionUrl = route('client.approvals.show', $approvalRequest->id);

            foreach ($clientAdmins as $clientAdmin) {
                AppNotification::notify(
                    $clientAdmin->id,
                    $title,
                    $approvalRequest->description,
                    'approval_' . $event,
                    $clientActionUrl,
                    $approvalRequest->client_id
                );
            }
        }

        if ($approvalRequest->requested_by && in_array($event, ['approved', 'rejected', 'changes_requested'])) {
            $requester = $approvalRequest->requester;
            if ($requester && !$admins->contains('id', $requester->id)) {
                AppNotification::notify(
                    $requester->id,
                    $title,
                    $approvalRequest->description,
                    'approval_' . $event,
                    $actionUrl,
                    $approvalRequest->client_id
                );
            }
        }
    }

    private function logActivity(ApprovalRequest $approvalRequest, User $user, string $action): void
    {
        if (!class_exists(ActivityLog::class)) {
            return;
        }

        ActivityLog::create([
            'user_id'    => $user->id,
            'client_id'  => $approvalRequest->client_id,
            'action'     => $action,
            'module'     => 'approvals',
            'description' => "ApprovalRequest #{$approvalRequest->id} — {$approvalRequest->title}",
            'metadata'   => [
                'approval_request_id' => $approvalRequest->id,
                'approvable_type'     => $approvalRequest->approvable_type,
                'approvable_id'       => $approvalRequest->approvable_id,
            ],
        ]);
    }
}
