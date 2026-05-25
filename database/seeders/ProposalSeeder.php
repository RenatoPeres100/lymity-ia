<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProposalSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();
        $admin  = User::where('email', 'admin@lymity.local')->first();

        if (!$client) {
            return;
        }

        $proposal = Proposal::updateOrCreate(
            ['title' => 'Proposta de crescimento digital com IA', 'client_id' => $client->id],
            [
                'created_by'  => $admin?->id,
                'description' => 'Proposta completa de operação digital com inteligência artificial para o cliente demo. Inclui tráfego pago, SEO, automação e conteúdo.',
                'terms'       => "Pagamento: mensal, antecipado via boleto ou PIX.\nVigência: 3 meses (renovável).\nReajuste: IGPM anual.\nCancelamento: aviso prévio de 30 dias.\nTodos os serviços são executados com aprovação humana em cada etapa relevante.",
                'valid_until' => now()->addDays(30)->toDateString(),
                'status'      => 'draft',
            ]
        );

        $items = [
            ['name' => 'Setup Estratégico e Diagnóstico', 'description' => 'Auditoria completa, mapeamento de oportunidades e plano de ação 90 dias', 'quantity' => 1, 'unit_price' => 1800.00],
            ['name' => 'Gestão de Tráfego Pago (Google + Meta)', 'description' => 'Criação, otimização e relatórios mensais de campanhas pagas', 'quantity' => 1, 'unit_price' => 2400.00],
            ['name' => 'Automação e Conteúdo com IA', 'description' => 'Fluxos automáticos, produção de artigos e nutrição de leads', 'quantity' => 1, 'unit_price' => 1600.00],
        ];

        foreach ($items as $itemData) {
            ProposalItem::updateOrCreate(
                ['proposal_id' => $proposal->id, 'name' => $itemData['name']],
                array_merge($itemData, ['total_price' => $itemData['quantity'] * $itemData['unit_price']])
            );
        }

        $total = $proposal->items()->sum('total_price');
        $proposal->update(['total_amount' => $total]);
    }
}
