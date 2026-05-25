<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Ads\AdsPlanningService;
use Illuminate\Http\Request;

class AdsAiController extends Controller
{
    public function __construct(protected AdsPlanningService $planning) {}

    public function generateAi(Request $request)
    {
        $data = $request->validate([
            'platform'    => 'required|in:google_ads,meta_ads',
            'client_id'   => 'nullable|exists:clients,id',
            'objective'   => 'required|in:leads,sales,traffic,awareness,engagement,app,remarketing',
            'daily_budget' => 'required|numeric|min:1',
            'prompt'      => 'nullable|string',
        ]);

        $campaign = $this->planning->generateCampaignWithAi($data, $request->user());

        return redirect()->route('admin.ads.campaigns.show', $campaign)
            ->with('success', 'Campanha gerada por IA com sucesso (modo sandbox).');
    }
}
