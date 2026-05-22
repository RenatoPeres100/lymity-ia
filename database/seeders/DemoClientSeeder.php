<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoClientSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('email', 'contato@lymity.local')->first();

        $client = Client::updateOrCreate(
            ['name' => 'Cliente Demonstração'],
            [
                'company_id'        => $company?->id,
                'name'              => 'Cliente Demonstração',
                'segment'           => 'Clínica de Estética',
                'website'           => 'https://cliente-demo.local',
                'instagram'         => '@clientedemo',
                'brand_voice'       => 'profissional, elegante, direto e orientado a resultado',
                'target_audience'   => 'mulheres e homens interessados em estética, bem-estar e transformação pessoal',
                'offer_description' => 'serviços premium de estética e emagrecimento',
                'status'            => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@demo.local'],
            [
                'company_id' => $company?->id,
                'client_id'  => $client->id,
                'name'       => 'Admin Cliente Demo',
                'email'      => 'cliente@demo.local',
                'password'   => Hash::make('password'),
                'role'       => 'cliente_admin',
                'user_type'  => 'client',
                'job_title'  => 'Responsável pela conta',
                'status'     => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@lymity.local'],
            [
                'company_id' => $company?->id,
                'client_id'  => $client->id,
                'name'       => 'Cliente Lymity',
                'email'      => 'cliente@lymity.local',
                'password'   => Hash::make('password'),
                'role'       => 'cliente_admin',
                'user_type'  => 'client',
                'job_title'  => 'Administrador',
                'status'     => 'active',
            ]
        );
    }
}
