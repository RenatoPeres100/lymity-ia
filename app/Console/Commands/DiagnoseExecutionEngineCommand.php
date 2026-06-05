<?php

namespace App\Console\Commands;

use App\Models\AgentTask;
use App\Models\AgentTaskRun;
use App\Models\AgencyBrandContext;
use App\Services\AI\AIProviderManager;
use App\Services\AI\AICostGuardService;
use App\Services\AI\Context\BrandContextSnapshotService;
use Illuminate\Console\Command;

class DiagnoseExecutionEngineCommand extends Command
{
    protected $signature = 'agents:diagnose-execution-engine';
    protected $description = 'Diagnose the full AI execution engine — tasks, brand context, errors, flags';

    public function handle(
        AIProviderManager       $providerManager,
        AICostGuardService      $costGuard,
        BrandContextSnapshotService $brandCtxSvc
    ): int {
        $this->info('=== DIAGNÓSTICO DO MOTOR DE EXECUÇÃO IA ===');
        $this->newLine();

        // 1. Feature flags
        $this->info('── Feature Flags ─────────────────────────────');
        $flags = [
            'ai_task_engine_real', 'agent_tasks_menu', 'ai_memory_menu', 'ai_usage_menu',
            'legacy_ai_tasks_menu', 'legacy_ai_schedules_menu', 'legacy_agent_routines_menu',
            'auto_publish_without_approval', 'mock_content_generation',
        ];
        foreach ($flags as $flag) {
            $val = config("features.{$flag}");
            $this->line("  " . ($val ? '<fg=green>✓</>' : '<fg=gray>○</>') . " {$flag}: " . ($val ? 'true' : 'false'));
        }

        // 2. Provider
        $this->newLine();
        $this->info('── Provider de IA ────────────────────────────');
        $geminiOk = $providerManager->isGeminiConfigured();
        $this->line("  " . ($geminiOk ? '<fg=green>✓</>' : '<fg=red>✗</>') . " Gemini: " . ($geminiOk ? 'Configurado' : 'AUSENTE — GEMINI_API_KEY ou AI_PROVIDER=google'));

        // 3. Limits
        $daily   = $costGuard->getDailyUsage();
        $monthly = $costGuard->getMonthlyUsage();
        $this->newLine();
        $this->info('── Limites de Custo ──────────────────────────');
        $this->line("  Diário:  {$daily['count']}/{$daily['limit']} requests " . ($daily['exceeded'] ? '<fg=red>(EXCEDIDO)</>' : ''));
        $this->line("  Mensal:  \${$monthly['estimated_cost_usd']}/\${$monthly['budget_usd']} " . ($monthly['exceeded'] ? '<fg=red>(EXCEDIDO)</>' : ''));

        // 4. AgentTasks
        $this->newLine();
        $this->info('── AgentTasks ────────────────────────────────');
        $total      = AgentTask::count();
        $active     = AgentTask::where('status', 'active')->count();
        $paused     = AgentTask::where('status', 'paused')->count();
        $noEmployee = AgentTask::whereNull('ai_employee_id')->count();
        $noCompany  = AgentTask::whereNull('company_id')->count();

        $this->line("  Total: {$total} | Ativas: {$active} | Pausadas: {$paused}");
        if ($noEmployee > 0) $this->line("  <fg=red>✗ {$noEmployee} tarefa(s) sem funcionário IA vinculado</>");
        if ($noCompany > 0)  $this->line("  <fg=red>✗ {$noCompany} tarefa(s) sem company_id</>");

        // 5. Brand Contexts
        $this->newLine();
        $this->info('── Brand Contexts ────────────────────────────');
        $brandContexts = AgencyBrandContext::where('active', true)->get();
        $this->line("  Ativos: " . $brandContexts->count());

        // Check tasks without a brand context
        $tasksWithoutBrand = 0;
        foreach (AgentTask::where('status', 'active')->get() as $task) {
            $bc = $brandCtxSvc->getActiveBrandContextForScope($task->company_id, $task->client_id);
            if (!$bc) $tasksWithoutBrand++;
        }
        if ($tasksWithoutBrand > 0) {
            $this->line("  <fg=red>✗ {$tasksWithoutBrand} tarefa(s) ativa(s) sem Brand Context acessível</>");
        } else {
            $this->line("  <fg=green>✓ Todas as tarefas ativas têm Brand Context</>");
        }

        // 6. Recent runs
        $this->newLine();
        $this->info('── Últimas Execuções ─────────────────────────');
        $runs = AgentTaskRun::latest()->limit(10)->with('agentTask')->get();
        foreach ($runs as $run) {
            $color = match ($run->status) {
                'waiting_approval', 'approved', 'published' => 'green',
                'failed', 'canceled'                        => 'red',
                default                                     => 'yellow',
            };
            $this->line("  <fg={$color}>#{$run->id}</> [{$run->status}] {$run->agentTask?->title} — " . $run->created_at->format('d/m H:i'));
            if ($run->error_message) {
                $this->line("     <fg=red>Erro: " . \Str::limit($run->error_message, 120) . "</>");
            }
        }

        // 7. JSON failures
        $this->newLine();
        $jsonFails = AgentTaskRun::where('status', 'failed')
            ->where('error_message', 'like', '%JSON%')
            ->orWhere('error_message', 'like', '%json%')
            ->orWhere('error_message', 'like', '%control char%')
            ->count();
        if ($jsonFails > 0) {
            $this->warn("  ⚠ {$jsonFails} falha(s) por JSON inválido");
        } else {
            $this->line("  <fg=green>✓ Sem falhas por JSON inválido recentes</>");
        }

        // 8. Recommendations
        $this->newLine();
        $this->info('── Recomendações ─────────────────────────────');
        if (!$geminiOk)         $this->warn("  • Configure GEMINI_API_KEY e AI_PROVIDER=google no .env");
        if ($tasksWithoutBrand) $this->warn("  • Configure Brand Context em /admin/agency/brand-context");
        if ($noEmployee > 0)    $this->warn("  • Vincule um funcionário IA às tarefas sem employee");
        if ($noCompany > 0)     $this->warn("  • Corrija company_id nulo nas tarefas (agents:repair-operational-consistency --fix)");
        if ($daily['exceeded']) $this->warn("  • Limite diário atingido — aguarde ou aumente AI_DAILY_REQUEST_LIMIT");

        $this->newLine();
        $this->info('=== FIM DO DIAGNÓSTICO ===');
        return 0;
    }
}
