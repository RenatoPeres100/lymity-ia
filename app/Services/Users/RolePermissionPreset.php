<?php

namespace App\Services\Users;

class RolePermissionPreset
{
    /**
     * Default permission keys per role.
     * admin_geral bypasses all checks at runtime — no preset needed.
     */
    private static array $presets = [

        'agencia_admin' => [
            'users.view', 'users.create', 'users.update', 'users.disable',
            'users.reset_password', 'users.manage_permissions',
            'clients.view', 'clients.create', 'clients.update',
            'dashboard.view', 'operation.view',
            'approvals.view', 'approvals.approve', 'approvals.reject',
            'blog.view', 'blog.create', 'blog.approve', 'blog.publish',
            'social.view', 'social.create', 'social.approve', 'social.schedule',
            'instagram.connect', 'instagram.publish',
            'content.view', 'content.create', 'content.approve', 'content.publish',
            'ai_employees.view', 'ai_employees.manage',
            'logs.view', 'system.health',
            'seo.view', 'seo.create', 'seo.approve',
        ],

        'agencia_operador' => [
            'dashboard.view', 'operation.view',
            'approvals.view',
            'blog.view', 'blog.create',
            'social.view', 'social.create',
            'content.view', 'content.create',
            'logs.view',
        ],

        'social_media' => [
            'dashboard.view', 'operation.view',
            'social.view', 'social.create', 'social.approve', 'social.schedule',
            'instagram.connect', 'instagram.publish',
            'content.view', 'content.create',
            'approvals.view',
        ],

        'copywriter' => [
            'dashboard.view',
            'blog.view', 'blog.create',
            'content.view', 'content.create',
            'approvals.view',
        ],

        'blog_writer' => [
            'dashboard.view',
            'blog.view', 'blog.create',
            'content.view', 'content.create',
            'approvals.view',
        ],

        'seo' => [
            'dashboard.view',
            'seo.view', 'seo.create',
            'content.view',
            'blog.view',
            'logs.view',
        ],

        'designer' => [
            'dashboard.view',
            'content.view', 'content.create',
            'social.view',
            'approvals.view',
        ],

        'gestor_trafego' => [
            'dashboard.view', 'operation.view',
            'approvals.view',
            'content.view',
            'logs.view',
        ],

        // Cliente admin: manages their area + can add collaborators
        'cliente_admin' => [
            'users.view', 'users.create', 'users.disable',
            'approvals.view', 'approvals.approve',
            'blog.view',
            'social.view', 'social.approve',
            'content.view',
            'instagram.connect',
        ],

        // Colaborador: limited read + basic operations
        'cliente_colaborador' => [
            'approvals.view',
            'blog.view',
            'social.view',
            'content.view',
        ],

        'viewer' => [
            'dashboard.view',
            'content.view',
        ],

        'ai_employee' => [],
    ];

    public static function forRole(string $role): array
    {
        return self::$presets[$role] ?? [];
    }

    public static function allRoles(): array
    {
        return array_keys(self::$presets);
    }

    public static function label(string $role): string
    {
        return match ($role) {
            'agencia_admin'       => 'Admin Agência — acesso total operacional',
            'agencia_operador'    => 'Operador — criação e visualização de conteúdo',
            'social_media'        => 'Social Media — gestão completa de posts e Instagram',
            'copywriter'          => 'Copywriter — criação de conteúdo e blog',
            'blog_writer'         => 'Blog Writer — criação e edição de posts',
            'seo'                 => 'SEO — gestão de palavras-chave e auditoria',
            'designer'            => 'Designer — visualização e criação de conteúdo',
            'gestor_trafego'      => 'Gestor de Tráfego — operação e relatórios',
            'cliente_admin'       => 'Admin Cliente — área do cliente + gestão de colaboradores',
            'cliente_colaborador' => 'Colaborador — visualização da área do cliente',
            'viewer'              => 'Visualizador — acesso somente leitura',
            'ai_employee'         => 'Funcionário IA — sem permissões de login',
            default               => $role,
        };
    }
}
