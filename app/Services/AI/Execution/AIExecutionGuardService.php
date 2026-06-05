<?php

namespace App\Services\AI\Execution;

use App\Models\AgencyBrandContext;
use App\Models\AgentTask;
use App\Models\AiEmployee;
use App\Services\AI\Context\BrandContextSnapshotService;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIExecutionGuardService
{
    public function __construct(
        private BrandContextSnapshotService $brandContextService,
    ) {}

    /**
     * Full guard: validates task, employee and brand context before any generation.
     */
    public function assertCanGenerateFromTask(
        ?AgentTask $task,
        ?AiEmployee $employee,
        bool $isManual = false
    ): AgencyBrandContext {
        $this->assertTaskIsValid($task, $isManual);
        $this->assertEmployeeIsValid($employee);

        $brandContext = $this->brandContextService->getActiveBrandContextForScope(
            $task->company_id,
            $task->client_id
        );

        $this->assertBrandContextIsValid($brandContext, $task);

        return $brandContext;
    }

    public function assertTaskIsValid(?AgentTask $task, bool $isManual = false): void
    {
        if (!$task) {
            $this->block(
                'agent_task.missing',
                "Toda geração de IA precisa estar vinculada a uma tarefa operacional."
            );
        }

        if ($task->status === 'archived' || $task->status === 'disabled') {
            $this->block(
                'agent_task.invalid_status',
                "Tarefa operacional '{$task->title}' está {$task->status} e não pode gerar conteúdo."
            );
        }

        if (!$isManual && $task->status !== 'active') {
            $this->block(
                'agent_task.not_active',
                "Execução automática requer tarefa com status 'active'. Status atual: {$task->status}."
            );
        }
    }

    public function assertEmployeeIsValid(?AiEmployee $employee): void
    {
        if (!$employee) {
            $this->block(
                'ai_employee.missing',
                "Funcionário IA não encontrado. Vincule um funcionário ativo à tarefa."
            );
        }

        if ($employee->status !== 'active') {
            $this->block(
                'ai_employee.not_active',
                "Funcionário IA '{$employee->name}' não está ativo (status: {$employee->status})."
            );
        }
    }

    public function assertBrandContextIsValid(?AgencyBrandContext $brandContext, AgentTask $task): void
    {
        if (!$brandContext) {
            Log::warning('[AIExecutionGuard] Brand Context missing', [
                'task_id'    => $task->id,
                'company_id' => $task->company_id,
                'client_id'  => $task->client_id,
            ]);
            $this->block(
                'brand_context.missing',
                "Brand Context ativo é obrigatório para gerar conteúdo com IA. " .
                "Configure o Brand Context em /admin/agency/brand-context antes de executar."
            );
        }

        if (!$brandContext->active) {
            $this->block(
                'brand_context.inactive',
                "Brand Context '{$brandContext->brand_name}' está inativo. Ative-o antes de gerar."
            );
        }

        if (!$brandContext->isComplete()) {
            $this->block(
                'brand_context.incomplete',
                "Brand Context '{$brandContext->brand_name}' está incompleto. " .
                "Preencha: nome, posicionamento, tom de voz e público-alvo."
            );
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function block(string $reason, string $message): never
    {
        Log::warning("[AIExecutionGuard] Blocked: {$reason} — {$message}");
        throw new RuntimeException($message);
    }
}
