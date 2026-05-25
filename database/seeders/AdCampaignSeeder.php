<?php

namespace Database\Seeders;

use App\Models\AdAccount;
use App\Models\AdAudience;
use App\Models\AdCampaign;
use App\Models\AdCreative;
use App\Models\AdGroup;
use App\Models\AdKeyword;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $client  = Client::first();
        $admin   = User::where('email', 'admin@lymity.local')->first();
        $account = AdAccount::where('platform', 'google_ads')->first();

        if (!$account || !$client) {
            return;
        }

        $campaign = AdCampaign::updateOrCreate(
            ['name' => 'Campanha Demo — Captação de Leads', 'client_id' => $client->id],
            [
                'ad_account_id'    => $account->id,
                'company_id'       => $client->company_id,
                'created_by'       => $admin?->id,
                'platform'         => 'google_ads',
                'objective'        => 'leads',
                'status'           => 'pending_approval',
                'daily_budget'     => 50.00,
                'total_budget'     => 1500.00,
                'start_date'       => now()->addDays(3)->toDateString(),
                'end_date'         => now()->addDays(33)->toDateString(),
                'strategy_summary' => 'Campanha de captação de leads via Google Search para o cliente demo. Foco em palavras-chave de alta intenção de compra no segmento de marketing digital com IA. Orçamento inicial conservador para coleta de dados.',
                'requires_approval' => true,
            ]
        );

        $group = AdGroup::updateOrCreate(
            ['ad_campaign_id' => $campaign->id, 'name' => 'Grupo Principal — Marketing Digital'],
            ['targeting_summary' => 'Usuários com alta intenção de compra em marketing digital', 'status' => 'draft']
        );

        $creatives = [
            ['title' => 'Anúncio Responsivo 1', 'headline' => 'Agência Digital com Inteligência Artificial', 'description' => 'Funcionários IA especializados trabalhando para o seu negócio. Aprovação humana em cada etapa.', 'cta' => 'Solicitar Demo'],
            ['title' => 'Anúncio Responsivo 2', 'headline' => 'Transforme Seu Marketing com IA', 'description' => 'SEO, Social Media e Ads gerenciados por IA. Resultados reais com aprovação humana.', 'cta' => 'Saiba Mais'],
        ];

        foreach ($creatives as $cData) {
            AdCreative::updateOrCreate(
                ['ad_campaign_id' => $campaign->id, 'title' => $cData['title']],
                array_merge($cData, ['ad_group_id' => $group->id, 'status' => 'draft', 'destination_url' => 'https://lymity.com.br'])
            );
        }

        $keywords = [
            ['keyword' => 'agência digital com inteligência artificial', 'match_type' => 'exact',   'status' => 'active'],
            ['keyword' => 'marketing digital para empresas',             'match_type' => 'phrase',  'status' => 'active'],
            ['keyword' => 'automação de marketing digital',             'match_type' => 'broad',   'status' => 'active'],
            ['keyword' => 'como captar leads online',                   'match_type' => 'phrase',  'status' => 'active'],
            ['keyword' => 'agência barata',                             'match_type' => 'negative','status' => 'negative'],
        ];

        foreach ($keywords as $kw) {
            AdKeyword::updateOrCreate(
                ['ad_campaign_id' => $campaign->id, 'keyword' => $kw['keyword']],
                array_merge($kw, ['ad_group_id' => $group->id])
            );
        }

        AdAudience::updateOrCreate(
            ['ad_campaign_id' => $campaign->id, 'name' => 'Remarketing — Visitantes 30 dias'],
            [
                'description'    => 'Usuários que visitaram o site nos últimos 30 dias',
                'targeting_data' => ['type' => 'remarketing', 'days' => 30, 'mode' => 'sandbox'],
                'status'         => 'active',
            ]
        );
    }
}
