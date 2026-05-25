<?php

namespace Database\Seeders;

use App\Models\AdCampaign;
use App\Services\Ads\CampaignMetricService;
use Illuminate\Database\Seeder;

class CampaignMetricSeeder extends Seeder
{
    public function run(): void
    {
        $campaign = AdCampaign::where('name', 'Campanha Demo — Captação de Leads')->first();

        if (!$campaign) {
            return;
        }

        if ($campaign->metrics()->count() === 0) {
            app(CampaignMetricService::class)->createMockMetrics($campaign, 7);
        }
    }
}
