<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lymity.local')->first();

        $catAI     = BlogCategory::where('slug', 'inteligencia-artificial')->first();
        $catAds    = BlogCategory::where('slug', 'trafego-pago')->first();
        $catAuto   = BlogCategory::where('slug', 'automacao')->first();

        $posts = [
            [
                'title'              => 'Como a IA muda a operação de crescimento das empresas',
                'slug'               => 'como-a-ia-muda-a-operacao-de-crescimento-das-empresas',
                'subtitle'           => 'Entenda por que empresas que integram IA à sua operação crescem mais rápido, com menos desperdício e mais previsibilidade.',
                'excerpt'            => 'A inteligência artificial deixou de ser uma promessa distante e se tornou o principal diferencial competitivo de empresas que crescem com consistência. Entenda como isso funciona na prática.',
                'focus_keyword'      => 'IA para crescimento empresarial',
                'secondary_keywords' => 'inteligência artificial negócios, automação com IA, agência digital com IA',
                'seo_title'          => 'Como a IA Muda a Operação de Crescimento das Empresas | Lymity',
                'seo_description'    => 'Descubra como a inteligência artificial transforma a operação de crescimento de empresas modernas, reduzindo desperdício e aumentando previsibilidade.',
                'category_id'        => $catAI?->id,
                'content'            => $this->postContentAI(),
            ],
            [
                'title'              => 'Por que campanhas isoladas não sustentam crescimento',
                'slug'               => 'por-que-campanhas-isoladas-nao-sustentam-crescimento',
                'subtitle'           => 'O problema não é a campanha. É a ausência de um sistema integrado de aquisição, nutrição e conversão.',
                'excerpt'            => 'Muitas empresas investem em anúncios e depois se perguntam por que o crescimento não vem. O problema raramente está na campanha em si.',
                'focus_keyword'      => 'sistema de crescimento',
                'secondary_keywords' => 'campanhas de marketing, tráfego pago resultados, funil de vendas',
                'seo_title'          => 'Por Que Campanhas Isoladas Não Sustentam Crescimento | Lymity',
                'seo_description'    => 'Descubra por que campanhas de tráfego pago isoladas não geram crescimento sustentável e como construir um sistema integrado de aquisição.',
                'category_id'        => $catAds?->id,
                'content'            => $this->postContentAds(),
            ],
            [
                'title'              => 'Automação, dados e conteúdo: o novo motor das empresas digitais',
                'slug'               => 'automacao-dados-e-conteudo-o-novo-motor-das-empresas-digitais',
                'subtitle'           => 'Como a combinação de automação inteligente, análise de dados e conteúdo estratégico cria o motor de crescimento das empresas mais avançadas.',
                'excerpt'            => 'As empresas que mais crescem hoje não têm necessariamente os maiores orçamentos. Elas têm sistemas. E esses sistemas combinam três forças: automação, dados e conteúdo.',
                'focus_keyword'      => 'automação de marketing com IA',
                'secondary_keywords' => 'automação digital, marketing de conteúdo, dados para crescimento',
                'seo_title'          => 'Automação, Dados e Conteúdo: O Motor das Empresas Digitais | Lymity',
                'seo_description'    => 'Entenda como automação, análise de dados e conteúdo estratégico formam o motor de crescimento das empresas digitais mais eficientes.',
                'category_id'        => $catAuto?->id,
                'content'            => $this->postContentAutomation(),
            ],
        ];

        foreach ($posts as $postData) {
            BlogPost::updateOrCreate(
                ['slug' => $postData['slug']],
                array_merge($postData, [
                    'author_id'    => $admin?->id,
                    'type'         => 'agency',
                    'status'       => 'published',
                    'published_at' => now()->subDays(rand(1, 30)),
                ])
            );
        }
    }

    private function postContentAI(): string
    {
        return '<p>A inteligência artificial deixou de ser uma promessa distante e se tornou o principal diferencial competitivo de empresas que crescem com consistência. Não estamos falando de chatbots genéricos ou automações simples. Estamos falando de uma mudança estrutural na forma como empresas operam, decidem e se posicionam no mercado.</p>

<p>Durante anos, crescimento digital significava contratar mais pessoas, rodar mais campanhas e esperar que os números melhorassem com o tempo. Esse modelo tem prazo de validade. As empresas que entenderam isso mais cedo estão, hoje, operando com uma eficiência que seria impossível de alcançar sem tecnologia.</p>

<h2>O que mudou com a IA aplicada ao crescimento</h2>

<p>A principal mudança não é técnica — é operacional. Empresas que integram IA à sua operação conseguem executar em paralelo o que antes exigia equipes grandes e processos lentos. Um funcionário IA especializado em SEO pode analisar centenas de páginas, identificar oportunidades e estruturar um plano de otimização em minutos. Um copywriter IA pode produzir rascunhos de alta qualidade que um profissional humano revisa e refina rapidamente.</p>

<p>Isso não elimina o humano — ele se torna mais estratégico. A IA executa o trabalho operacional com velocidade e consistência. O humano direciona, revisa e aprova. Essa combinação é o que chamamos de operação híbrida de crescimento.</p>

<h2>Os três pilares da operação com IA</h2>

<p><strong>1. Velocidade de execução:</strong> Tarefas que levavam dias passam a ser executadas em horas. Isso não significa qualidade menor — significa que o ciclo de teste e aprendizado acelera. Empresas que testam mais, aprendem mais rápido e se adaptam mais eficientemente.</p>

<p><strong>2. Consistência operacional:</strong> Um dos maiores problemas de agências e times de marketing é a inconsistência. Semanas boas, semanas ruins. Com funcionários IA operando com rotinas definidas, a execução se torna previsível. Relatórios sempre prontos. Conteúdo sempre sendo produzido. Campanhas sempre sendo monitoradas.</p>

<p><strong>3. Rastreabilidade total:</strong> Toda ação de um funcionário IA é registrada. Isso cria um nível de rastreabilidade operacional que times humanos raramente conseguem manter. Cada sugestão, cada análise, cada rascunho tem origem, contexto e histórico.</p>

<h2>O que isso significa para sua empresa</h2>

<p>Se você ainda depende exclusivamente de processos manuais e times sem apoio de IA, está operando com uma desvantagem crescente. Não porque IA substituirá seus colaboradores — mas porque empresas que combinam pessoas e IA conseguem entregar mais, mais rápido, com mais consistência e a um custo operacional menor.</p>

<p>A pergunta não é mais "devemos usar IA?" A pergunta é: "Como vamos estruturar nossa operação para extrair o máximo da IA sem perder o controle estratégico?"</p>

<h2>Como a Lymity estrutura isso</h2>

<p>Na Lymity, cada funcionário IA tem uma função específica, habilidades mapeadas, rotinas definidas e um sistema de aprovação humana obrigatório para ações sensíveis. Isso cria uma operação que funciona 24 horas, com supervisão humana nos momentos que realmente importam.</p>

<p>O resultado é uma agência que entrega mais, com mais consistência, sem comprometer a qualidade estratégica que só profissionais humanos experientes podem garantir.</p>';
    }

    private function postContentAds(): string
    {
        return '<p>Muitas empresas investem em anúncios e depois se perguntam por que o crescimento não vem. Aumentam o orçamento, testam novos criativos, trocam de plataforma. E o resultado continua aquém do esperado. O problema raramente está na campanha em si.</p>

<p>O problema está na ausência de um sistema. E campanhas isoladas, por melhores que sejam, não constroem sistemas.</p>

<h2>O equívoco do "mais tráfego"</h2>

<p>Existe uma crença muito comum no marketing digital: se as vendas não estão boas, é porque falta tráfego. Então a solução é investir mais em anúncios. Mais impressões, mais cliques, mais orçamento. Só que, na prática, mais tráfego sem um sistema de conversão adequado significa mais dinheiro sendo desperdiçado.</p>

<p>Tráfego é o combustível. Mas sem o motor — que é o sistema de conversão, nutrição e relacionamento — o combustível simplesmente não gera movimento.</p>

<h2>O que é um sistema de crescimento</h2>

<p>Um sistema de crescimento é a integração inteligente de aquisição, conversão e retenção. Não é uma campanha. É uma arquitetura.</p>

<p><strong>Aquisição:</strong> Como você atrai pessoas que têm real potencial de se tornar clientes? Não só quem clica — mas quem tem o perfil, a intenção e a capacidade de comprar.</p>

<p><strong>Conversão:</strong> O que acontece após o clique? Uma landing page genérica que não converte desperdiça todo o investimento feito na aquisição. A conversão precisa ser projetada com o mesmo cuidado que o anúncio.</p>

<p><strong>Nutrição:</strong> Nem todo visitante está pronto para comprar agora. Um sistema de crescimento captura, nutre e se mantém presente até o momento certo. E-mail, remarketing, conteúdo — tudo integrado.</p>

<p><strong>Retenção:</strong> O cliente que já comprou tem o maior potencial de crescimento. Mas a maioria das empresas investe quase todo o orçamento em aquisição e ignora retenção. Um sistema equilibrado cuida de quem já está na base.</p>

<h2>Por que campanhas isoladas falham</h2>

<p>Campanhas isoladas não têm contexto. Elas começam do zero, geram dados fragmentados e precisam de tempo para otimizar antes de qualquer resultado consistente. Quando terminam, o aprendizado se perde.</p>

<p>Sistemas de crescimento, por outro lado, são cumulativos. Cada ciclo gera dados que alimentam o próximo. O aprendizado se acumula. As conversões melhoram progressivamente. E o custo de aquisição tende a cair com o tempo.</p>

<h2>Como construir um sistema que sustenta crescimento</h2>

<p>O primeiro passo é mapear toda a jornada do cliente, do primeiro contato até a fidelização. Depois, identificar os gargalos: onde você está perdendo pessoas? Por que? O que pode ser melhorado?</p>

<p>Com esse mapa em mãos, as campanhas deixam de ser iniciativas isoladas e passam a ser peças de um sistema maior. Cada anúncio tem um propósito claro dentro da jornada. Cada página de destino foi projetada para um estágio específico. Cada automação foi pensada para mover o lead para o próximo passo.</p>

<p>Esse é o tipo de operação que a Lymity constrói para seus clientes. Não apenas campanhas — sistemas de crescimento integrados, monitorados e evoluídos continuamente.</p>';
    }

    private function postContentAutomation(): string
    {
        return '<p>As empresas que mais crescem hoje não têm necessariamente os maiores orçamentos. Elas têm sistemas. E esses sistemas combinam três forças que, quando integradas, criam um motor de crescimento que opera com eficiência, consistência e escalabilidade: automação, dados e conteúdo.</p>

<p>Entender como essas três forças se conectam é o primeiro passo para construir uma operação digital que cresce de forma previsível.</p>

<h2>Automação: a infraestrutura do crescimento moderno</h2>

<p>Automação não é sobre substituir pessoas. É sobre eliminar o trabalho repetitivo e sem valor estratégico para que as pessoas possam focar no que realmente importa. E no contexto do marketing digital, há uma quantidade enorme de trabalho que pode e deve ser automatizado.</p>

<p>Fluxos de nutrição de leads, disparo de e-mails comportamentais, atualização de CRM, relatórios de performance, publicação de conteúdo — tudo isso pode operar de forma autônoma, com regras claras e resultados rastreáveis.</p>

<p>O resultado é uma operação que não para. Que funciona nos fins de semana, nas madrugadas e nos feriados. E que entrega consistência independentemente do momento.</p>

<h2>Dados: a inteligência por trás das decisões</h2>

<p>Automação sem dados é cega. Você pode automatizar processos, mas se não tiver visibilidade sobre o que está funcionando e o que não está, vai automatizar erros em escala.</p>

<p>Dados bem estruturados transformam a operação. Você passa a entender quais canais trazem clientes com maior valor ao longo do tempo, quais conteúdos convertem melhor em cada etapa do funil, quais segmentos respondem melhor a determinadas abordagens e onde estão os gargalos que travam o crescimento.</p>

<p>Com esses dados, cada decisão de marketing deixa de ser baseada em feeling e passa a ser baseada em evidência. E evidência, combinada com velocidade de execução, é imbatível.</p>

<h2>Conteúdo: o ativo que se acumula</h2>

<p>Diferente de campanhas pagas — que existem enquanto você investe — conteúdo de qualidade se acumula. Um artigo bem escrito e otimizado para SEO pode trazer visitantes por anos. Um case bem produzido pode fechar negócios sem nenhuma intervenção ativa.</p>

<p>Conteúdo estratégico não é sobre volume. É sobre relevância. É sobre responder as perguntas que seu potencial cliente está fazendo antes mesmo de saber que precisa do que você oferece. É sobre construir autoridade de forma consistente e sustentável.</p>

<h2>A integração que cria o motor</h2>

<p>Quando as três forças se integram, o motor começa a funcionar. O conteúdo atrai visitantes qualificados. Os dados identificam quem tem maior potencial. A automação nutre, converte e retém — de forma consistente e escalável.</p>

<p>Esse motor não precisa de intervenção constante para rodar. Ele precisa de monitoramento inteligente, ajustes estratégicos e evolução contínua. É exatamente para isso que existem os funcionários IA da Lymity.</p>

<p>Cada especialista — SEO, Copywriter, Analista, Gestor de Tráfego — opera dentro de sua função, contribui para o motor e garante que a operação nunca pare de evoluir. Com aprovação humana nos momentos críticos e autonomia operacional no dia a dia.</p>

<p>O resultado é uma empresa que cresce de forma consistente, previsível e escalável. Não por acaso. Por sistema.</p>';
    }
}
