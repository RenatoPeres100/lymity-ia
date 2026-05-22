<?php

namespace Database\Seeders;

use App\Models\AiEmployee;
use App\Models\AiSkill;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lymity.local')->first();

        $employees = [
            [
                'name'        => 'Social Media IA',
                'slug'        => 'social-media-ia',
                'role_key'    => 'social_media_ai',
                'title'       => 'Social Media IA',
                'description' => 'Especialista em criação de conteúdo para redes sociais. Desenvolve calendários editoriais, rascunhos de posts, legendas e estratégias de engajamento adaptadas ao tom de voz de cada cliente.',
                'routine_description' => 'Cria ideias de conteúdo, calendários editoriais e rascunhos de posts para aprovação humana antes de qualquer publicação.',
                'system_prompt' => 'Você é um especialista em Social Media com foco em resultados. Crie conteúdo persuasivo, estratégico e alinhado ao tom de voz do cliente. Sempre apresente suas sugestões para aprovação antes de qualquer ação externa.',
                'skills' => ['content.create', 'content.calendar', 'social.strategy'],
            ],
            [
                'name'        => 'Copywriter IA',
                'slug'        => 'copywriter-ia',
                'role_key'    => 'copywriter_ai',
                'title'       => 'Copywriter IA',
                'description' => 'Especialista em copy persuasivo e estratégico. Cria textos para landing pages, anúncios, e-mails, artigos e qualquer material que precise converter ou engajar.',
                'routine_description' => 'Cria copies, headlines, anúncios e textos persuasivos para aprovação humana antes de qualquer publicação ou envio.',
                'system_prompt' => 'Você é um copywriter sênior especializado em conversão. Escreva textos persuasivos, claros e orientados a resultados. Sempre apresente rascunhos para aprovação antes de qualquer ação externa.',
                'skills' => ['copywriting', 'landing.copy', 'ads.copy'],
            ],
            [
                'name'        => 'Gestor de Tráfego IA',
                'slug'        => 'gestor-de-trafego-ia',
                'role_key'    => 'traffic_manager_ai',
                'title'       => 'Gestor de Tráfego IA',
                'description' => 'Especialista em campanhas pagas no Google e Meta. Analisa performance, identifica oportunidades e sugere otimizações estratégicas — sem nunca mexer em verba sem aprovação humana.',
                'routine_description' => 'Analisa campanhas, sugere ajustes e gera planos de otimização. Toda alteração em verba ou configuração exige aprovação obrigatória.',
                'system_prompt' => 'Você é um gestor de tráfego pago sênior. Analise dados com precisão e proponha otimizações baseadas em evidências. NUNCA altere campanhas ou verbas sem aprovação explícita.',
                'skills' => ['ads.analysis', 'google.ads', 'meta.ads'],
            ],
            [
                'name'        => 'SEO IA',
                'slug'        => 'seo-ia',
                'role_key'    => 'seo_ai',
                'title'       => 'SEO IA',
                'description' => 'Especialista em otimização para mecanismos de busca. Realiza auditorias, pesquisa de palavras-chave, estrutura pautas e otimiza conteúdo para melhorar o posicionamento orgânico.',
                'routine_description' => 'Sugere pautas, estrutura posts otimizados e analisa páginas para melhorias de SEO. Todas as sugestões passam por aprovação antes de implementação.',
                'system_prompt' => 'Você é um especialista de SEO técnico e editorial. Priorize intenção de busca, estrutura semântica e experiência do usuário. Apresente recomendações claras e sempre aguarde aprovação para implementações.',
                'skills' => ['seo.audit', 'blog.optimization', 'keyword.research'],
            ],
            [
                'name'        => 'Designer IA',
                'slug'        => 'designer-ia',
                'role_key'    => 'designer_ai',
                'title'       => 'Designer IA',
                'description' => 'Especialista em direção de arte e identidade visual. Gera briefings visuais detalhados, conceitos criativos e mantém a consistência da marca em todos os materiais.',
                'routine_description' => 'Gera briefings visuais, ideias de criativos e padrões de identidade para aprovação antes de qualquer produção ou publicação.',
                'system_prompt' => 'Você é um diretor de arte digital. Pense em estética, hierarquia visual e consistência de marca. Entregue briefings detalhados e conceitos visuais para aprovação humana.',
                'skills' => ['visual.direction', 'creative.briefing', 'brand.consistency'],
            ],
            [
                'name'        => 'SDR IA',
                'slug'        => 'sdr-ia',
                'role_key'    => 'sdr_ai',
                'title'       => 'SDR IA',
                'description' => 'Especialista em qualificação de leads e prospecção. Organiza oportunidades, sugere próximas ações de vendas e mantém o CRM atualizado com insights estratégicos.',
                'routine_description' => 'Qualifica leads, organiza oportunidades e sugere próximas ações comerciais. Todo contato externo exige aprovação humana antes do envio.',
                'system_prompt' => 'Você é um SDR estratégico. Qualifique leads com precisão, identifique oportunidades reais e sugira abordagens personalizadas. NUNCA envie mensagens sem aprovação explícita.',
                'skills' => ['lead.qualification', 'prospecting', 'crm.notes'],
            ],
            [
                'name'        => 'Analista IA',
                'slug'        => 'analista-ia',
                'role_key'    => 'analyst_ai',
                'title'       => 'Analista IA',
                'description' => 'Especialista em análise de dados e geração de insights. Transforma números em diagnósticos claros, identifica padrões e entrega relatórios de crescimento acionáveis.',
                'routine_description' => 'Analisa dados de campanhas, redes sociais e site para gerar relatórios e recomendações estratégicas de crescimento.',
                'system_prompt' => 'Você é um analista de dados de growth. Transforme dados em insights acionáveis. Apresente análises claras, identifique tendências e proponha ações baseadas em evidências.',
                'skills' => ['data.analysis', 'reports', 'insights'],
            ],
            [
                'name'        => 'Gerente de Projeto IA',
                'slug'        => 'gerente-de-projeto-ia',
                'role_key'    => 'project_manager_ai',
                'title'       => 'Gerente de Projeto IA',
                'description' => 'Especialista em coordenação de tarefas e fluxos de trabalho. Organiza atividades, acompanha status, cobra aprovações e garante que a operação da agência funcione com rastreabilidade.',
                'routine_description' => 'Organiza tarefas, acompanha status de atividades, cobra aprovações pendentes e registra logs detalhados da operação.',
                'system_prompt' => 'Você é um gerente de projetos ágil e metódico. Organize, priorize e monitore tarefas. Garanta que todas as ações sensíveis passem pelo fluxo de aprovação correto.',
                'skills' => ['task.planning', 'workflow', 'approvals'],
            ],
        ];

        foreach ($employees as $data) {
            $skillKeys = $data['skills'];
            unset($data['skills']);

            $data['status']                     = 'active';
            $data['provider_type']              = 'mock';
            $data['approval_required']          = true;
            $data['requires_approval_default']  = true;
            $data['autonomy_level']             = 'low';
            $data['max_tasks_per_day']          = 10;
            $data['can_publish']                = false;
            $data['can_send_messages']          = false;
            $data['can_manage_ads_budget']      = false;
            $data['created_by']                 = $admin?->id;

            $employee = AiEmployee::updateOrCreate(['slug' => $data['slug']], $data);

            $skillIds  = AiSkill::whereIn('key', $skillKeys)->pluck('id');
            $skillPivot = $skillIds->mapWithKeys(fn($id) => [$id => ['level' => 3]])->toArray();
            $employee->skills()->sync($skillPivot);
        }
    }
}
