<?php

namespace App\Services\Commercial;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\User;
use App\Services\Approval\ApprovalService;

class BudgetService
{
    public function __construct(
        protected ApprovalService $approvalService,
    ) {}

    public function createBudget(array $data, User $user): Budget
    {
        $budget = Budget::create([
            'client_id'   => $data['client_id'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'month'       => $data['month'] ?? null,
            'year'        => $data['year'] ?? null,
            'status'      => 'draft',
        ]);

        $this->syncItems($budget, $data['items'] ?? []);

        $this->log($user, 'budget_created', "Orçamento criado: {$budget->title}", $budget->client_id);

        return $budget->fresh();
    }

    public function updateBudget(Budget $budget, array $data, User $user): Budget
    {
        $budget->update([
            'client_id'   => $data['client_id'] ?? $budget->client_id,
            'title'       => $data['title'] ?? $budget->title,
            'description' => $data['description'] ?? $budget->description,
            'month'       => $data['month'] ?? $budget->month,
            'year'        => $data['year'] ?? $budget->year,
        ]);

        if (isset($data['items'])) {
            $budget->items()->delete();
            $this->syncItems($budget, $data['items']);
        }

        $this->log($user, 'budget_updated', "Orçamento atualizado: {$budget->title}", $budget->client_id);

        return $budget->fresh();
    }

    public function recalculateTotal(Budget $budget): Budget
    {
        return $budget->recalculateTotal();
    }

    public function sendToApproval(Budget $budget, User $user): ApprovalRequest
    {
        $budget->update(['status' => 'pending_approval']);

        $approval = $this->approvalService->createApproval([
            'client_id'       => $budget->client_id,
            'requested_by'    => $user->id,
            'approvable_type' => Budget::class,
            'approvable_id'   => $budget->id,
            'approval_type'   => 'budget',
            'title'           => "Aprovar orçamento: {$budget->title}",
            'sensitive_level' => 'medium',
            'payload'         => [
                'total_amount' => $budget->total_amount,
                'month'        => $budget->month,
                'year'         => $budget->year,
                'items'        => $budget->items->map(fn($i) => [
                    'category' => $i->category,
                    'name'     => $i->name,
                    'amount'   => $i->amount,
                ])->toArray(),
            ],
        ]);

        $this->log($user, 'budget_sent_to_approval', "Orçamento enviado para aprovação: {$budget->title}", $budget->client_id);

        return $approval;
    }

    public function approveBudget(Budget $budget, User $user): Budget
    {
        $budget->update(['status' => 'approved']);
        $this->log($user, 'budget_approved', "Orçamento aprovado: {$budget->title}", $budget->client_id);
        return $budget->fresh();
    }

    public function rejectBudget(Budget $budget, User $user): Budget
    {
        $budget->update(['status' => 'rejected']);
        $this->log($user, 'budget_rejected', "Orçamento rejeitado: {$budget->title}", $budget->client_id);
        return $budget->fresh();
    }

    private function syncItems(Budget $budget, array $items): void
    {
        foreach ($items as $item) {
            BudgetItem::create([
                'budget_id'   => $budget->id,
                'category'    => $item['category'] ?? 'other',
                'name'        => $item['name'],
                'description' => $item['description'] ?? null,
                'amount'      => (float) ($item['amount'] ?? 0),
                'status'      => 'active',
            ]);
        }
        $budget->recalculateTotal();
    }

    private function log(User $user, string $action, string $description, ?int $clientId = null): void
    {
        if (!class_exists(ActivityLog::class)) {
            return;
        }
        ActivityLog::create([
            'user_id'     => $user->id,
            'client_id'   => $clientId,
            'action'      => $action,
            'module'      => 'commercial',
            'description' => $description,
        ]);
    }
}
