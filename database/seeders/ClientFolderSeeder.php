<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientFolder;
use Illuminate\Database\Seeder;

class ClientFolderSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();
        if (!$client) return;

        $folders = [
            ['name' => 'Materiais da Marca', 'description' => 'Logos, paletas, fontes e guias de marca'],
            ['name' => 'Artes Redes Sociais', 'description' => 'Posts e stories criados para redes sociais'],
            ['name' => 'Documentos', 'description' => 'Contratos, propostas e relatórios'],
        ];

        foreach ($folders as $data) {
            if (!ClientFolder::where('client_id', $client->id)->where('name', $data['name'])->exists()) {
                ClientFolder::create([
                    'client_id'   => $client->id,
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'local_path'  => 'client-files/' . $client->id . '/' . \Illuminate\Support\Str::slug($data['name']),
                ]);
            }
        }
    }
}
