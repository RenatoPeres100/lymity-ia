<?php

namespace App\Services\Ads;

use App\Models\AdAccount;

class GoogleAdsService
{
    public function sandboxGenerateCampaign(array $context): array
    {
        $objective  = $context['objective'] ?? 'leads';
        $budget     = $context['daily_budget'] ?? 50;
        $clientName = $context['client_name'] ?? 'Cliente';

        return [
            'platform' => 'google_ads',
            'mode'     => 'sandbox',
            'strategy' => "Campanha Google Ads de {$objective} para {$clientName}. Foco em intenção de compra com palavras-chave de cauda longa.",
            'campaign' => [
                'name'      => "Campanha {$objective} — {$clientName}",
                'type'      => 'SEARCH',
                'objective' => $objective,
                'budget'    => $budget,
            ],
            'groups' => [
                [
                    'name'             => 'Grupo Principal',
                    'targeting'        => 'Público com alta intenção de compra',
                    'keywords_count'   => 5,
                ],
            ],
            'extensions_suggested' => ['callout', 'sitelink', 'call'],
            'expected_metrics'     => [
                'impressions_week' => rand(5000, 15000),
                'clicks_week'      => rand(150, 600),
                'ctr'              => round(rand(30, 60) / 10, 2),
                'cpc'              => round(rand(20, 80) / 10, 2),
                'conversions_week' => rand(5, 30),
            ],
        ];
    }

    public function validateSandboxAccount(AdAccount $account): bool
    {
        return $account->platform === 'google_ads' && $account->status === 'active';
    }

    public function realPublish(): never
    {
        throw new \RuntimeException('Publicação real no Google Ads não está habilitada nesta fase. Use o modo sandbox.');
    }
}
