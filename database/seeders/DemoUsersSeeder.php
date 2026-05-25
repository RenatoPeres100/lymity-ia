<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('email', 'contato@lymity.local')->first();

        // ── Agency internal users ────────────────────────────
        $agencyUsers = [
            ['email' => 'agencia@lymity.local',  'name' => 'Admin Agência',       'role' => 'agencia_admin'],
            ['email' => 'social@lymity.local',   'name' => 'Social Media Team',   'role' => 'social_media'],
            ['email' => 'trafego@lymity.local',  'name' => 'Gestor de Tráfego',   'role' => 'gestor_trafego'],
            ['email' => 'seo@lymity.local',      'name' => 'SEO Specialist',      'role' => 'seo'],
            ['email' => 'copy@lymity.local',     'name' => 'Copywriter',          'role' => 'copywriter'],
            ['email' => 'designer@lymity.local', 'name' => 'Designer',            'role' => 'designer'],
        ];

        foreach ($agencyUsers as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'company_id' => $company?->id,
                    'name'       => $data['name'],
                    'email'      => $data['email'],
                    'password'   => Hash::make('password'),
                    'role'       => $data['role'],
                    'user_type'  => 'agency',
                    'status'     => 'active',
                ]
            );
        }

        // ── Second demo client ───────────────────────────────
        $clientB2B = Client::updateOrCreate(
            ['name' => 'Cliente Demo B2B'],
            [
                'company_id'        => $company?->id,
                'name'              => 'Cliente Demo B2B',
                'segment'           => 'Tecnologia B2B',
                'website'           => 'https://clienteb2b.local',
                'instagram'         => '@clienteb2b',
                'brand_voice'       => 'técnico, confiável, inovador e orientado a resultados',
                'target_audience'   => 'empresas de médio e grande porte que buscam soluções tecnológicas',
                'offer_description' => 'software de gestão empresarial e consultoria em TI',
                'status'            => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente2@lymity.local'],
            [
                'company_id' => $company?->id,
                'client_id'  => $clientB2B->id,
                'name'       => 'Admin Cliente B2B',
                'email'      => 'cliente2@lymity.local',
                'password'   => Hash::make('password'),
                'role'       => 'cliente_admin',
                'user_type'  => 'client',
                'status'     => 'active',
            ]
        );

        // ── Inactive user for security test ─────────────────
        User::updateOrCreate(
            ['email' => 'inativo@lymity.local'],
            [
                'company_id' => $company?->id,
                'name'       => 'Usuário Inativo Demo',
                'email'      => 'inativo@lymity.local',
                'password'   => Hash::make('password'),
                'role'       => 'viewer',
                'user_type'  => 'agency',
                'status'     => 'inactive',
            ]
        );
    }
}
