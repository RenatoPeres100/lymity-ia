<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\CampaignMetric;
use App\Services\Ads\CampaignMetricService;
use Illuminate\Http\Request;

class CampaignMetricController extends Controller
{
    public function __construct(protected CampaignMetricService $service) {}

    public function index(Request $request)
    {
        $query = CampaignMetric::with('campaign.client')->latest('date');

        if ($request->filled('campaign_id')) {
            $query->where('ad_campaign_id', $request->campaign_id);
        }

        $metrics   = $query->paginate(30)->withQueryString();
        $campaigns = AdCampaign::with('client')->get();

        $totals = CampaignMetric::selectRaw('
            SUM(impressions) as total_impressions,
            SUM(clicks) as total_clicks,
            SUM(cost) as total_cost,
            SUM(leads) as total_leads,
            SUM(conversions) as total_conversions,
            SUM(revenue) as total_revenue
        ')->first();

        return view('admin.ads.metrics.index', compact('metrics', 'campaigns', 'totals'));
    }
}
