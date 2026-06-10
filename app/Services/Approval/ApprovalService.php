<?php

namespace App\Services\Approval;

use App\Jobs\SendApprovalEmailNotificationJob;
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

        // Dispatch email notification job (non-blocking — mail failure does not affect approval)
        try {
            SendApprovalEmailNotificationJob::dispatch($approval->id, 'created')
                ->onQueue('default');
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'client_id'   => $approval->client_id,
                'action'      => 'approval_email_queued',
                'module'      => 'approvals',
                'level'       => 'info',
                'description' => "E-mail de aprovação enfileirado — #{$approval->id}",
                'metadata'    => ['approval_request_id' => $approval->id],
            ]);
        } catch (\Throwable $e) {
            // Never let email failure break approval creation
        }

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

        if ($modelClass === \App\Models\GeneratedContentPackage::class) {
            $this->syncGeneratedPackageStatus($approvable, $newStatus, $user, $approvalRequest);
            return;
        }

        if ($modelClass === \App\Models\AiTask::class) {
            $this->syncAiTaskStatus($approvable, $newStatus, $user);
            return;
        }

        if (in_array($modelClass, [\App\Models\BlogPost::class, \App\Models\ClientWebsitePage::class])) {
            $this->syncContentStatus($approvable, $newStatus, $user);
            return;
        }

        if ($modelClass === \App\Models\SocialPost::class) {
            $this->syncSocialPostStatus($approvable, $newStatus, $user);
            return;
        }

        if ($modelClass === \App\Models\AdCampaign::class) {
            $this->syncAdCampaignStatus($approvable, $newStatus, $user);
            return;
        }

        if ($modelClass === \App\Models\CampaignBudgetChange::class) {
            $this->syncCampaignBudgetChangeStatus($approvable, $newStatus, $user);
            return;
        }

        if ($modelClass === \App\Models\Proposal::class) {
            $this->syncProposalStatus($approvable, $newStatus, $user);
            return;
        }

        if ($modelClass === \App\Models\Budget::class) {
            $this->syncBudgetStatus($approvable, $newStatus, $user);
            return;
        }
    }

    private function syncGeneratedPackageStatus(
        \App\Models\GeneratedContentPackage $package,
        string $newStatus,
        ?User $user,
        ApprovalRequest $approvalRequest
    ): void {
        $memoryService = app(\App\Services\AI\Memory\AIMemoryService::class);

        if ($newStatus === 'approved') {
            $package->update(['status' => 'approved']);

            // Sync the underlying entity
            if ($package->generated_entity_type && $package->generated_entity_id) {
                $entity = $package->generatedEntity;
                if ($entity) {
                    $entityClass = get_class($entity);
                    if (in_array($entityClass, [\App\Models\BlogPost::class, \App\Models\SocialPost::class])) {
                        $futureScheduled = $entity->scheduled_at && $entity->scheduled_at->isFuture();
                        $updateData = [
                            'status'      => $futureScheduled ? 'scheduled' : 'approved',
                            'approved_by' => $user?->id,
                            'approved_at' => now(),
                        ];

                        // Propagate image to BlogPost if not yet set
                        if ($entityClass === \App\Models\BlogPost::class && !$entity->featured_image) {
                            $visual = $package->visual_payload ?? [];
                            $imageUrl = $visual['featured_image_url'] ?? null;
                            if (!$imageUrl) {
                                $asset = $package->assets()
                                    ->where('asset_type', 'featured_image')
                                    ->where('status', 'generated')
                                    ->latest('id')
                                    ->first();
                                $imageUrl = $asset?->public_url;
                            }
                            if ($imageUrl) {
                                $updateData['featured_image'] = $imageUrl;
                                if (!$entity->featured_image_alt && isset($visual['image_alt'])) {
                                    $updateData['featured_image_alt'] = $visual['image_alt'];
                                }
                            }
                        }

                        $entity->update($updateData);
                    }
                }
            }

            // Create approved_pattern memory
            try {
                $memoryService->createApprovedPatternFromApproval(
                    $package->company_id,
                    $package->client_id,
                    $package->ai_employee_id,
                    $package->agent_task_id,
                    "Padrão aprovado: " . \Str::limit($package->title, 60),
                    "Conteúdo aprovado em " . now()->format('d/m/Y') . ". Tipo: {$package->package_type}. Título: {$package->title}.",
                    $approvalRequest->id
                );
                ActivityLog::create([
                    'action' => 'memory.approved_pattern_created',
                    'module' => 'ai_tasks',
                    'level'  => 'info',
                    'description' => "Padrão aprovado criado para pacote #{$package->id}",
                    'metadata' => ['package_id' => $package->id],
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[ApprovalService] Memory creation failed: " . $e->getMessage());
            }

            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => 'approval.package_approved',
                'module'      => 'approvals',
                'description' => "Pacote #{$package->id} aprovado: {$package->title}",
                'metadata'    => ['package_id' => $package->id, 'approval_request_id' => $approvalRequest->id],
            ]);
            return;
        }

        if ($newStatus === 'rejected') {
            $package->update(['status' => 'rejected']);

            if ($package->generated_entity_type && $package->generated_entity_id) {
                $entity = $package->generatedEntity;
                $entity?->update(['status' => 'draft']);
            }

            $notes = $approvalRequest->actions()->latest()->value('notes') ?? 'Sem feedback';
            try {
                $memoryService->createRejectedPatternFromRejection(
                    $package->company_id,
                    $package->client_id,
                    $package->ai_employee_id,
                    $package->agent_task_id,
                    "Padrão rejeitado: " . \Str::limit($package->title, 60),
                    "Rejeitado em " . now()->format('d/m/Y') . ". Tipo: {$package->package_type}. Feedback: {$notes}",
                    $approvalRequest->id
                );
                ActivityLog::create([
                    'action' => 'memory.rejected_pattern_created',
                    'module' => 'ai_tasks',
                    'level'  => 'info',
                    'description' => "Padrão rejeitado registrado para pacote #{$package->id}",
                    'metadata' => ['package_id' => $package->id],
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[ApprovalService] Memory creation failed: " . $e->getMessage());
            }

            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => 'approval.package_rejected',
                'module'      => 'approvals',
                'description' => "Pacote #{$package->id} rejeitado: {$package->title}",
                'metadata'    => ['package_id' => $package->id, 'approval_request_id' => $approvalRequest->id],
            ]);
            return;
        }

        if ($newStatus === 'changes_requested') {
            $package->update(['status' => 'draft']);

            $notes = $approvalRequest->actions()->latest()->value('notes') ?? 'Sem feedback';
            try {
                $memoryService->createFeedbackMemoryFromChangesRequest(
                    $package->company_id,
                    $package->client_id,
                    $package->ai_employee_id,
                    $package->agent_task_id,
                    "Ajuste solicitado: " . \Str::limit($package->title, 60),
                    "Ajuste solicitado em " . now()->format('d/m/Y') . ". Feedback: {$notes}",
                    $approvalRequest->id
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[ApprovalService] Memory creation failed: " . $e->getMessage());
            }

            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => 'approval.package_changes_requested',
                'module'      => 'approvals',
                'description' => "Ajuste solicitado para pacote #{$package->id}: {$package->title}",
                'metadata'    => ['package_id' => $package->id, 'approval_request_id' => $approvalRequest->id],
            ]);
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
        if ($newStatus === 'approved') {
            $updateData = [
                'approved_by' => $user?->id,
                'approved_at' => now(),
            ];

            $futureScheduled = $post->scheduled_at && $post->scheduled_at->isFuture();
            $imageValid      = $post->image_validation_status === 'valid' && !empty($post->public_image_url);

            if ($futureScheduled && $imageValid) {
                $updateData['status'] = 'scheduled';

                ActivityLog::create([
                    'user_id'     => $user?->id,
                    'client_id'   => $post->client_id,
                    'action'      => 'social_post_scheduled_after_approval',
                    'module'      => 'social',
                    'description' => "Post social #{$post->id} agendado automaticamente após aprovação para " . $post->scheduled_at->format('d/m/Y H:i'),
                    'metadata'    => ['social_post_id' => $post->id, 'scheduled_at' => $post->scheduled_at->toIso8601String()],
                ]);
            } elseif ($futureScheduled && !$imageValid) {
                $updateData['status'] = 'approved';

                ActivityLog::create([
                    'user_id'     => $user?->id,
                    'client_id'   => $post->client_id,
                    'action'      => 'social_post_approved_but_image_invalid',
                    'module'      => 'social',
                    'description' => "Post social #{$post->id} aprovado mas não agendado — imagem inválida ou ausente.",
                    'metadata'    => [
                        'social_post_id'          => $post->id,
                        'image_validation_status' => $post->image_validation_status,
                        'public_image_url'        => !empty($post->public_image_url),
                    ],
                ]);
            } else {
                $updateData['status'] = 'approved';
            }

            $post->update($updateData);

            $this->logActivity(
                new \App\Models\ApprovalRequest(['client_id' => $post->client_id]),
                $user ?? new User(),
                'social_post_approved'
            );

            return;
        }

        $targetStatus = match ($newStatus) {
            'rejected'          => 'rejected',
            'changes_requested' => 'draft',
            default             => null,
        };

        if (!$targetStatus) {
            return;
        }

        $post->update(['status' => $targetStatus]);

        $this->logActivity(
            new \App\Models\ApprovalRequest(['client_id' => $post->client_id]),
            $user ?? new User(),
            'social_post_' . $newStatus
        );
    }

    private function syncAdCampaignStatus(\App\Models\AdCampaign $campaign, string $newStatus, ?User $user): void
    {
        $updates = match ($newStatus) {
            'approved'          => ['status' => 'approved', 'approved_by' => $user?->id, 'approved_at' => now()],
            'rejected'          => ['status' => 'rejected'],
            'changes_requested' => ['status' => 'draft'],
            default             => [],
        };

        if (!empty($updates)) {
            $campaign->update($updates);
        }
    }

    private function syncCampaignBudgetChangeStatus(\App\Models\CampaignBudgetChange $change, string $newStatus, ?User $user): void
    {
        $updates = match ($newStatus) {
            'approved'          => ['status' => 'approved', 'approved_by' => $user?->id, 'approved_at' => now()],
            'rejected'          => ['status' => 'rejected'],
            'changes_requested' => ['status' => 'pending_approval'],
            default             => [],
        };

        if (!empty($updates)) {
            $change->update($updates);
        }
    }

    private function syncProposalStatus(\App\Models\Proposal $proposal, string $newStatus, ?User $user): void
    {
        $updates = match ($newStatus) {
            'approved'          => ['status' => 'approved', 'approved_by' => $user?->id, 'approved_at' => now()],
            'rejected'          => ['status' => 'rejected'],
            'changes_requested' => ['status' => 'draft'],
            default             => [],
        };

        if (!empty($updates)) {
            $proposal->update($updates);
        }
    }

    private function syncBudgetStatus(\App\Models\Budget $budget, string $newStatus, ?User $user): void
    {
        $updates = match ($newStatus) {
            'approved'          => ['status' => 'approved'],
            'rejected'          => ['status' => 'rejected'],
            'changes_requested' => ['status' => 'draft'],
            default             => [],
        };

        if (!empty($updates)) {
            $budget->update($updates);
        }
    }

    private function syncContentStatus($model, string $newStatus, ?User $user = null): void
    {
        if ($newStatus === 'approved') {
            $futureScheduled = $model->scheduled_at && $model->scheduled_at->isFuture();
            $targetStatus    = $futureScheduled ? 'scheduled' : 'approved';

            $model->update([
                'status'      => $targetStatus,
                'approved_by' => $user?->id,
                'approved_at' => now(),
            ]);

            if ($futureScheduled) {
                ActivityLog::create([
                    'user_id'     => $user?->id,
                    'action'      => 'blog_post_scheduled_after_approval',
                    'module'      => 'blog',
                    'description' => "Post #{$model->id} agendado automaticamente após aprovação para " . $model->scheduled_at->format('d/m/Y H:i'),
                    'metadata'    => ['blog_post_id' => $model->id, 'scheduled_at' => $model->scheduled_at->toIso8601String()],
                ]);
            }

            return;
        }

        $targetStatus = match ($newStatus) {
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
