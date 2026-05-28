<?php

namespace App\Services\Users;

class RolePermissionPreset
{
    /**
     * Default permission keys per role.
     * admin_geral bypasses all checks at runtime — no preset needed.
     */
    private static array $presets = [

        // admin_geral: runtime bypass, no preset needed
        'admin_geral' => [],

        // Cliente: main user of a client company — receives client.* permissions via UserPermissionPresetService
        'cliente' => [
            'client.dashboard.view',
            'client.approvals.view',
            'client.approvals.approve',
            'client.approvals.comment',
            'client.blog.view',
            'client.blog.create',
            'client.blog.update',
            'client.blog.approve',
            'client.blog.schedule',
            'client.brand_context.view',
            'client.brand_context.update',
            'client.routines.view',
            'client.routines.manage',
            'client.ai_employees.view',
            'client.ai_tasks.view',
            'client.ai_tasks.create',
            'client.ai_logs.view',
            'client.files.view',
            'client.files.upload',
            'client.users.view',
            'client.users.create',
            'client.users.update',
            'client.users.disable',
            'client.users.reset_password',
            'client.app.view',
        ],

        // Colaborador: limited — Cliente configures which permissions to grant
        'colaborador' => [
            'client.dashboard.view',
            'client.approvals.view',
            'client.blog.view',
            'client.files.view',
            'client.app.view',
        ],

        // ── Legacy roles kept for any seeded data still using old values ──
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

    /**
     * All permissions a Cliente can grant to a Colaborador (uses client.* keys).
     */
    public static array $colaboradorPermissions = [
        'client.dashboard.view'      => 'Ver painel',
        'client.approvals.view'      => 'Ver aprovações',
        'client.approvals.approve'   => 'Aprovar/rejeitar itens',
        'client.approvals.comment'   => 'Comentar aprovações',
        'client.blog.view'           => 'Ver posts de blog',
        'client.blog.create'         => 'Criar posts de blog',
        'client.blog.update'         => 'Editar posts de blog',
        'client.blog.approve'        => 'Aprovar posts de blog',
        'client.brand_context.view'  => 'Ver brand context',
        'client.ai_employees.view'   => 'Ver funcionários IA',
        'client.ai_tasks.view'       => 'Ver tarefas IA',
        'client.ai_logs.view'        => 'Ver logs IA',
        'client.files.view'          => 'Ver arquivos',
        'client.files.upload'        => 'Enviar arquivos',
        'client.app.view'            => 'Acessar App Mobile',
    ];

    public static function forRole(string $role): array
    {
        return self::$presets[$role] ?? [];
    }

    public static function allRoles(): array
    {
        return ['admin_geral', 'cliente', 'colaborador'];
    }

    public static function label(string $role): string
    {
        return match ($role) {
            'admin_geral'  => 'Admin Geral — acesso total ao sistema',
            'cliente'      => 'Cliente — gerencia o próprio ambiente e colaboradores',
            'colaborador'  => 'Colaborador — permissões definidas pelo Cliente',
            'ai_employee'  => 'Funcionário IA — sem permissões de login',
            // Legacy (for display only)
            'agencia_admin'       => 'Admin Geral',
            'agencia_operador'    => 'Admin Geral',
            'social_media'        => 'Admin Geral',
            'copywriter'          => 'Admin Geral',
            'blog_writer'         => 'Admin Geral',
            'seo'                 => 'Admin Geral',
            'designer'            => 'Admin Geral',
            'gestor_trafego'      => 'Admin Geral',
            'cliente_admin'       => 'Cliente',
            'cliente_colaborador' => 'Colaborador',
            'viewer'              => 'Colaborador',
            default               => $role,
        };
    }
}
