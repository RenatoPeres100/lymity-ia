<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['email' => 'contato@lymity.local'],
            [
                'name'       => 'Lymity AI Agency',
                'legal_name' => 'Lymity AI Agency',
                'email'      => 'contato@lymity.local',
                'website'    => 'https://lymity.local',
                'status'     => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@lymity.local'],
            [
                'company_id' => $company->id,
                'name'       => 'Administrador Geral',
                'email'      => 'admin@lymity.local',
                'password'   => Hash::make('password'),
                'role'       => 'admin_geral',
                'user_type'  => 'agency',
                'job_title'  => 'Administrador Geral',
                'status'     => 'active',
            ]
        );
    }
}
