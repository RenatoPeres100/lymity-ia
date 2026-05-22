<?php

namespace Database\Seeders;

use App\Models\AiEmployee;
use App\Models\AiMemory;
use Illuminate\Database\Seeder;

class AiMemorySeeder extends Seeder
{
    public function run(): void
    {
        $memories = [
            [
                'slug'        => 'social-media-ia',
                'memory_type' => 'rule',
                'title'       => 'Tom de voz da agência',
                'content'     => 'A comunicação deve ser profissional mas próxima. Evitar jargões técnicos excessivos. Preferir frases curtas e diretas. Emojis são bem-vindos apenas quando o contexto do cliente permitir.',
                'weight'      => 8,
            ],
            [
                'slug'        => 'social-media-ia',
                'memory_type' => 'preference',
                'title'       => 'Formatos preferidos de posts',
                'content'     => 'Posts do tipo carrossel têm maior engajamento. Sempre sugerir versão para feed e stories. Incluir CTA (call-to-action) claro no último slide ou na legenda principal.',
                'weight'      => 6,
            ],
            [
                'slug'        => 'seo-ia',
                'memory_type' => 'insight',
                'title'       => 'Palavras-chave de maior volume no nicho',
                'content'     => 'Termos como "agência digital", "marketing digital", "criação de site" e "gestão de redes sociais" têm volume alto. Priorizar long-tails para conversão mais qualificada.',
                'weight'      => 7,
            ],
            [
                'slug'        => 'gestor-de-trafego-ia',
                'memory_type' => 'rule',
                'title'       => 'Regra de ouro para campanhas',
                'content'     => 'NUNCA alterar verba, segmentação ou criativos de campanhas ativas sem aprovação explícita do responsável. Toda sugestão de otimização deve ser apresentada como rascunho para revisão humana.',
                'weight'      => 10,
            ],
            [
                'slug'        => 'copywriter-ia',
                'memory_type' => 'preference',
                'title'       => 'Estrutura de headline preferida',
                'content'     => 'Headlines que funcionam melhor: [Número] + [Benefício] + [Prazo]. Ex: "3 estratégias para dobrar suas vendas em 30 dias". Evitar promessas impossíveis ou exageradas.',
                'weight'      => 6,
            ],
            [
                'slug'        => 'analista-ia',
                'memory_type' => 'insight',
                'title'       => 'Métricas prioritárias de acompanhamento',
                'content'     => 'Focar em: CAC (Custo de Aquisição de Cliente), ROAS (Retorno sobre Investimento em Anúncios), taxa de conversão de leads e engajamento orgânico. Relatórios semanais são esperados toda segunda-feira.',
                'weight'      => 8,
            ],
            [
                'slug'        => 'gerente-de-projeto-ia',
                'memory_type' => 'rule',
                'title'       => 'Fluxo de aprovação obrigatório',
                'content'     => 'Todo output gerado por funcionário IA deve passar por aprovação humana antes de qualquer ação externa. Prioridade: conteúdo para publicação → alterações em campanhas → envio de mensagens. Registrar log em todas as etapas.',
                'weight'      => 10,
            ],
        ];

        foreach ($memories as $data) {
            $employee = AiEmployee::where('slug', $data['slug'])->first();
            if (!$employee) continue;

            AiMemory::updateOrCreate(
                ['ai_employee_id' => $employee->id, 'title' => $data['title']],
                [
                    'ai_employee_id' => $employee->id,
                    'memory_type'    => $data['memory_type'],
                    'title'          => $data['title'],
                    'content'        => $data['content'],
                    'weight'         => $data['weight'],
                ]
            );
        }
    }
}
