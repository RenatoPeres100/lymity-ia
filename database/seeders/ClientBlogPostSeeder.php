<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientBlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::where('name', 'Cliente Demonstração')->first();
        if (!$client) return;

        $category = BlogCategory::first();
        $author   = User::where('user_type', 'agency')->first();

        $posts = [
            [
                'title'           => '5 Tratamentos Estéticos Que Estão Transformando Vidas em 2025',
                'slug'            => '5-tratamentos-esteticos-transformando-vidas-2025',
                'status'          => 'pending_approval',
                'seo_description' => 'Descubra os 5 tratamentos estéticos mais procurados em 2025 e como eles podem transformar sua autoestima e bem-estar.',
                'content'         => "O setor de estética avançada vive um momento de revolução tecnológica. Com procedimentos cada vez mais precisos, seguros e eficazes, mais pessoas estão conquistando resultados que antes pareciam impossíveis.\n\nNeste artigo, apresentamos os 5 tratamentos estéticos mais procurados no Brasil em 2025 e por que eles estão mudando a vida de tantas pessoas.\n\n## 1. Criolipólise: A Queima de Gordura por Frio\n\nA criolipólise é uma técnica não invasiva que utiliza o frio controlado para destruir células de gordura localizada. O procedimento é indolor, não exige cirurgia e o recuperação é praticamente imediata.\n\nResultados visíveis já a partir da segunda semana, com redução de até 25% da gordura na área tratada.\n\n## 2. Radiofrequência: Firmeza sem Bisturi\n\nA radiofrequência estimula a produção de colágeno e elastina, devolvendo firmeza e jovialidade à pele. Indicada para flacidez facial e corporal, é um dos tratamentos mais solicitados nas clínicas de estética.\n\nSessões regulares podem substituir procedimentos cirúrgicos para muitas pessoas.\n\n## 3. Ultrassom Cavitacional: Fim da Gordura Localizada\n\nO ultrassom cavitacional utiliza ondas sonoras de alta frequência para explodir células adiposas. É ideal para áreas como abdômen, flancos, coxas e culote.\n\nO tratamento é completamente seguro, não causa dor e não exige repouso.\n\n## 4. Microneedling com PRP: Rejuvenescimento Profundo\n\nA combinação de microneedling com plasma rico em plaquetas (PRP) é revolucionária para rejuvenescimento. O tratamento estimula a regeneração celular e reduz marcas, cicatrizes e sinais de envelhecimento.\n\nResultados notáveis em apenas 3 a 5 sessões.\n\n## 5. Drenagem Linfática Modeladora: Leveza e Definição\n\nA drenagem linfática evoluiu muito além do relaxamento. As técnicas modernas combinam movimentos drenantes com modelagem corporal, eliminando toxinas, reduzindo edemas e definindo o contorno do corpo.\n\n## Agende Sua Avaliação Gratuita\n\nQuer descobrir qual desses tratamentos é ideal para você? Nossa equipe de especialistas oferece avaliação gratuita e personalizada. Entre em contato e dê o primeiro passo rumo à sua transformação.",
                'is_featured'     => true,
                'tags'            => ['estética', 'tratamentos', 'bem-estar', 'autoestima'],
            ],
            [
                'title'           => 'Como Manter os Resultados Estéticos por Mais Tempo',
                'slug'            => 'como-manter-resultados-esteticos-por-mais-tempo',
                'status'          => 'draft',
                'seo_description' => 'Aprenda as melhores práticas para prolongar os resultados dos seus tratamentos estéticos e conquistar bem-estar duradouro.',
                'content'         => "Conquistar resultados com tratamentos estéticos é uma conquista incrível. Mas manter esses resultados ao longo do tempo depende de uma série de cuidados que vão muito além das sessões na clínica.\n\nNeste guia completo, você vai aprender as melhores estratégias para prolongar seus resultados e maximizar o investimento em sua autoestima.\n\n## Hidratação: O Segredo Que Muitos Ignoram\n\nA hidratação adequada é fundamental para qualquer tratamento estético. Beber pelo menos 2 litros de água por dia ajuda a eliminar toxinas, mantém a pele firme e potencializa os resultados de procedimentos como criolipólise e ultrassom cavitacional.\n\nDica: adicione rodelas de limão ou folhas de hortelã para tornar o hábito mais agradável.\n\n## Alimentação Anti-Inflamatória\n\nUma dieta rica em antioxidantes protege a pele e os tecidos tratados. Frutas vermelhas, azeite de oliva, peixes ricos em ômega-3 e vegetais coloridos são aliados poderosos.\n\nEvite o excesso de açúcar refinado e gorduras saturadas, que podem reverter resultados de tratamentos de emagrecimento.\n\n## Atividade Física Regular\n\nO exercício físico é insubstituível para manter os resultados. A combinação de exercícios aeróbicos com musculação fortalece os músculos e reduz a recidiva da gordura localizada tratada.\n\nNão precisa ser exagerado: 30 minutos de caminhada diária já fazem diferença.\n\n## Skincare Consistente\n\nTratamentos faciais como microneedling e radiofrequência precisam de uma rotina de skincare eficaz para potencializar e manter os resultados. Use protetor solar diariamente, hidratantes com ácido hialurônico e séruns com vitamina C.\n\n## Sessões de Manutenção\n\nPlaneje sessões de manutenção periódicas, geralmente a cada 30 a 60 dias, dependendo do tratamento. Essas sessões prolongam os resultados e previnem regressões.\n\nNossa equipe pode criar um plano de manutenção personalizado para você.\n\n## Sono de Qualidade\n\nDurante o sono, o organismo regenera células e tecidos. Dormir de 7 a 9 horas por noite potencializa os resultados de praticamente todos os tratamentos estéticos.\n\n## Conclusão\n\nManter os resultados estéticos é uma jornada de autocuidado que envolve escolhas diárias. Combinando os tratamentos certos com esses hábitos, você conquista uma transformação real e duradoura.\n\nPrecisa de orientação personalizada? Nossa equipe está pronta para ajudar. Agende sua consulta de acompanhamento.",
                'is_featured'     => false,
                'tags'            => ['dicas', 'cuidados', 'resultados', 'saúde'],
            ],
        ];

        foreach ($posts as $postData) {
            BlogPost::updateOrCreate(
                ['slug' => $postData['slug'], 'client_id' => $client->id],
                array_merge($postData, [
                    'type'        => 'client',
                    'client_id'   => $client->id,
                    'category_id' => $category?->id,
                    'author_id'   => $author?->id,
                ])
            );
        }
    }
}
