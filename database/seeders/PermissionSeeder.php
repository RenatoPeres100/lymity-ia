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
            ['key' => 'users.view',               'name' => 'Ver usuários',               'module' => 'users'],
            ['key' => 'users.create',             'name' => 'Criar usuários',             'module' => 'users'],
            ['key' => 'users.update',             'name' => 'Editar usuários',            'module' => 'users'],
            ['key' => 'users.disable',            'name' => 'Inativar usuários',          'module' => 'users'],
            ['key' => 'users.reset_password',     'name' => 'Resetar senha de usuários',  'module' => 'users'],
            ['key' => 'users.manage_permissions', 'name' => 'Gerenciar permissões',       'module' => 'users'],
            // clients
            ['key' => 'clients.view',   'name' => 'Ver clientes',    'module' => 'clients'],
            ['key' => 'clients.create', 'name' => 'Criar clientes',  'module' => 'clients'],
            ['key' => 'clients.update', 'name' => 'Editar clientes', 'module' => 'clients'],
            ['key' => 'clients.delete', 'name' => 'Excluir clientes','module' => 'clients'],
            // settings
            ['key' => 'settings.view',   'name' => 'Ver configurações',   'module' => 'settings'],
            ['key' => 'settings.update', 'name' => 'Editar configurações','module' => 'settings'],
            // dashboard
            ['key' => 'dashboard.view', 'name' => 'Ver dashboard', 'module' => 'dashboard'],
            // operation
            ['key' => 'operation.view', 'name' => 'Ver operação', 'module' => 'operation'],
            // approvals
            ['key' => 'approvals.view',    'name' => 'Ver aprovações',   'module' => 'approvals'],
            ['key' => 'approvals.approve', 'name' => 'Aprovar',          'module' => 'approvals'],
            ['key' => 'approvals.reject',  'name' => 'Rejeitar',         'module' => 'approvals'],
            // blog
            ['key' => 'blog.view',    'name' => 'Ver blog',        'module' => 'blog'],
            ['key' => 'blog.create',  'name' => 'Criar posts',     'module' => 'blog'],
            ['key' => 'blog.approve', 'name' => 'Aprovar posts',   'module' => 'blog'],
            ['key' => 'blog.publish', 'name' => 'Publicar posts',  'module' => 'blog'],
            // social
            ['key' => 'social.view',     'name' => 'Ver social',          'module' => 'social'],
            ['key' => 'social.create',   'name' => 'Criar posts sociais', 'module' => 'social'],
            ['key' => 'social.approve',  'name' => 'Aprovar posts',       'module' => 'social'],
            ['key' => 'social.schedule', 'name' => 'Agendar posts',       'module' => 'social'],
            // instagram
            ['key' => 'instagram.connect', 'name' => 'Conectar Instagram', 'module' => 'instagram'],
            ['key' => 'instagram.publish', 'name' => 'Publicar Instagram', 'module' => 'instagram'],
            // content
            ['key' => 'content.view',    'name' => 'Ver conteúdo',      'module' => 'content'],
            ['key' => 'content.create',  'name' => 'Criar conteúdo',    'module' => 'content'],
            ['key' => 'content.approve', 'name' => 'Aprovar conteúdo',  'module' => 'content'],
            ['key' => 'content.publish', 'name' => 'Publicar conteúdo', 'module' => 'content'],
            // ai
            ['key' => 'ai_employees.view',   'name' => 'Ver funcionários IA',       'module' => 'ai'],
            ['key' => 'ai_employees.manage', 'name' => 'Gerenciar funcionários IA', 'module' => 'ai'],
            // logs
            ['key' => 'logs.view',   'name' => 'Ver logs',    'module' => 'logs'],
            // system
            ['key' => 'system.health', 'name' => 'Ver saúde do sistema', 'module' => 'system'],
            // ads
            ['key' => 'ads.view',    'name' => 'Ver campanhas',    'module' => 'ads'],
            ['key' => 'ads.create',  'name' => 'Criar campanhas',  'module' => 'ads'],
            ['key' => 'ads.approve', 'name' => 'Aprovar campanhas','module' => 'ads'],
            // seo
            ['key' => 'seo.view',    'name' => 'Ver SEO',    'module' => 'seo'],
            ['key' => 'seo.create',  'name' => 'Criar SEO',  'module' => 'seo'],
            ['key' => 'seo.approve', 'name' => 'Aprovar SEO','module' => 'seo'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['key' => $perm['key']],
                array_merge($perm, ['description' => null])
            );
        }
    }
}
