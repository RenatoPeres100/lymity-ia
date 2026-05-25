<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Client;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        if (!$client) {
            return;
        }

        $budget = Budget::updateOrCreate(
            ['title' => 'Orçamento Mensal — Operação Digital', 'client_id' => $client->id],
            [
                'description' => 'Orçamento mensal de operação digital com distribuição de verba de mídia, produção e serviços.',
                'month'       => now()->month,
                'year'        => now()->year,
                'status'      => 'draft',
            ]
        );

        $items = [
            ['category' => 'media',      'name' => 'Investimento Google Ads',   'description' => 'Verba de mídia para search e performance', 'amount' => 3000.00],
            ['category' => 'media',      'name' => 'Investimento Meta Ads',     'description' => 'Verba de mídia Facebook e Instagram',       'amount' => 2000.00],
            ['category' => 'production', 'name' => 'Produção de Criativos',     'description' => 'Peças estáticas e vídeos mensais',          'amount' => 1000.00],
            ['category' => 'service',    'name' => 'Gestão Mensal',             'description' => 'Fee de gestão da agência',                  'amount' => 2500.00],
            ['category' => 'tool',       'name' => 'Ferramentas e Automações',  'description' => 'Licenças de plataformas e IA',              'amount' => 500.00],
        ];

        foreach ($items as $itemData) {
            BudgetItem::updateOrCreate(
                ['budget_id' => $budget->id, 'name' => $itemData['name']],
                array_merge($itemData, ['status' => 'active'])
            );
        }

        $total = $budget->items()->where('status', 'active')->sum('amount');
        $budget->update(['total_amount' => $total]);
    }
}
