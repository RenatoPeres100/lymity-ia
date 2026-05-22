<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientWebsite;
use Illuminate\Database\Seeder;

class ClientWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::where('name', 'Cliente Demonstração')->first();
        if (!$client) return;

        ClientWebsite::updateOrCreate(
            ['client_id' => $client->id, 'domain' => 'cliente-demo.local'],
            [
                'platform'        => 'internal',
                'status'          => 'active',
                'primary_color'   => '#C9956C',
                'secondary_color' => '#F5F5F5',
                'notes'           => 'Site institucional da clínica. Hospedado na plataforma Lymity.',
            ]
        );
    }
}
