<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SeoContentPlan;
use Illuminate\Database\Seeder;

class SeoContentPlanSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        $plans = [
            [
                'title'       => 'Plano de Conteúdo SEO — Junho 2026',
                'description' => "Objetivos:\n- 4 posts de blog sobre IA e marketing\n- 2 posts sobre automação\n- Foco em palavras-chave de intenção comercial\n\nTemas prioritários:\n1. Como a IA está transformando agências digitais\n2. Guia de SEO com inteligência artificial\n3. Automação de marketing: o que é e como implementar\n4. Funcionários IA vs equipes tradicionais",
                'month'       => 6,
                'year'        => 2026,
                'status'      => 'active',
                'client_id'   => null,
            ],
            [
                'title'       => 'Plano de Conteúdo — Julho 2026',
                'description' => "Planejamento inicial para julho. Temas a definir após análise de performance de junho.",
                'month'       => 7,
                'year'        => 2026,
                'status'      => 'draft',
                'client_id'   => null,
            ],
            [
                'title'       => 'Plano de Blog Cliente — Junho 2026',
                'description' => "Plano de conteúdo para blog do cliente. 2 posts mensais com foco no segmento.",
                'month'       => 6,
                'year'        => 2026,
                'status'      => 'approved',
                'client_id'   => $client?->id,
            ],
        ];

        foreach ($plans as $plan) {
            SeoContentPlan::firstOrCreate(
                ['title' => $plan['title'], 'client_id' => $plan['client_id']],
                $plan
            );
        }
    }
}
