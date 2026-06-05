<?php

namespace App\Console\Commands;

use App\Models\AgencyBrandContext;
use App\Models\AgentTask;
use App\Models\AiMemory;
use App\Services\AI\Context\AgentTaskContextService;
use App\Services\AI\Context\BrandContextSnapshotService;
use App\Services\AI\Memory\AIMemoryService;
use Illuminate\Console\Command;

class RebuildCompactContextsCommand extends Command
{
    protected $signature = 'agents:rebuild-compact-contexts';
    protected $description = 'Rebuild compact contexts for brand contexts, agent tasks, and memories without regenerating content';

    public function handle(
        BrandContextSnapshotService $brandContextService,
        AgentTaskContextService     $taskContextService,
        AIMemoryService             $memoryService
    ): int {
        $this->info('[agents:rebuild-compact-contexts] Starting...');

        // 1. Brand Contexts
        $this->line("\n→ Processando Brand Contexts...");
        $brandContexts = AgencyBrandContext::where('active', true)->get();
        $updated = 0;
        foreach ($brandContexts as $ctx) {
            if ($brandContextService->needsCompactRefresh($ctx)) {
                $brandContextService->refreshCompactContext($ctx);
                $this->line("  Atualizado: [{$ctx->id}] {$ctx->brand_name} v" . ($ctx->fresh()->version));
                $updated++;
            }
        }
        $this->info("  Brand Contexts atualizados: {$updated}/" . $brandContexts->count());

        // 2. Agent Tasks
        $this->line("\n→ Processando Agent Tasks...");
        $tasks    = AgentTask::where('status', '!=', 'archived')->get();
        $updated  = 0;
        foreach ($tasks as $task) {
            if ($taskContextService->needsCompactRefresh($task)) {
                $taskContextService->refreshCompactTaskContext($task);
                $this->line("  Atualizado: [{$task->id}] {$task->title}");
                $updated++;
            }
        }
        $this->info("  Agent Tasks atualizados: {$updated}/" . $tasks->count());

        // 3. Memories without compact_content
        $this->line("\n→ Compactando memórias...");
        $memories = AiMemory::whereNull('compact_content')->where('active', true)->get();
        $updated  = 0;
        foreach ($memories as $mem) {
            $memoryService->compactMemoryIfNeeded($mem);
            $updated++;
        }
        $this->info("  Memórias compactadas: {$updated}/" . $memories->count());

        $this->newLine();
        $this->info('[agents:rebuild-compact-contexts] Done.');
        return 0;
    }
}
