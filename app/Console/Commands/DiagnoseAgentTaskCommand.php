<?php

namespace App\Console\Commands;

use App\Models\AgentTask;
use App\Services\AI\AICostGuardService;
use App\Services\AI\AIProviderManager;
use App\Services\AI\Context\BrandContextSnapshotService;
use App\Services\AI\Context\AgentTaskContextService;
use App\Services\AI\Memory\AIMemoryService;
use Illuminate\Console\Command;

class DiagnoseAgentTaskCommand extends Command
{
    protected $signature = 'agents:diagnose-task {task_id : The AgentTask ID to diagnose}';
    protected $description = 'Diagnose an agent task and check readiness for execution';

    public function handle(
        BrandContextSnapshotService $brandContextService,
        AgentTaskContextService     $taskContextService,
        AIProviderManager           $providerManager,
        AICostGuardService          $costGuard,
        AIMemoryService             $memoryService
    ): int {
        $taskId = $this->argument('task_id');
        $task   = AgentTask::with('aiEmployee')->find($taskId);

        if (!$task) {
            $this->error("AgentTask #{$taskId} not found.");
            return 1;
        }

        $this->info("=== DIAGNÓSTICO DA TAREFA #{$task->id} ===");
        $this->line("Título: {$task->title}");
        $this->line("Tipo: {$task->task_type_label}");
        $this->line("Status: {$task->status_label}");
        $this->line("Frequência: {$task->frequency_label}");
        $this->newLine();

        // 1. Task active?
        $taskOk = $task->isActive();
        $this->checkLine('Tarefa ativa', $taskOk, $taskOk ? 'OK' : "Status: {$task->status}");

        // 2. Employee active?
        $employee    = $task->aiEmployee;
        $employeeOk  = $employee && $employee->active;
        $this->checkLine('Funcionário IA ativo', $employeeOk,
            $employeeOk ? ($employee->name ?? 'OK') : 'Nenhum funcionário ativo vinculado');

        // 3. Brand Context
        $brandCtx   = $brandContextService->getActiveBrandContextForScope($task->company_id, $task->client_id);
        $brandOk    = $brandCtx && $brandCtx->isComplete();
        $this->checkLine('Brand Context ativo', $brandOk,
            $brandOk ? "v{$brandCtx->version}: {$brandCtx->brand_name}" : 'Brand Context não encontrado ou incompleto');

        // 4. Compact context
        if ($brandCtx) {
            $needsRefresh = $brandContextService->needsCompactRefresh($brandCtx);
            $this->checkLine('Contexto compacto da marca', !$needsRefresh,
                $needsRefresh ? 'Precisa de atualização' : 'OK (cache válido)');
        }

        // 5. Task compact context
        $taskCompactOk = !$taskContextService->needsCompactRefresh($task);
        $this->checkLine('Contexto compacto da tarefa', $taskCompactOk,
            $taskCompactOk ? 'OK' : 'Precisa de atualização');

        // 6. AI provider
        $providerOk = $providerManager->isGeminiConfigured();
        $this->checkLine('Provider de IA (Gemini)', $providerOk,
            $providerOk ? 'Configurado' : 'GEMINI_API_KEY ausente ou AI_PROVIDER != google');

        // 7. Image provider (if required)
        if ($task->requiresImage()) {
            $imageOk = config('ai-images.enabled', false);
            $this->checkLine('Provider de imagem', $imageOk,
                $imageOk ? 'IMAGE_GENERATION_ENABLED=true' : 'IMAGE_GENERATION_ENABLED=false (imagens não serão geradas)');
        }

        // 8. Daily cost/limit
        $daily = $costGuard->getDailyUsage();
        $this->checkLine('Limite diário', !$daily['exceeded'],
            "Uso: {$daily['count']}/{$daily['limit']} requests");

        $monthly = $costGuard->getMonthlyUsage();
        $this->checkLine('Orçamento mensal', !$monthly['exceeded'],
            "Custo: \${$monthly['estimated_cost_usd']}/\${$monthly['budget_usd']}");

        // 9. Next run
        $this->newLine();
        $this->line("Próxima execução: " . ($task->next_run_at?->format('d/m/Y H:i') ?? 'não calculado'));
        $this->line("Último sucesso: " . ($task->last_success_at?->format('d/m/Y H:i') ?? 'nunca'));
        $this->line("Último erro: " . ($task->last_failure_at?->format('d/m/Y H:i') ?? 'nenhum'));
        if ($task->failure_count > 0) {
            $this->warn("Falhas consecutivas: {$task->failure_count}");
        }

        // 10. Memory
        if ($employee) {
            $memories = $memoryService->getRelevantMemoriesForTask($task, $employee, 5);
            $this->newLine();
            $this->line("Memórias disponíveis: {$memories->count()} (máx 5 por execução)");
        }

        // 11. Scope
        $this->newLine();
        $this->line("Escopo: company_id={$task->company_id}, client_id={$task->client_id}");

        // 12. Research
        if ($task->requires_external_research) {
            $researchEnabled = config('external-research.enabled', false);
            $this->checkLine('Pesquisa externa', $researchEnabled,
                $researchEnabled ? 'Habilitada' : "EXTERNAL_RESEARCH_ENABLED=false → comportamento: {$task->if_research_unavailable}");
        }

        $this->newLine();
        $allOk = $taskOk && $employeeOk && $brandOk && $providerOk;
        if ($allOk) {
            $this->info('✓ Tarefa pronta para execução.');
        } else {
            $this->warn('✗ Tarefa não está pronta. Corrija os itens marcados com ERRO acima.');
        }

        return $allOk ? 0 : 1;
    }

    private function checkLine(string $label, bool $ok, string $detail = ''): void
    {
        $icon   = $ok ? '<fg=green>✓</>' : '<fg=red>✗</>';
        $status = $ok ? '<fg=green>OK</>' : '<fg=red>ERRO</>';
        $this->line("  {$icon} {$label}: {$status}" . ($detail ? " — {$detail}" : ''));
    }
}
