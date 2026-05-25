<?php

namespace App\Services\Ads;

use App\Models\AdCampaign;
use App\Models\ApprovalRequest;
use App\Models\CampaignBudgetChange;
use App\Models\User;
use App\Services\Approval\ApprovalService;

class CampaignApprovalService
{
    public function __construct(protected ApprovalService $approvalService) {}

    public function createCampaignApproval(AdCampaign $campaign, User $user): ApprovalRequest
    {
        return $this->approvalService->createApproval([
            'approval_type'   => 'campaign',
            'approvable_type' => AdCampaign::class,
            'approvable_id'   => $campaign->id,
            'client_id'       => $campaign->client_id,
            'ai_employee_id'  => $campaign->ai_employee_id,
            'requested_by'    => $user->id,
            'title'           => "Aprovar campanha: {$campaign->name}",
            'description'     => "Solicitação de aprovação para campanha {$campaign->platform_label} com objetivo {$campaign->objective_label}.",
            'sensitive_level' => 'medium',
            'status'          => 'pending',
            'payload'         => [
                'campaign_id'      => $campaign->id,
                'platform'         => $campaign->platform,
                'objective'        => $campaign->objective,
                'daily_budget'     => $campaign->daily_budget,
                'total_budget'     => $campaign->total_budget,
                'start_date'       => $campaign->start_date?->toDateString(),
                'end_date'         => $campaign->end_date?->toDateString(),
                'strategy_summary' => $campaign->strategy_summary,
            ],
        ]);
    }

    public function createBudgetApproval(CampaignBudgetChange $change, User $user): ApprovalRequest
    {
        $campaign = $change->campaign;

        return $this->approvalService->createApproval([
            'approval_type'   => 'budget',
            'approvable_type' => CampaignBudgetChange::class,
            'approvable_id'   => $change->id,
            'client_id'       => $campaign->client_id,
            'requested_by'    => $user->id,
            'title'           => "Aprovar alteração de orçamento: {$campaign->name}",
            'description'     => "Solicitação de alteração de orçamento de R$ {$change->old_budget} para R$ {$change->new_budget}.",
            'sensitive_level' => 'high',
            'status'          => 'pending',
            'payload'         => [
                'campaign_id'  => $campaign->id,
                'campaign_name' => $campaign->name,
                'old_budget'   => $change->old_budget,
                'new_budget'   => $change->new_budget,
                'reason'       => $change->reason,
            ],
        ]);
    }

    public function syncApprovalResult(ApprovalRequest $approvalRequest, string $status, User $user): void
    {
        $approvable = $approvalRequest->approvable;
        if (!$approvable) {
            return;
        }

        if ($approvable instanceof AdCampaign) {
            $this->syncCampaignStatus($approvable, $status, $user);
        }

        if ($approvable instanceof CampaignBudgetChange) {
            $this->syncBudgetChangeStatus($approvable, $status, $user);
        }
    }

    private function syncCampaignStatus(AdCampaign $campaign, string $status, User $user): void
    {
        $updates = match ($status) {
            'approved'          => ['status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()],
            'rejected'          => ['status' => 'rejected'],
            'changes_requested' => ['status' => 'draft'],
            default             => [],
        };

        if (!empty($updates)) {
            $campaign->update($updates);
        }
    }

    private function syncBudgetChangeStatus(CampaignBudgetChange $change, string $status, User $user): void
    {
        $updates = match ($status) {
            'approved'          => ['status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()],
            'rejected'          => ['status' => 'rejected'],
            'changes_requested' => ['status' => 'pending_approval'],
            default             => [],
        };

        if (!empty($updates)) {
            $change->update($updates);
        }
    }
}
