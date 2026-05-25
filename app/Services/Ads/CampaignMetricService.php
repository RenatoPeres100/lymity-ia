<?php

namespace App\Services\Ads;

use App\Models\AdCampaign;
use App\Models\CampaignMetric;

class CampaignMetricService
{
    public function createMockMetrics(AdCampaign $campaign, int $days = 7): void
    {
        $startDate = now()->subDays($days - 1);

        for ($i = 0; $i < $days; $i++) {
            $date        = $startDate->copy()->addDays($i);
            $impressions = rand(500, 5000);
            $clicks      = (int) ($impressions * (rand(20, 60) / 1000));
            $cost        = round($clicks * (rand(15, 80) / 10), 2);
            $conversions = round($clicks * (rand(5, 20) / 100), 2);
            $leads       = round($conversions * (rand(50, 100) / 100), 2);
            $revenue     = round($leads * (rand(100, 500)), 2);

            $metric = CampaignMetric::create([
                'ad_campaign_id' => $campaign->id,
                'date'           => $date->toDateString(),
                'impressions'    => $impressions,
                'clicks'         => $clicks,
                'cost'           => $cost,
                'conversions'    => $conversions,
                'leads'          => $leads,
                'revenue'        => $revenue,
            ]);

            $this->calculateDerivedMetrics($metric);
        }
    }

    public function calculateDerivedMetrics(CampaignMetric $metric): void
    {
        $updates = [];

        if ($metric->impressions > 0) {
            $updates['ctr'] = round(($metric->clicks / $metric->impressions) * 100, 4);
        }

        if ($metric->clicks > 0) {
            $updates['cpc'] = round($metric->cost / $metric->clicks, 2);
        }

        if ($metric->conversions > 0) {
            $updates['cpa'] = round($metric->cost / $metric->conversions, 2);
        }

        if ($metric->cost > 0 && $metric->revenue > 0) {
            $updates['roas'] = round($metric->revenue / $metric->cost, 4);
        }

        if (!empty($updates)) {
            $metric->update($updates);
        }
    }

    public function summarizeCampaign(AdCampaign $campaign): array
    {
        $metrics = $campaign->metrics;

        if ($metrics->isEmpty()) {
            return [
                'total_impressions' => 0,
                'total_clicks'      => 0,
                'total_cost'        => 0,
                'total_leads'       => 0,
                'total_conversions' => 0,
                'total_revenue'     => 0,
                'avg_ctr'           => 0,
                'avg_cpc'           => 0,
                'avg_cpa'           => 0,
                'avg_roas'          => 0,
                'days_tracked'      => 0,
            ];
        }

        $totalImpressions = $metrics->sum('impressions');
        $totalClicks      = $metrics->sum('clicks');
        $totalCost        = $metrics->sum('cost');
        $totalLeads       = $metrics->sum('leads');
        $totalConversions = $metrics->sum('conversions');
        $totalRevenue     = $metrics->sum('revenue');

        return [
            'total_impressions' => $totalImpressions,
            'total_clicks'      => $totalClicks,
            'total_cost'        => round($totalCost, 2),
            'total_leads'       => round($totalLeads, 2),
            'total_conversions' => round($totalConversions, 2),
            'total_revenue'     => round($totalRevenue, 2),
            'avg_ctr'           => $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0,
            'avg_cpc'           => $totalClicks > 0 ? round($totalCost / $totalClicks, 2) : 0,
            'avg_cpa'           => $totalConversions > 0 ? round($totalCost / $totalConversions, 2) : 0,
            'avg_roas'          => $totalCost > 0 && $totalRevenue > 0 ? round($totalRevenue / $totalCost, 2) : 0,
            'days_tracked'      => $metrics->count(),
        ];
    }
}
