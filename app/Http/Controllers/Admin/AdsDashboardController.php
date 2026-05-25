<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\ApprovalRequest;
use App\Models\CampaignBudgetChange;
use App\Models\CampaignMetric;

class AdsDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'draft'            => AdCampaign::where('status', 'draft')->count(),
            'pending_approval' => AdCampaign::where('status', 'pending_approval')->count(),
            'approved'         => AdCampaign::where('status', 'approved')->count(),
            'active'           => AdCampaign::where('status', 'active')->count(),
            'total_budget'     => AdCampaign::whereNotNull('daily_budget')->sum('daily_budget'),
        ];

        $metricsAgg = CampaignMetric::selectRaw('SUM(leads) as total_leads, SUM(cost) as total_cost, SUM(conversions) as total_conversions')->first();
        $stats['total_leads'] = round($metricsAgg->total_leads ?? 0, 0);
        $stats['avg_cpa']     = ($metricsAgg->total_conversions ?? 0) > 0
            ? round(($metricsAgg->total_cost ?? 0) / $metricsAgg->total_conversions, 2)
            : 0;

        $recentCampaigns     = AdCampaign::with(['client', 'account'])->latest()->limit(8)->get();
        $pendingBudgetChanges = CampaignBudgetChange::where('status', 'pending_approval')
            ->with(['campaign', 'requester'])->latest()->limit(5)->get();

        return view('admin.ads.index', compact('stats', 'recentCampaigns', 'pendingBudgetChanges'));
    }
}
