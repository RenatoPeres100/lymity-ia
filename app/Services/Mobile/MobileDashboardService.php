<?php

namespace App\Services\Mobile;

use App\Models\AdCampaign;
use App\Models\AppNotification;
use App\Models\ApprovalRequest;
use App\Models\Budget;
use App\Models\ClientBlogPost;
use App\Models\Proposal;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Support\Collection;

class MobileDashboardService
{
    public function getClientSummary(User $user): array
    {
        $scope = $this->resolveScope($user);

        return [
            'pending_approvals'   => $this->buildApprovalQuery($scope)->where('status', 'pending')->count(),
            'urgent_approvals'    => $this->buildApprovalQuery($scope)->where('status', 'pending')->where('sensitive_level', 'critical')->count(),
            'social_posts_count'  => $this->buildSocialQuery($scope)->count(),
            'campaigns_count'     => $this->buildCampaignQuery($scope)->count(),
            'proposals_count'     => $this->buildProposalQuery($scope)->count(),
            'budgets_count'       => $this->buildBudgetQuery($scope)->count(),
            'unread_notifications' => $this->buildNotifQuery($user)->where('status', 'unread')->count(),
        ];
    }

    public function getPendingApprovals(User $user): Collection
    {
        $scope = $this->resolveScope($user);

        return $this->buildApprovalQuery($scope)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function getCalendarItems(User $user): array
    {
        $scope = $this->resolveScope($user);

        $posts = $this->buildSocialQuery($scope)
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'type'  => 'social_post',
                'id'    => $p->id,
                'title' => $p->title,
                'date'  => $p->scheduled_at?->toDateString(),
                'status'=> $p->status,
            ]);

        $campaigns = $this->buildCampaignQuery($scope)
            ->whereNotNull('start_date')
            ->orderBy('start_date')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'type'  => 'campaign',
                'id'    => $c->id,
                'title' => $c->name,
                'date'  => $c->start_date,
                'status'=> $c->status,
            ]);

        $approvals = $this->buildApprovalQuery($scope)
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'type'  => 'approval',
                'id'    => $a->id,
                'title' => $a->title,
                'date'  => $a->due_at?->toDateString(),
                'status'=> $a->status,
            ]);

        return collect($posts)->concat($campaigns)->concat($approvals)
            ->sortBy('date')
            ->values()
            ->all();
    }

    public function getReportsSummary(User $user): array
    {
        $scope = $this->resolveScope($user);

        return [
            'campaigns' => [
                'total'  => $this->buildCampaignQuery($scope)->count(),
                'active' => $this->buildCampaignQuery($scope)->where('status', 'active')->count(),
                'paused' => $this->buildCampaignQuery($scope)->where('status', 'paused')->count(),
            ],
            'social' => [
                'total'     => $this->buildSocialQuery($scope)->count(),
                'published' => $this->buildSocialQuery($scope)->where('status', 'published')->count(),
                'pending'   => $this->buildSocialQuery($scope)->where('status', 'draft')->count(),
            ],
            'approvals' => [
                'total'    => $this->buildApprovalQuery($scope)->count(),
                'pending'  => $this->buildApprovalQuery($scope)->where('status', 'pending')->count(),
                'approved' => $this->buildApprovalQuery($scope)->where('status', 'approved')->count(),
                'rejected' => $this->buildApprovalQuery($scope)->where('status', 'rejected')->count(),
            ],
            'proposals' => [
                'total'    => $this->buildProposalQuery($scope)->count(),
                'accepted' => $this->buildProposalQuery($scope)->where('status', 'accepted')->count(),
                'sent'     => $this->buildProposalQuery($scope)->where('status', 'sent')->count(),
            ],
            'budgets' => [
                'total'  => $this->buildBudgetQuery($scope)->count(),
                'active' => $this->buildBudgetQuery($scope)->where('status', 'active')->count(),
            ],
        ];
    }

    private function resolveScope(User $user): ?int
    {
        return $user->isAdminGeral() ? null : $user->client_id;
    }

    private function buildApprovalQuery(?int $clientId)
    {
        $q = ApprovalRequest::query();
        if ($clientId !== null) {
            $q->where('client_id', $clientId);
        }
        return $q;
    }

    private function buildSocialQuery(?int $clientId)
    {
        $q = SocialPost::query();
        if ($clientId !== null) {
            $q->where('client_id', $clientId);
        }
        return $q;
    }

    private function buildCampaignQuery(?int $clientId)
    {
        $q = AdCampaign::query();
        if ($clientId !== null) {
            $q->where('client_id', $clientId);
        }
        return $q;
    }

    private function buildProposalQuery(?int $clientId)
    {
        $q = Proposal::query();
        if ($clientId !== null) {
            $q->where('client_id', $clientId);
        }
        return $q;
    }

    private function buildBudgetQuery(?int $clientId)
    {
        $q = Budget::query();
        if ($clientId !== null) {
            $q->where('client_id', $clientId);
        }
        return $q;
    }

    private function buildNotifQuery(User $user)
    {
        $q = AppNotification::where('user_id', $user->id);
        if ($user->client_id) {
            $q->orWhere(fn ($sub) => $sub->where('client_id', $user->client_id)->whereNull('user_id'));
        }
        return $q;
    }
}
