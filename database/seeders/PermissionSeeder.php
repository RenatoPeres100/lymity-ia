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
            ['key' => 'clients.view',     'name' => 'Ver clientes',       'module' => 'clients'],
            ['key' => 'clients.create',   'name' => 'Criar clientes',     'module' => 'clients'],
            ['key' => 'clients.update',   'name' => 'Editar clientes',    'module' => 'clients'],
            ['key' => 'clients.disable',  'name' => 'Desativar clientes', 'module' => 'clients'],
            ['key' => 'clients.activate', 'name' => 'Ativar clientes',    'module' => 'clients'],
            ['key' => 'clients.archive',  'name' => 'Arquivar clientes',  'module' => 'clients'],
            ['key' => 'clients.delete',   'name' => 'Excluir clientes',   'module' => 'clients'],
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
            ['key' => 'social.view',     'name' => 'Ver social',           'module' => 'social'],
            ['key' => 'social.create',   'name' => 'Criar posts sociais',  'module' => 'social'],
            ['key' => 'social.update',   'name' => 'Editar posts sociais', 'module' => 'social'],
            ['key' => 'social.approve',  'name' => 'Aprovar posts',        'module' => 'social'],
            ['key' => 'social.schedule', 'name' => 'Agendar posts',        'module' => 'social'],
            ['key' => 'social.publish',  'name' => 'Publicar posts sociais','module' => 'social'],
            ['key' => 'social.archive',  'name' => 'Arquivar posts sociais','module' => 'social'],
            // instagram
            ['key' => 'instagram.view',       'name' => 'Ver Instagram',         'module' => 'instagram'],
            ['key' => 'instagram.connect',    'name' => 'Conectar Instagram',    'module' => 'instagram'],
            ['key' => 'instagram.disconnect', 'name' => 'Desconectar Instagram', 'module' => 'instagram'],
            ['key' => 'instagram.check',      'name' => 'Verificar Instagram',   'module' => 'instagram'],
            ['key' => 'instagram.publish',    'name' => 'Publicar Instagram',    'module' => 'instagram'],
            // threads
            ['key' => 'social.threads.view',       'name' => 'Ver Threads',              'module' => 'threads'],
            ['key' => 'social.threads.connect',    'name' => 'Conectar Threads',         'module' => 'threads'],
            ['key' => 'social.threads.disconnect', 'name' => 'Desconectar Threads',      'module' => 'threads'],
            ['key' => 'social.threads.create',     'name' => 'Criar posts Threads',      'module' => 'threads'],
            ['key' => 'social.threads.update',     'name' => 'Editar posts Threads',     'module' => 'threads'],
            ['key' => 'social.threads.approve',    'name' => 'Aprovar posts Threads',    'module' => 'threads'],
            ['key' => 'social.threads.schedule',   'name' => 'Agendar posts Threads',    'module' => 'threads'],
            ['key' => 'social.threads.publish',    'name' => 'Publicar posts Threads',   'module' => 'threads'],
            ['key' => 'social.threads.logs.view',  'name' => 'Ver logs Threads',         'module' => 'threads'],
            // publishing queue
            ['key' => 'publishing_queue.view',   'name' => 'Ver fila de publicação',       'module' => 'publishing_queue'],
            ['key' => 'publishing_queue.manage', 'name' => 'Gerenciar fila de publicação', 'module' => 'publishing_queue'],
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
            // ai_images
            ['key' => 'ai_images.view',     'name' => 'Ver Imagens IA',               'module' => 'ai_images'],
            ['key' => 'ai_images.create',   'name' => 'Criar geração de imagens IA',  'module' => 'ai_images'],
            ['key' => 'ai_images.update',   'name' => 'Editar geração de imagens IA', 'module' => 'ai_images'],
            ['key' => 'ai_images.delete',   'name' => 'Excluir imagens IA',           'module' => 'ai_images'],
            ['key' => 'ai_images.attach',   'name' => 'Vincular imagens IA',          'module' => 'ai_images'],
            ['key' => 'ai_images.settings', 'name' => 'Configurar Imagens IA',        'module' => 'ai_images'],
            ['key' => 'ai_images.logs',     'name' => 'Ver logs de Imagens IA',       'module' => 'ai_images'],
            // ads
            ['key' => 'ads.view',    'name' => 'Ver campanhas',    'module' => 'ads'],
            ['key' => 'ads.create',  'name' => 'Criar campanhas',  'module' => 'ads'],
            ['key' => 'ads.approve', 'name' => 'Aprovar campanhas','module' => 'ads'],
            // seo
            ['key' => 'seo.view',    'name' => 'Ver SEO',    'module' => 'seo'],
            ['key' => 'seo.create',  'name' => 'Criar SEO',  'module' => 'seo'],
            ['key' => 'seo.approve', 'name' => 'Aprovar SEO','module' => 'seo'],

            // ── CLIENT PANEL permissions (prefixo client.) ─────────────────
            // client.dashboard
            ['key' => 'client.dashboard.view',          'name' => 'Ver painel do cliente',          'module' => 'client_dashboard'],

            // client.approvals
            ['key' => 'client.approvals.view',          'name' => 'Ver aprovações (cliente)',        'module' => 'client_approvals'],
            ['key' => 'client.approvals.approve',       'name' => 'Aprovar itens (cliente)',         'module' => 'client_approvals'],
            ['key' => 'client.approvals.comment',       'name' => 'Comentar aprovações (cliente)',   'module' => 'client_approvals'],

            // client.blog
            ['key' => 'client.blog.view',               'name' => 'Ver blog (cliente)',              'module' => 'client_blog'],
            ['key' => 'client.blog.create',             'name' => 'Criar posts de blog (cliente)',   'module' => 'client_blog'],
            ['key' => 'client.blog.update',             'name' => 'Editar posts de blog (cliente)',  'module' => 'client_blog'],
            ['key' => 'client.blog.approve',            'name' => 'Aprovar posts de blog (cliente)', 'module' => 'client_blog'],
            ['key' => 'client.blog.schedule',           'name' => 'Agendar posts de blog (cliente)', 'module' => 'client_blog'],
            ['key' => 'client.blog.publish',            'name' => 'Publicar blog (cliente)',         'module' => 'client_blog'],

            // client.brand_context
            ['key' => 'client.brand_context.view',      'name' => 'Ver brand context (cliente)',     'module' => 'client_brand_context'],
            ['key' => 'client.brand_context.update',    'name' => 'Editar brand context (cliente)',  'module' => 'client_brand_context'],

            // client.routines
            ['key' => 'client.routines.view',           'name' => 'Ver rotinas (cliente)',           'module' => 'client_routines'],
            ['key' => 'client.routines.manage',         'name' => 'Gerenciar rotinas (cliente)',     'module' => 'client_routines'],

            // client.ai_employees
            ['key' => 'client.ai_employees.view',       'name' => 'Ver funcionários IA (cliente)',   'module' => 'client_ai'],
            ['key' => 'client.ai_employees.use',        'name' => 'Usar funcionários IA (cliente)',  'module' => 'client_ai'],

            // client.ai_tasks
            ['key' => 'client.ai_tasks.view',           'name' => 'Ver tarefas IA (cliente)',        'module' => 'client_ai'],
            ['key' => 'client.ai_tasks.create',         'name' => 'Criar tarefas IA (cliente)',      'module' => 'client_ai'],

            // client.ai_logs
            ['key' => 'client.ai_logs.view',            'name' => 'Ver logs IA (cliente)',           'module' => 'client_ai'],

            // client.files
            ['key' => 'client.files.view',              'name' => 'Ver arquivos (cliente)',          'module' => 'client_files'],
            ['key' => 'client.files.upload',            'name' => 'Enviar arquivos (cliente)',       'module' => 'client_files'],

            // client.users
            ['key' => 'client.users.view',              'name' => 'Ver usuários (cliente)',          'module' => 'client_users'],
            ['key' => 'client.users.create',            'name' => 'Criar usuários (cliente)',        'module' => 'client_users'],
            ['key' => 'client.users.update',            'name' => 'Editar usuários (cliente)',       'module' => 'client_users'],
            ['key' => 'client.users.disable',           'name' => 'Inativar usuários (cliente)',     'module' => 'client_users'],
            ['key' => 'client.users.reset_password',    'name' => 'Resetar senha (cliente)',         'module' => 'client_users'],

            // client.app
            ['key' => 'client.app.view',                'name' => 'Acessar App Mobile',              'module' => 'client_app'],

            // client.social
            ['key' => 'client.social.view',     'name' => 'Ver Social Media (cliente)',    'module' => 'client_social'],
            ['key' => 'client.social.create',   'name' => 'Criar posts sociais (cliente)', 'module' => 'client_social'],
            ['key' => 'client.social.approve',  'name' => 'Aprovar posts (cliente)',       'module' => 'client_social'],
            ['key' => 'client.social.schedule', 'name' => 'Agendar posts (cliente)',       'module' => 'client_social'],
            ['key' => 'client.social.comment',  'name' => 'Comentar posts (cliente)',      'module' => 'client_social'],

            // agent_tasks
            ['key' => 'agent_tasks.view',    'name' => 'Ver tarefas operacionais',    'module' => 'agent_tasks'],
            ['key' => 'agent_tasks.create',  'name' => 'Criar tarefas operacionais',  'module' => 'agent_tasks'],
            ['key' => 'agent_tasks.update',  'name' => 'Editar tarefas operacionais', 'module' => 'agent_tasks'],
            ['key' => 'agent_tasks.delete',  'name' => 'Excluir tarefas operacionais','module' => 'agent_tasks'],
            ['key' => 'agent_tasks.run',     'name' => 'Executar tarefas operacionais','module' => 'agent_tasks'],
            ['key' => 'agent_tasks.pause',   'name' => 'Pausar tarefas operacionais', 'module' => 'agent_tasks'],
            ['key' => 'agent_tasks.archive', 'name' => 'Arquivar tarefas operacionais','module' => 'agent_tasks'],

            // ai_memory
            ['key' => 'ai_memory.view',   'name' => 'Ver memória IA',      'module' => 'ai_memory'],
            ['key' => 'ai_memory.create', 'name' => 'Criar memória IA',    'module' => 'ai_memory'],
            ['key' => 'ai_memory.update', 'name' => 'Editar memória IA',   'module' => 'ai_memory'],
            ['key' => 'ai_memory.manage', 'name' => 'Gerenciar memória IA','module' => 'ai_memory'],

            // ai_usage
            ['key' => 'ai_usage.view', 'name' => 'Ver uso/consumo de IA', 'module' => 'ai_usage'],

            // generated_packages
            ['key' => 'generated_packages.view',    'name' => 'Ver pacotes gerados',    'module' => 'packages'],
            ['key' => 'generated_packages.approve', 'name' => 'Aprovar pacotes gerados','module' => 'packages'],
            ['key' => 'generated_packages.update',  'name' => 'Editar pacotes gerados', 'module' => 'packages'],

            // prospecting / CRM / SDR IA
            ['key' => 'prospecting.view',                   'name' => 'Ver prospecção',                          'module' => 'prospecting'],
            ['key' => 'prospecting.create',                 'name' => 'Criar leads',                             'module' => 'prospecting'],
            ['key' => 'prospecting.update',                 'name' => 'Editar leads',                            'module' => 'prospecting'],
            ['key' => 'prospecting.delete',                 'name' => 'Excluir leads',                           'module' => 'prospecting'],
            ['key' => 'prospecting.pipeline.view',          'name' => 'Ver pipeline de prospecção',              'module' => 'prospecting'],
            ['key' => 'prospecting.pipeline.manage',        'name' => 'Gerenciar pipeline de prospecção',        'module' => 'prospecting'],
            ['key' => 'prospecting.activities.view',        'name' => 'Ver atividades de prospecção',            'module' => 'prospecting'],
            ['key' => 'prospecting.activities.create',      'name' => 'Criar atividades de prospecção',          'module' => 'prospecting'],
            ['key' => 'prospecting.activities.update',      'name' => 'Editar atividades de prospecção',         'module' => 'prospecting'],
            ['key' => 'prospecting.ai.analyze',             'name' => 'Analisar lead com IA',                    'module' => 'prospecting'],
            ['key' => 'prospecting.ai.generate_message',    'name' => 'Gerar mensagem IA para lead',             'module' => 'prospecting'],
            ['key' => 'prospecting.ai.suggest_next_action', 'name' => 'Sugerir próxima ação com IA',             'module' => 'prospecting'],
            ['key' => 'prospecting.ai.qualify',             'name' => 'Qualificar lead com IA',                  'module' => 'prospecting'],
            ['key' => 'prospecting.logs.view',              'name' => 'Ver logs de prospecção',                  'module' => 'prospecting'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['key' => $perm['key']],
                array_merge($perm, ['description' => null])
            );
        }
    }
}
