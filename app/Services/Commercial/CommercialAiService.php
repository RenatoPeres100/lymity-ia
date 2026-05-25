<?php

namespace App\Services\Commercial;

use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\Budget;
use App\Models\Proposal;
use App\Models\User;
use App\Services\Ai\MockAiProvider;

class CommercialAiService
{
    public function __construct(
        protected ProposalService $proposalService,
        protected BudgetService   $budgetService,
        protected MockAiProvider  $mockAi,
    ) {}

    public function generateProposal(array $data, User $user): Proposal|AiTask
    {
        return $this->proposalService->generateWithAi($data, $user);
    }

    public function generateBudget(array $data, User $user): Budget|AiTask
    {
        $aiEmployee = AiEmployee::where('role_key', 'analyst')
            ->orWhere('name', 'like', '%Analista%')
            ->first()
            ?? AiEmployee::first();

        $task = AiTask::create([
            'client_id'      => $data['client_id'] ?? null,
            'ai_employee_id' => $aiEmployee?->id,
            'created_by'     => $user->id,
            'task_type'      => 'generate_budget',
            'title'          => $data['title'] ?? 'Orçamento gerado por IA',
            'description'    => $data['description'] ?? null,
            'status'         => 'running',
        ]);

        $output = $this->mockAi->generate($task);
        $parsed = json_decode($output, true) ?? [];

        $task->update(['status' => 'completed', 'output' => $output, 'finished_at' => now()]);

        return $this->budgetService->createBudget([
            'client_id'   => $data['client_id'] ?? null,
            'title'       => $parsed['title'] ?? ($data['title'] ?? 'Orçamento IA'),
            'description' => $parsed['description'] ?? null,
            'month'       => $data['month'] ?? now()->month,
            'year'        => $data['year'] ?? now()->year,
            'items'       => $parsed['items'] ?? [],
        ], $user);
    }

    public function improveProposal(Proposal $proposal, string $instruction, User $user): AiTask
    {
        $aiEmployee = AiEmployee::where('role_key', 'copywriter')->first() ?? AiEmployee::first();

        $task = AiTask::create([
            'client_id'      => $proposal->client_id,
            'ai_employee_id' => $aiEmployee?->id,
            'created_by'     => $user->id,
            'task_type'      => 'improve_proposal',
            'title'          => "Melhorar proposta: {$proposal->title}",
            'description'    => $instruction,
            'status'         => 'running',
        ]);

        $output = $this->mockAi->generate($task);
        $task->update(['status' => 'completed', 'output' => $output, 'finished_at' => now()]);

        return $task->fresh();
    }

    public function summarizeBudget(Budget $budget, User $user): AiTask
    {
        $aiEmployee = AiEmployee::where('role_key', 'analyst')->first() ?? AiEmployee::first();

        $task = AiTask::create([
            'client_id'      => $budget->client_id,
            'ai_employee_id' => $aiEmployee?->id,
            'created_by'     => $user->id,
            'task_type'      => 'summarize_budget',
            'title'          => "Resumo orçamento: {$budget->title}",
            'description'    => "Total: R$ " . number_format($budget->total_amount, 2, ',', '.'),
            'status'         => 'running',
        ]);

        $output = $this->mockAi->generate($task);
        $task->update(['status' => 'completed', 'output' => $output, 'finished_at' => now()]);

        return $task->fresh();
    }
}
