<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // users
            ['key' => 'users.view',            'name' => 'Ver usuários',            'module' => 'users'],
            ['key' => 'users.create',          'name' => 'Criar usuários',          'module' => 'users'],
            ['key' => 'users.update',          'name' => 'Editar usuários',         'module' => 'users'],
            ['key' => 'users.delete',          'name' => 'Excluir usuários',        'module' => 'users'],
            // clients
            ['key' => 'clients.view',          'name' => 'Ver clientes',            'module' => 'clients'],
            ['key' => 'clients.create',        'name' => 'Criar clientes',          'module' => 'clients'],
            ['key' => 'clients.update',        'name' => 'Editar clientes',         'module' => 'clients'],
            ['key' => 'clients.delete',        'name' => 'Excluir clientes',        'module' => 'clients'],
            // settings
            ['key' => 'settings.view',         'name' => 'Ver configurações',       'module' => 'settings'],
            ['key' => 'settings.update',       'name' => 'Editar configurações',    'module' => 'settings'],
            // dashboard
            ['key' => 'dashboard.view',        'name' => 'Ver dashboard',           'module' => 'dashboard'],
            // content
            ['key' => 'content.view',          'name' => 'Ver conteúdo',            'module' => 'content'],
            ['key' => 'content.create',        'name' => 'Criar conteúdo',          'module' => 'content'],
            ['key' => 'content.approve',       'name' => 'Aprovar conteúdo',        'module' => 'content'],
            ['key' => 'content.publish',       'name' => 'Publicar conteúdo',       'module' => 'content'],
            // ai
            ['key' => 'ai_employees.view',     'name' => 'Ver funcionários IA',     'module' => 'ai'],
            ['key' => 'ai_employees.manage',   'name' => 'Gerenciar funcionários IA','module' => 'ai'],
            // approvals
            ['key' => 'approvals.view',        'name' => 'Ver aprovações',          'module' => 'approvals'],
            ['key' => 'approvals.approve',     'name' => 'Aprovar',                 'module' => 'approvals'],
            ['key' => 'approvals.reject',      'name' => 'Rejeitar',                'module' => 'approvals'],
            // logs
            ['key' => 'logs.view',             'name' => 'Ver logs',                'module' => 'logs'],
            // ads
            ['key' => 'ads.view',              'name' => 'Ver campanhas',           'module' => 'ads'],
            ['key' => 'ads.create',            'name' => 'Criar campanhas',         'module' => 'ads'],
            ['key' => 'ads.approve',           'name' => 'Aprovar campanhas',       'module' => 'ads'],
            // seo
            ['key' => 'seo.view',              'name' => 'Ver SEO',                 'module' => 'seo'],
            ['key' => 'seo.create',            'name' => 'Criar SEO',               'module' => 'seo'],
            ['key' => 'seo.approve',           'name' => 'Aprovar SEO',             'module' => 'seo'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['key' => $perm['key']], array_merge($perm, ['description' => null]));
        }
    }
}
