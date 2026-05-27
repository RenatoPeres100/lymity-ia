<?php

namespace Database\Seeders;

use App\Models\Client;
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

        // Admin Geral
        User::updateOrCreate(
            ['email' => 'admin@lymity.local'],
            [
                'company_id'        => $company->id,
                'name'              => 'Administrador Geral',
                'email'             => 'admin@lymity.local',
                'password'          => Hash::make('password'),
                'role'              => 'admin_geral',
                'user_type'         => 'internal',
                'job_title'         => 'Administrador Geral',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Admin Agência
        User::updateOrCreate(
            ['email' => 'agencia@lymity.local'],
            [
                'company_id'        => $company->id,
                'name'              => 'Admin Agência',
                'email'             => 'agencia@lymity.local',
                'password'          => Hash::make('password'),
                'role'              => 'agencia_admin',
                'user_type'         => 'internal',
                'job_title'         => 'Gerente de Operações',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Social Media
        User::updateOrCreate(
            ['email' => 'social@lymity.local'],
            [
                'company_id'        => $company->id,
                'name'              => 'Social Media Team',
                'email'             => 'social@lymity.local',
                'password'          => Hash::make('password'),
                'role'              => 'social_media',
                'user_type'         => 'internal',
                'job_title'         => 'Especialista Social Media',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Copywriter
        User::updateOrCreate(
            ['email' => 'copy@lymity.local'],
            [
                'company_id'        => $company->id,
                'name'              => 'Copywriter',
                'email'             => 'copy@lymity.local',
                'password'          => Hash::make('password'),
                'role'              => 'copywriter',
                'user_type'         => 'internal',
                'job_title'         => 'Redator Especialista',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Blog Writer
        User::updateOrCreate(
            ['email' => 'blog@lymity.local'],
            [
                'company_id'        => $company->id,
                'name'              => 'Blog Writer',
                'email'             => 'blog@lymity.local',
                'password'          => Hash::make('password'),
                'role'              => 'blog_writer',
                'user_type'         => 'internal',
                'job_title'         => 'Escritor de Blog',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Meta App Reviewer
        User::updateOrCreate(
            ['email' => 'reviewer@lymity.com.br'],
            [
                'company_id'        => $company->id,
                'name'              => 'Meta App Review',
                'email'             => 'reviewer@lymity.com.br',
                'password'          => Hash::make('LymityReview2026!'),
                'role'              => 'admin_geral',
                'user_type'         => 'internal',
                'job_title'         => 'Meta App Reviewer',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Cliente demo
        $demoClient = Client::where('name', 'Cliente Demonstração')->first()
            ?? Client::first();

        User::updateOrCreate(
            ['email' => 'cliente@lymity.local'],
            [
                'company_id'        => $company->id,
                'client_id'         => $demoClient?->id,
                'name'              => 'Cliente Lymity',
                'email'             => 'cliente@lymity.local',
                'password'          => Hash::make('password'),
                'role'              => 'cliente_admin',
                'user_type'         => 'client',
                'job_title'         => 'Responsável pelo Cliente',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
