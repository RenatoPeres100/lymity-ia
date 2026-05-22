<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientAsset;
use Illuminate\Database\Seeder;

class ClientAssetSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::where('name', 'Cliente Demonstração')->first();
        if (!$client) return;

        $assets = [
            [
                'name'         => 'Logo Principal',
                'notes'  => 'Logo da clínica em fundo transparente (PNG)',
                'type'         => 'logo',
                'source'       => 'upload',
                'external_url' => 'https://via.placeholder.com/300x100/C9956C/FFFFFF?text=LOGO',
            ],
            [
                'name'         => 'Manual de Identidade Visual',
                'notes'  => 'Guia completo de identidade visual da marca',
                'type'         => 'brand_file',
                'source'       => 'upload',
                'external_url' => null,
            ],
        ];

        foreach ($assets as $asset) {
            ClientAsset::updateOrCreate(
                ['client_id' => $client->id, 'name' => $asset['name']],
                array_merge($asset, ['client_id' => $client->id])
            );
        }
    }
}
