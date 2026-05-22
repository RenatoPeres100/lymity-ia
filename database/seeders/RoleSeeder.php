<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['key' => 'admin_geral',         'name' => 'Admin Geral',          'description' => 'Acesso total ao sistema'],
            ['key' => 'agencia_admin',        'name' => 'Admin da Agência',     'description' => 'Administrador da agência'],
            ['key' => 'agencia_operador',     'name' => 'Operador da Agência',  'description' => 'Operador interno'],
            ['key' => 'social_media',         'name' => 'Social Media',         'description' => 'Responsável por redes sociais'],
            ['key' => 'gestor_trafego',       'name' => 'Gestor de Tráfego',    'description' => 'Gestor de campanhas pagas'],
            ['key' => 'seo',                  'name' => 'SEO',                  'description' => 'Especialista em SEO'],
            ['key' => 'copywriter',           'name' => 'Copywriter',           'description' => 'Criador de conteúdo'],
            ['key' => 'designer',             'name' => 'Designer',             'description' => 'Designer visual'],
            ['key' => 'cliente_admin',        'name' => 'Admin do Cliente',     'description' => 'Administrador da conta do cliente'],
            ['key' => 'cliente_colaborador',  'name' => 'Colaborador Cliente',  'description' => 'Colaborador da conta do cliente'],
            ['key' => 'viewer',               'name' => 'Visualizador',         'description' => 'Apenas visualização'],
            ['key' => 'ai_employee',          'name' => 'Funcionário IA',       'description' => 'Agente de inteligência artificial'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['key' => $role['key']], $role);
        }
    }
}
