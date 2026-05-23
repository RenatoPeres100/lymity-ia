<?php

namespace Database\Seeders;

use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Models\ClientWebsitePage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApprovalRequestSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();
        $admin  = User::where('email', 'admin@lymity.local')->first();

        if (!$client || !$admin) {
            return;
        }

        ApprovalRequest::firstOrCreate(
            ['title' => 'Aprovar post teste', 'client_id' => $client->id],
            [
                'requested_by'   => $admin->id,
                'approval_type'  => 'post',
                'sensitive_level'=> 'low',
                'status'         => 'pending',
                'description'    => 'Rascunho de post para redes sociais gerado pela IA Social Media.',
            ]
        );

        ApprovalRequest::firstOrCreate(
            ['title' => 'Aprovar alteração sensível de campanha', 'client_id' => $client->id],
            [
                'requested_by'   => $admin->id,
                'approval_type'  => 'campaign',
                'sensitive_level'=> 'critical',
                'status'         => 'pending',
                'description'    => 'Alteração crítica de budget e segmentação da campanha Meta Ads. Necessita aprovação do gestor.',
                'payload'        => [
                    'budget_change' => '+R$5.000/mês',
                    'audience'      => 'Reconfigurada',
                    'warning'       => 'Ação irreversível sem histórico no Meta Business.',
                ],
            ]
        );

        $page = ClientWebsitePage::first();

        if ($page) {
            ApprovalRequest::firstOrCreate(
                [
                    'title'           => 'Aprovar página: ' . $page->title,
                    'approvable_type' => ClientWebsitePage::class,
                    'approvable_id'   => $page->id,
                ],
                [
                    'client_id'      => $client->id,
                    'requested_by'   => $admin->id,
                    'approval_type'  => 'website_page',
                    'sensitive_level'=> 'medium',
                    'status'         => 'pending',
                    'description'    => "Página '{$page->title}' aguarda aprovação antes de ir ao ar.",
                ]
            );
        }

        ApprovalRequest::firstOrCreate(
            ['title' => 'Aprovar proposta comercial Q3', 'client_id' => $client->id],
            [
                'requested_by'   => $admin->id,
                'approval_type'  => 'proposal',
                'sensitive_level'=> 'high',
                'status'         => 'approved',
                'description'    => 'Proposta de serviços para o trimestre aprovada anteriormente.',
                'approved_by'    => $admin->id,
                'approved_at'    => now()->subDays(5),
            ]
        );
    }
}
