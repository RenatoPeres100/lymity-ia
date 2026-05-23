<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SeoCluster;
use Illuminate\Database\Seeder;

class SeoClusterSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        $clusters = [
            [
                'name'         => 'IA & Automação de Marketing',
                'main_keyword' => 'automação de marketing com ia',
                'description'  => 'Cluster de conteúdo sobre o uso de inteligência artificial em marketing digital, automações e eficiência operacional.',
                'status'       => 'active',
                'client_id'    => null,
            ],
            [
                'name'         => 'SEO & Conteúdo Orgânico',
                'main_keyword' => 'estratégia de seo',
                'description'  => 'Cluster focado em SEO técnico, link building, pesquisa de palavras-chave e conteúdo orgânico.',
                'status'       => 'active',
                'client_id'    => null,
            ],
            [
                'name'         => 'Tráfego Pago & Performance',
                'main_keyword' => 'google ads marketing digital',
                'description'  => 'Campanhas pagas, Google Ads, Meta Ads, otimização de conversão e análise de ROI.',
                'status'       => 'active',
                'client_id'    => $client?->id,
            ],
        ];

        foreach ($clusters as $cluster) {
            SeoCluster::firstOrCreate(
                ['name' => $cluster['name'], 'client_id' => $cluster['client_id']],
                $cluster
            );
        }
    }
}
