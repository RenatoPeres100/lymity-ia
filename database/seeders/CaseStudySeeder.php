<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use Illuminate\Database\Seeder;

class CaseStudySeeder extends Seeder
{
    public function run(): void
    {
        $cases = [
            [
                'title'              => 'Clínica de estética premium dobrou conversões em 90 dias',
                'slug'               => 'clinica-estetica-premium-dobrou-conversoes',
                'client_name'        => 'Clínica Estética Premium (confidencial)',
                'industry'           => 'Saúde e Estética',
                'challenge'          => 'A clínica investia em tráfego pago mas não conseguia converter visitas em agendamentos. O custo por lead era alto e a taxa de conversão estava abaixo de 2%. Além disso, a presença orgânica era quase inexistente e os conteúdos publicados não geravam engajamento real.',
                'solution'           => 'Reestruturamos toda a operação digital da clínica. Criamos landing pages específicas por procedimento com copy orientado à transformação. Implementamos um sistema de automação para nutrição de leads com sequências de e-mail e WhatsApp. Desenvolvemos uma estratégia de conteúdo orgânico focada em palavras-chave de alta intenção. Os funcionários IA da Lymity operaram semanalmente: o Social Media IA criando calendários, o SEO IA otimizando páginas, o Copywriter IA produzindo textos para anúncios e landing pages.',
                'results'            => [
                    'Crescimento de tráfego orgânico' => '+280%',
                    'Taxa de conversão'               => '1,8% → 4,6%',
                    'Redução custo por agendamento'   => '-42%',
                    'Crescimento de receita'          => '+67%',
                ],
                'testimonial'        => 'Finalmente entendemos o que significa ter uma operação digital de verdade. Em 3 meses vimos mais resultado do que em 2 anos com agência comum.',
                'testimonial_author' => 'Diretora de Marketing — Clínica Estética Premium',
                'tags'               => ['tráfego pago', 'seo', 'automação', 'saúde'],
                'published_at'       => now()->subMonths(2),
            ],
            [
                'title'              => 'Empresa B2B de tecnologia gerou 3x mais leads qualificados',
                'slug'               => 'empresa-b2b-tecnologia-leads-qualificados',
                'client_name'        => 'Empresa B2B de Tecnologia (confidencial)',
                'industry'           => 'Tecnologia B2B',
                'challenge'          => 'A empresa tinha um produto excelente mas dificuldade em comunicar valor para tomadores de decisão. As campanhas no LinkedIn e Google geravam volume de leads, mas com baixa qualificação. O time comercial perdia tempo com prospects sem perfil real de compra. O ciclo de vendas estava longo e imprevisível.',
                'solution'           => 'Redesenhamos a estratégia de aquisição focando em qualidade antes de volume. Criamos conteúdo de autoridade voltado para CTOs e diretores de operação. Implementamos segmentação avançada nas campanhas para impactar apenas decisores com perfil adequado. O SDR IA qualificou leads automaticamente antes de chegar ao time comercial. O Analista IA gerou relatórios semanais de performance que guiaram as otimizações de campanha.',
                'results'            => [
                    'Melhora na qualificação de leads' => '+180%',
                    'Redução do ciclo de vendas'       => '68 → 31 dias',
                    'Taxa de fechamento'               => '8% → 22%',
                    'Redução do CAC'                   => '-55%',
                ],
                'testimonial'        => 'O que mais nos surpreendeu foi a qualidade dos insights. Não só geraram leads — nos ajudaram a entender quais leads realmente fechavam e por quê.',
                'testimonial_author' => 'CEO — Empresa B2B de Tecnologia',
                'tags'               => ['tráfego pago', 'crm', 'b2b', 'qualificação'],
                'published_at'       => now()->subMonth(),
            ],
        ];

        foreach ($cases as $case) {
            CaseStudy::updateOrCreate(['slug' => $case['slug']], $case);
        }
    }
}
