<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\ApprovalRequest;
use App\Models\CampaignMetric;
use Illuminate\Http\Request;

class AdsDashboardController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->user()->client_id;

        $stats = [
            'total'     => AdCampaign::where('client_id', $clientId)->count(),
            'active'    => AdCampaign::where('client_id', $clientId)->where('status', 'active')->count(),
            'approved'  => AdCampaign::where('client_id', $clientId)->where('status', 'approved')->count(),
            'pending'   => AdCampaign::where('client_id', $clientId)->where('status', 'pending_approval')->count(),
        ];

        $campaignIds = AdCampaign::where('client_id', $clientId)->pluck('id');

        $totals = CampaignMetric::whereIn('ad_campaign_id', $campaignIds)->selectRaw('
            SUM(leads) as total_leads,
            SUM(cost) as total_cost,
            SUM(impressions) as total_impressions,
            SUM(clicks) as total_clicks
        ')->first();

        $recentCampaigns = AdCampaign::where('client_id', $clientId)
            ->with('account')->latest()->limit(5)->get();

        return view('client.ads.index', compact('stats', 'totals', 'recentCampaigns'));
    }
}
