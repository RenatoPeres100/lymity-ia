<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\CampaignMetric;
use App\Services\Ads\CampaignMetricService;
use Illuminate\Http\Request;

class AdsReportController extends Controller
{
    public function __construct(protected CampaignMetricService $metrics) {}

    public function index(Request $request)
    {
        $clientId    = $request->user()->client_id;
        $campaignIds = AdCampaign::where('client_id', $clientId)->pluck('id');

        $metricsQuery = CampaignMetric::whereIn('ad_campaign_id', $campaignIds)
            ->orderByDesc('date');

        if ($request->filled('campaign_id')) {
            $campaign = AdCampaign::where('client_id', $clientId)->findOrFail($request->campaign_id);
            $metricsQuery->where('ad_campaign_id', $campaign->id);
        }

        $metrics = $metricsQuery->paginate(30)->withQueryString();

        $totals = CampaignMetric::whereIn('ad_campaign_id', $campaignIds)->selectRaw('
            SUM(impressions) as total_impressions,
            SUM(clicks) as total_clicks,
            SUM(cost) as total_cost,
            SUM(leads) as total_leads,
            SUM(conversions) as total_conversions,
            SUM(revenue) as total_revenue
        ')->first();

        $campaigns = AdCampaign::where('client_id', $clientId)->get();

        return view('client.ads.reports.index', compact('metrics', 'totals', 'campaigns'));
    }
}
