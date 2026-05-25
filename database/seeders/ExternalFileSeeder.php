<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ExternalFile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExternalFileSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();
        $admin  = User::where('email', 'admin@lymity.local')->first() ?? User::first();

        if (!$client || !$admin) return;

        $files = [
            [
                'name'         => 'Guia de Marca — Demo.pdf',
                'file_type'    => 'pdf',
                'mime_type'    => 'application/pdf',
                'external_url' => 'https://example.com/guia-de-marca-demo.pdf',
                'source'       => 'imported',
                'notes'        => 'Arquivo demo — não possui arquivo físico real.',
            ],
            [
                'name'         => 'Logo Principal — Demo.png',
                'file_type'    => 'image',
                'mime_type'    => 'image/png',
                'external_url' => 'https://placehold.co/400x200/0f172a/4a6cf7?text=Logo+Demo',
                'source'       => 'imported',
                'notes'        => 'Arquivo demo — placeholder de imagem.',
            ],
        ];

        foreach ($files as $fileData) {
            if (!ExternalFile::where('client_id', $client->id)->where('name', $fileData['name'])->exists()) {
                ExternalFile::create(array_merge($fileData, [
                    'client_id'   => $client->id,
                    'uploaded_by' => $admin->id,
                ]));
            }
        }
    }
}
