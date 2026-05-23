<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SeoKeyword;
use Illuminate\Database\Seeder;

class SeoKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        $keywords = [
            [
                'keyword'       => 'agência de marketing digital com IA',
                'search_intent' => 'commercial',
                'priority'      => 'high',
                'difficulty'    => 45,
                'volume'        => 1200,
                'status'        => 'planned',
                'notes'         => 'Palavra-chave principal da Lymity IA',
                'client_id'     => null,
            ],
            [
                'keyword'       => 'como usar inteligência artificial no marketing',
                'search_intent' => 'informational',
                'priority'      => 'high',
                'difficulty'    => 38,
                'volume'        => 3400,
                'status'        => 'in_progress',
                'notes'         => 'Blog post em produção',
                'client_id'     => null,
            ],
            [
                'keyword'       => 'funcionários ia para empresas',
                'search_intent' => 'commercial',
                'priority'      => 'high',
                'difficulty'    => 22,
                'volume'        => 590,
                'status'        => 'planned',
                'notes'         => null,
                'client_id'     => null,
            ],
            [
                'keyword'       => 'automação de marketing digital',
                'search_intent' => 'commercial',
                'priority'      => 'medium',
                'difficulty'    => 55,
                'volume'        => 4800,
                'status'        => 'planned',
                'notes'         => null,
                'client_id'     => $client?->id,
            ],
            [
                'keyword'       => 'o que é SEO',
                'search_intent' => 'informational',
                'priority'      => 'low',
                'difficulty'    => 70,
                'volume'        => 22000,
                'status'        => 'archived',
                'notes'         => 'Muito competitiva para fase inicial',
                'client_id'     => $client?->id,
            ],
        ];

        foreach ($keywords as $kw) {
            SeoKeyword::firstOrCreate(
                ['keyword' => $kw['keyword'], 'client_id' => $kw['client_id']],
                $kw
            );
        }
    }
}
