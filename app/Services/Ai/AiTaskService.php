<?php

namespace App\Services\Ai;

use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AiTaskService
{
    public function __construct(
        private AiProviderManager $provider,
        private AiLogService $logger,
    ) {}

    public function createManualTask(AiEmployee $employee, array $data): AiTask
    {
        $requiresApproval = $data['requires_approval']
            ?? $employee->requiresApproval();

        $task = AiTask::create([
            'ai_employee_id'   => $employee->id,
            'client_id'        => $data['client_id'] ?? $employee->default_client_id,
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'task_type'        => $data['task_type'] ?? 'general',
            'type'             => $data['task_type'] ?? 'general',
            'status'           => 'queued',
            'priority'         => $data['priority'] ?? 'normal',
            'requires_approval'  => $requiresApproval,
            'sensitive_action'   => $data['sensitive_action'] ?? false,
            'metadata'           => $data['metadata'] ?? null,
            'created_by'         => Auth::id(),
        ]);

        $this->logger->info($task, 'Tarefa criada manualmente.', ['created_by' => Auth::id()]);

        return $task;
    }

    public function runTask(AiTask $task): AiTask
    {
        if (!$task->canRun()) {
            throw new \RuntimeException("Tarefa {$task->id} não pode ser executada no status atual: {$task->status}");
        }

        $task->update(['status' => 'running']);
        $this->logger->info($task, 'Execução iniciada.');

        try {
            $output = $this->provider->generate($task);

            $nextStatus = $task->requires_approval ? 'waiting_approval' : 'completed';

            $task->update([
                'status'     => $nextStatus,
                'output'     => $output,
                'started_at' => $task->started_at ?? now(),
                'finished_at' => $nextStatus === 'completed' ? now() : null,
            ]);

            if ($nextStatus === 'waiting_approval') {
                $this->logger->warning($task, 'Output gerado. Aguardando aprovação do responsável.');
                $this->createApprovalRequestForTask($task);
            } else {
                $this->logger->success($task, 'Tarefa concluída com sucesso.');
            }
        } catch (Throwable $e) {
            $task->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
            ]);
            $this->logger->error($task, 'Erro na execução: ' . $e->getMessage());
            throw $e;
        }

        return $task->fresh();
    }

    public function approveTask(AiTask $task, ?User $approver = null): AiTask
    {
        if (!$task->canApprove()) {
            throw new \RuntimeException("Tarefa {$task->id} não pode ser aprovada no status atual: {$task->status}");
        }

        $task->update([
            'status'      => 'completed',
            'approved_by' => $approver?->id ?? Auth::id(),
            'approved_at' => now(),
            'finished_at' => now(),
        ]);

        $approverName = $approver?->name ?? Auth::user()?->name ?? 'Sistema';
        $this->logger->success($task, "Tarefa aprovada por {$approverName}.");

        return $task->fresh();
    }

    public function rejectTask(AiTask $task, string $reason = '', ?User $rejector = null): AiTask
    {
        if (!$task->canApprove()) {
            throw new \RuntimeException("Tarefa {$task->id} não pode ser rejeitada no status atual: {$task->status}");
        }

        $task->update([
            'status'        => 'rejected',
            'error_message' => $reason ?: 'Rejeitada pelo responsável.',
            'finished_at'   => now(),
        ]);

        $rejectorName = $rejector?->name ?? Auth::user()?->name ?? 'Sistema';
        $this->logger->warning($task, "Tarefa rejeitada por {$rejectorName}. Motivo: " . ($reason ?: '—'));

        return $task->fresh();
    }

    private function createApprovalRequestForTask(AiTask $task): void
    {
        $existingApproval = $task->approvalRequests()->where('status', 'pending')->exists();

        if ($existingApproval) {
            return;
        }

        $sensitiveLevel = $task->sensitive_action ? 'high' : 'medium';
        $approvalType   = $task->sensitive_action ? 'external_action' : 'ai_task';

        app(ApprovalService::class)->createApproval([
            'client_id'       => $task->client_id,
            'requested_by'    => $task->created_by,
            'ai_employee_id'  => $task->ai_employee_id,
            'approvable_type' => AiTask::class,
            'approvable_id'   => $task->id,
            'title'           => "Aprovação necessária: {$task->title}",
            'description'     => $task->output ?? $task->description,
            'approval_type'   => $approvalType,
            'sensitive_level' => $sensitiveLevel,
            'payload'         => [
                'task_type'         => $task->getEffectiveTaskType(),
                'output'            => $task->output,
                'requires_approval' => $task->requires_approval,
                'sensitive_action'  => $task->sensitive_action,
            ],
        ]);
    }

    public function cancelTask(AiTask $task): AiTask
    {
        if (!in_array($task->status, ['queued', 'waiting_approval', 'pending_approval', 'draft'])) {
            throw new \RuntimeException("Tarefa {$task->id} não pode ser cancelada no status atual: {$task->status}");
        }

        $task->update(['status' => 'canceled', 'finished_at' => now()]);
        $this->logger->warning($task, 'Tarefa cancelada.');

        return $task->fresh();
    }
}
