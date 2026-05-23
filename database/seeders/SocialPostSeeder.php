<?php

namespace Database\Seeders;

use App\Models\AiEmployee;
use App\Models\Client;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class SocialPostSeeder extends Seeder
{
    public function run(): void
    {
        $client   = Client::first();
        $user     = User::first();
        $employee = AiEmployee::where('slug', 'social-media-ia')->first()
            ?? AiEmployee::where('status', 'active')->first();

        $posts = [
            [
                'title'          => '5 formas que a IA está revolucionando o marketing digital',
                'objective'      => 'authority',
                'content_type'   => 'feed',
                'main_caption'   => "A inteligência artificial não é mais o futuro — é o presente.\n\nEm 2024, as empresas que integram IA nas suas estratégias de marketing estão obtendo resultados até 3x maiores.\n\nAqui estão as 5 principais formas:\n\n1️⃣ Personalização em escala\n2️⃣ Análise preditiva de comportamento\n3️⃣ Criação de conteúdo automatizada\n4️⃣ Atendimento 24/7 com chatbots inteligentes\n5️⃣ Otimização de campanhas em tempo real\n\nQual dessas já faz parte da sua estratégia? 👇",
                'hashtags'       => '#MarketingDigital #InteligenciaArtificial #IA #MarketingIA #Lymity',
                'cta'            => 'Comente abaixo qual estratégia você usa!',
                'status'         => 'approved',
                'requires_approval' => true,
                'client_id'      => null,
            ],
            [
                'title'          => 'Como criar um calendário editorial eficiente',
                'objective'      => 'awareness',
                'content_type'   => 'carousel',
                'main_caption'   => "Calendário editorial: o segredo das marcas que nunca ficam sem conteúdo.\n\nDeslize para ver o passo a passo completo ➡️\n\n✅ Defina seus pilares de conteúdo\n✅ Mapeie datas importantes\n✅ Planeje com antecedência\n✅ Deixe espaço para conteúdo reativo\n✅ Mensure e ajuste\n\nSalve esse post para usar depois!",
                'hashtags'       => '#CalendárioEditorial #Marketing #Conteúdo #SocialMedia',
                'cta'            => 'Salve e compartilhe com quem precisa!',
                'status'         => 'scheduled',
                'requires_approval' => false,
                'scheduled_at'   => now()->addDays(3)->setHour(9)->setMinute(0),
                'client_id'      => null,
            ],
            [
                'title'          => 'Post de engajamento — Cliente Demo',
                'objective'      => 'engagement',
                'content_type'   => 'feed',
                'main_caption'   => "Pergunta rápida para você! 🤔\n\nQual é o maior desafio da sua empresa no marketing digital hoje?\n\nA) Gerar mais leads\nB) Aumentar o engajamento\nC) Criar conteúdo consistente\nD) Medir os resultados\n\nComente a letra da sua resposta! 👇",
                'hashtags'       => '#MarketingDigital #Engajamento #Enquete',
                'cta'            => 'Comente sua letra!',
                'status'         => 'pending_approval',
                'requires_approval' => true,
                'client_id'      => $client?->id,
            ],
            [
                'title'          => 'Rascunho — Campanha de vendas Q2',
                'objective'      => 'sales',
                'content_type'   => 'reels',
                'creative_brief' => 'Criar um reel mostrando os resultados que os clientes obtiveram com a Lymity IA. Tom inspirador, com depoimentos e dados.',
                'status'         => 'draft',
                'requires_approval' => true,
                'client_id'      => null,
            ],
        ];

        foreach ($posts as $data) {
            if (!SocialPost::where('title', $data['title'])->exists()) {
                SocialPost::create(array_merge($data, [
                    'created_by'     => $user?->id,
                    'ai_employee_id' => $employee?->id,
                ]));
            }
        }
    }
}
