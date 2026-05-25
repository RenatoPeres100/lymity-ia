<?php

namespace App\Services\Ads;

use App\Models\AdAccount;

class MetaAdsService
{
    public function sandboxGenerateCampaign(array $context): array
    {
        $objective  = $context['objective'] ?? 'leads';
        $budget     = $context['daily_budget'] ?? 50;
        $clientName = $context['client_name'] ?? 'Cliente';

        return [
            'platform' => 'meta_ads',
            'mode'     => 'sandbox',
            'strategy' => "Campanha Meta Ads de {$objective} para {$clientName}. Segmentação por interesses + lookalike de clientes atuais.",
            'campaign' => [
                'name'      => "Campanha {$objective} Meta — {$clientName}",
                'objective' => strtoupper($objective),
                'budget'    => $budget,
            ],
            'audiences' => [
                [
                    'name'        => 'Público Frio — Interesses',
                    'description' => 'Usuários com interesse em marketing digital e empreendedorismo',
                    'size_est'    => '200k–500k',
                ],
                [
                    'name'        => 'Lookalike 1% — Clientes',
                    'description' => 'Similar aos melhores clientes atuais',
                    'size_est'    => '100k–300k',
                ],
            ],
            'creatives' => [
                [
                    'format'      => 'single_image',
                    'headline'    => "Transforme sua empresa com IA",
                    'primary_text' => "Descubra como a {$clientName} usa inteligência artificial para gerar resultados reais.",
                    'cta'         => 'Saiba Mais',
                ],
                [
                    'format'      => 'carousel',
                    'headline'    => "3 razões para escolher {$clientName}",
                    'primary_text' => "Resultados comprovados, tecnologia de ponta e atendimento dedicado.",
                    'cta'         => 'Solicitar Demonstração',
                ],
            ],
            'funnel'    => ['topo' => '60%', 'meio' => '25%', 'fundo' => '15%'],
            'expected_metrics' => [
                'impressions_week' => rand(10000, 50000),
                'clicks_week'      => rand(200, 1000),
                'ctr'              => round(rand(15, 40) / 10, 2),
                'cpc'              => round(rand(10, 60) / 10, 2),
                'leads_week'       => rand(10, 60),
                'cpl'              => round(rand(20, 100) / 10, 2),
            ],
        ];
    }

    public function validateSandboxAccount(AdAccount $account): bool
    {
        return $account->platform === 'meta_ads' && $account->status === 'active';
    }

    public function realPublish(): never
    {
        throw new \RuntimeException('Publicação real no Meta Ads não está habilitada nesta fase. Use o modo sandbox.');
    }
}
