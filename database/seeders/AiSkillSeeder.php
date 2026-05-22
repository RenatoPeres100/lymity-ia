<?php

namespace Database\Seeders;

use App\Models\AiSkill;
use Illuminate\Database\Seeder;

class AiSkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['key' => 'content.create',       'name' => 'Criação de Conteúdo',        'module' => 'content'],
            ['key' => 'content.calendar',      'name' => 'Calendário Editorial',       'module' => 'content'],
            ['key' => 'social.strategy',       'name' => 'Estratégia Social',          'module' => 'social'],
            ['key' => 'copywriting',           'name' => 'Copywriting',                'module' => 'content'],
            ['key' => 'landing.copy',          'name' => 'Copy de Landing Page',       'module' => 'content'],
            ['key' => 'ads.copy',              'name' => 'Copy de Anúncios',           'module' => 'ads'],
            ['key' => 'ads.analysis',          'name' => 'Análise de Campanhas',       'module' => 'ads'],
            ['key' => 'google.ads',            'name' => 'Google Ads',                 'module' => 'ads'],
            ['key' => 'meta.ads',              'name' => 'Meta Ads',                   'module' => 'ads'],
            ['key' => 'seo.audit',             'name' => 'Auditoria SEO',              'module' => 'seo'],
            ['key' => 'blog.optimization',     'name' => 'Otimização de Blog',         'module' => 'seo'],
            ['key' => 'keyword.research',      'name' => 'Pesquisa de Palavras-chave', 'module' => 'seo'],
            ['key' => 'visual.direction',      'name' => 'Direção de Arte',            'module' => 'design'],
            ['key' => 'creative.briefing',     'name' => 'Briefing Criativo',          'module' => 'design'],
            ['key' => 'brand.consistency',     'name' => 'Consistência de Marca',      'module' => 'design'],
            ['key' => 'lead.qualification',    'name' => 'Qualificação de Leads',      'module' => 'crm'],
            ['key' => 'prospecting',           'name' => 'Prospecção',                 'module' => 'crm'],
            ['key' => 'crm.notes',             'name' => 'Notas de CRM',               'module' => 'crm'],
            ['key' => 'data.analysis',         'name' => 'Análise de Dados',           'module' => 'analytics'],
            ['key' => 'reports',               'name' => 'Relatórios',                 'module' => 'analytics'],
            ['key' => 'insights',              'name' => 'Insights',                   'module' => 'analytics'],
            ['key' => 'task.planning',         'name' => 'Planejamento de Tarefas',    'module' => 'management'],
            ['key' => 'workflow',              'name' => 'Gestão de Fluxo',            'module' => 'management'],
            ['key' => 'approvals',             'name' => 'Aprovações',                 'module' => 'management'],
        ];

        $categoryMap = [
            'content'    => 'Conteúdo',
            'social'     => 'Redes Sociais',
            'ads'        => 'Anúncios',
            'seo'        => 'SEO',
            'design'     => 'Design',
            'crm'        => 'CRM',
            'analytics'  => 'Analytics',
            'management' => 'Gestão',
        ];

        foreach ($skills as $skill) {
            $skill['category'] = $categoryMap[$skill['module']] ?? null;
            AiSkill::updateOrCreate(['key' => $skill['key']], $skill);
        }
    }
}
