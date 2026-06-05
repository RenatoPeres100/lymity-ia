<?php

namespace App\Console\Commands;

use App\Models\AgentTask;
use App\Models\AgentTaskRun;
use App\Models\AgencyBrandContext;
use App\Models\BlogPost;
use App\Models\SocialPost;
use App\Services\AI\Context\AgentTaskContextService;
use App\Services\AI\Context\BrandContextSnapshotService;
use Illuminate\Console\Command;

class RepairOperationalConsistencyCommand extends Command
{
    protected $signature = 'agents:repair-operational-consistency
                            {--dry-run : Show what would be fixed without making changes (default)}
                            {--fix : Actually apply the fixes}';

    protected $description = 'Find and fix operational consistency issues: missing context, null company_id, etc.';

    public function handle(
        AgentTaskContextService     $taskCtxSvc,
        BrandContextSnapshotService $brandCtxSvc
    ): int {
        $isDry = !$this->option('fix');
        $mode  = $isDry ? '[DRY-RUN]' : '[FIX]';

        $this->info("=== REPAIR OPERATIONAL CONSISTENCY {$mode} ===");
        $this->newLine();

        $report = [];
        $fixed  = 0;

        // 1. AgentTasks without compact_task_context
        $tasks = AgentTask::whereNull('compact_task_context')->orWhere('compact_task_context', '')->get();
        $this->info("── AgentTasks sem compact_task_context: {$tasks->count()}");
        foreach ($tasks as $task) {
            $this->line("  #{$task->id} {$task->title}");
            if (!$isDry) {
                $taskCtxSvc->refreshCompactTaskContext($task);
                $fixed++;
                $this->line("    → Regenerado");
            }
            $report[] = "AgentTask #{$task->id}: compact_task_context ausente";
        }

        // 2. BrandContexts without compact_context
        $brands = AgencyBrandContext::whereNull('compact_context')->orWhere('compact_context', '')->get();
        $this->newLine();
        $this->info("── Brand Contexts sem compact_context: {$brands->count()}");
        foreach ($brands as $brand) {
            $this->line("  #{$brand->id} {$brand->brand_name}");
            if (!$isDry) {
                $brandCtxSvc->refreshCompactContext($brand);
                $fixed++;
                $this->line("    → Regenerado");
            }
            $report[] = "BrandContext #{$brand->id}: compact_context ausente";
        }

        // 3. AgentTasks without company_id
        $noCompany = AgentTask::whereNull('company_id')->get();
        $this->newLine();
        $this->info("── AgentTasks sem company_id: {$noCompany->count()}");
        foreach ($noCompany as $task) {
            $this->line("  #{$task->id} {$task->title}");
            if (!$isDry) {
                $firstCompany = \App\Models\Company::first();
                if ($firstCompany) {
                    $task->update(['company_id' => $firstCompany->id]);
                    $fixed++;
                    $this->line("    → company_id={$firstCompany->id} ({$firstCompany->name})");
                }
            }
            $report[] = "AgentTask #{$task->id}: company_id nulo";
        }

        // 4. AgentTaskRuns failed with JSON errors
        $jsonFails = AgentTaskRun::where('status', 'failed')
            ->where(function ($q) {
                $q->where('error_message', 'like', '%JSON%')
                  ->orWhere('error_message', 'like', '%control char%')
                  ->orWhere('error_message', 'like', '%json%');
            })
            ->with('agentTask')
            ->latest()
            ->limit(20)
            ->get();
        $this->newLine();
        $this->info("── Runs falhadas por JSON inválido: {$jsonFails->count()}");
        foreach ($jsonFails as $run) {
            $this->line("  Run #{$run->id} Task: {$run->agentTask?->title} — " . \Str::limit($run->error_message, 100));
            $report[] = "AgentTaskRun #{$run->id}: falha JSON";
        }

        // 5. BlogPosts/SocialPosts without agent_task_id (AI-generated ones)
        $blogNoTask = BlogPost::whereNotNull('ai_employee_id')->whereNull('agent_task_id')->count();
        $socialNoTask = SocialPost::whereNotNull('ai_employee_id')->whereNull('agent_task_id')->count();
        $this->newLine();
        $this->info("── Conteúdo gerado por IA sem agent_task_id: blog={$blogNoTask} social={$socialNoTask}");
        if ($blogNoTask > 0 || $socialNoTask > 0) {
            $report[] = "Conteúdo sem task_id: {$blogNoTask} blog + {$socialNoTask} social (legado — não corrigível automaticamente)";
        }

        // Summary
        $this->newLine();
        $this->info('── Relatório ──────────────────────────────────');
        foreach ($report as $item) {
            $this->line("  • {$item}");
        }
        $this->newLine();
        if ($isDry) {
            $this->warn("Modo DRY-RUN. Nenhuma alteração foi feita. Use --fix para aplicar.");
        } else {
            $this->info("{$fixed} item(s) corrigido(s).");
        }

        return 0;
    }
}
