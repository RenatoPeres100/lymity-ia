<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Services\Ads\CampaignMetricService;
use Illuminate\Http\Request;

class AdCampaignController extends Controller
{
    public function __construct(protected CampaignMetricService $metrics) {}

    public function index(Request $request)
    {
        $clientId  = $request->user()->client_id;
        $campaigns = AdCampaign::where('client_id', $clientId)
            ->with('account')
            ->latest()
            ->paginate(15);

        return view('client.ads.campaigns.index', compact('campaigns'));
    }

    public function show(AdCampaign $adCampaign, Request $request)
    {
        $clientId = $request->user()->client_id;
        abort_unless($adCampaign->client_id === $clientId, 403);

        $adCampaign->load(['account', 'groups.creatives', 'groups.keywords', 'creatives', 'keywords', 'audiences', 'metrics']);
        $summary = $this->metrics->summarizeCampaign($adCampaign);

        return view('client.ads.campaigns.show', compact('adCampaign', 'summary'));
    }
}
