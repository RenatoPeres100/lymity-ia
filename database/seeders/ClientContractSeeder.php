<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientContract;
use Illuminate\Database\Seeder;

class ClientContractSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        if (!$client) {
            return;
        }

        ClientContract::updateOrCreate(
            ['title' => 'Contrato de prestação de serviços digitais', 'client_id' => $client->id],
            [
                'description' => "OBJETO: Prestação de serviços de marketing digital com inteligência artificial.\n\nSERVIÇOS INCLUSOS:\n- Gestão de tráfego pago (Google Ads + Meta Ads)\n- Produção de conteúdo com IA\n- SEO técnico e estratégico\n- Automação de marketing\n- Relatórios mensais de performance\n\nVIGÊNCIA: 12 meses, renovável automaticamente.\n\nPAGAMENTO: Mensal, antecipado.\n\nCONFIDENCIALIDADE: As partes comprometem-se a manter sigilo sobre informações estratégicas compartilhadas.",
                'status'      => 'draft',
            ]
        );
    }
}
