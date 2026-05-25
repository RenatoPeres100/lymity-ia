<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AppNotification;
use App\Models\ApprovalRequest;
use App\Models\Budget;
use App\Models\Proposal;
use App\Models\SocialPost;
use App\Services\Mobile\MobileDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppDashboardController extends Controller
{
    public function __construct(private MobileDashboardService $service) {}

    public function __invoke(Request $request): View
    {
        $user    = $request->user();
        $summary = $this->service->getClientSummary($user);
        $scope   = $user->isAdminGeral() ? null : $user->client_id;

        $pendingApprovals = ApprovalRequest::when($scope, fn ($q) => $q->where('client_id', $scope))
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentPosts = SocialPost::when($scope, fn ($q) => $q->where('client_id', $scope))
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $proposals = Proposal::when($scope, fn ($q) => $q->where('client_id', $scope))
            ->whereIn('status', ['sent', 'pending_approval'])
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $budgets = Budget::when($scope, fn ($q) => $q->where('client_id', $scope))
            ->whereIn('status', ['active', 'draft'])
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $notifications = AppNotification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if ($user->client_id) {
                $q->orWhere(fn ($s) => $s->where('client_id', $user->client_id)->whereNull('user_id'));
            }
        })->orderByDesc('created_at')->limit(5)->get();

        return view('app.dashboard', compact('summary', 'pendingApprovals', 'recentPosts', 'proposals', 'budgets', 'notifications'));
    }
}
