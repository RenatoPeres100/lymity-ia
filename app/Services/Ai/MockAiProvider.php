<?php

namespace App\Services\Ai;

use App\Models\AiTask;

class MockAiProvider implements AiProviderInterface
{
    public function providerName(): string { return 'mock'; }

    public function supportsStructuredOutput(): bool { return true; }

    public function testConnection(): array
    {
        return [
            'success'    => true,
            'message'    => 'Mock provider sempre disponível. Nenhuma API externa necessária.',
            'model'      => config('ai.model', 'mock-growth-agent'),
            'latency_ms' => rand(5, 30),
        ];
    }

    /**
     * Accept array payload (from AiGenerationService) OR a legacy AiTask object
     * so that code that calls generate($task) directly still works.
     *
     * @param array|AiTask $payload
     */
    public function generate(array|AiTask $payload): array
    {
        // Legacy: accept AiTask directly
        if ($payload instanceof AiTask) {
            $task       = $payload;
            $employee   = $task->employee;
            $taskType   = $task->getEffectiveTaskType();
            $title      = $task->title;
            $desc       = $task->description;
            $clientName = $task->client?->name ?? 'Lymity IA';
        } else {
            $employee   = $payload['employee']    ?? null;
            $taskType   = $payload['task_type']   ?? 'general';
            $title      = $payload['title']       ?? 'Sem título';
            $desc       = $payload['description'] ?? null;
            $clientName = $payload['client_name'] ?? 'Lymity IA';

            // Enrich description with client context if available
            if (!empty($payload['client_context'])) {
                $ctx  = $payload['client_context'];
                $desc = $desc
                    ? "{$desc}\n\nContexto: " . json_encode($ctx, JSON_UNESCAPED_UNICODE)
                    : 'Contexto: ' . json_encode($ctx, JSON_UNESCAPED_UNICODE);
            }
        }

        $skillList = $employee?->skills->pluck('name')->implode(', ') ?? '—';

        $response = match ($taskType) {
            'generate_social_post','social_post'  => $this->generateSocialPosts($title, $desc, $clientName, $employee),
            'generate_social_calendar'            => $this->generateSocialCalendar($title, $desc, $clientName),
            'generate_social_variants'            => $this->generateSocialVariants($title, $desc, $clientName),
            'improve_social_post'                 => $this->improveSocialPost($title, $desc, $clientName),
            'generate_blog_post'                  => $this->generateBlogPostJson($title, $desc, $clientName),
            'generate_seo_plan'                   => $this->generateSeoContentPlanJson($title, $desc, $clientName),
            'generate_keyword_cluster'            => $this->generateKeywordClusterJson($title, $desc, $clientName),
            'improve_blog_post'                   => $this->improveBlogPost($title, $desc, $clientName),
            'generate_meta_description'           => $this->generateMetaDescription($title, $desc, $clientName),
            'generate_seo_audit_mock'             => $this->generateSeoAuditMockJson($title, $desc, $clientName),
            'seo_plan'                            => $this->generateSeoPlan($title, $desc, $clientName),
            'ads_analysis'                        => $this->generateAdsAnalysis($title, $desc, $clientName),
            'generate_google_ads_campaign'        => $this->generateGoogleAdsCampaign($title, $desc, $clientName),
            'generate_meta_ads_campaign'          => $this->generateMetaAdsCampaign($title, $desc, $clientName),
            'generate_ad_creatives'               => $this->generateAdCreatives($title, $desc, $clientName),
            'generate_keywords'                   => $this->generateAdKeywords($title, $desc, $clientName),
            'generate_audience_suggestions'       => $this->generateAudienceSuggestions($title, $desc, $clientName),
            'analyze_campaign_metrics'            => $this->analyzeCampaignMetrics($title, $desc, $clientName),
            'suggest_budget_change'               => $this->suggestBudgetChange($title, $desc, $clientName),
            'generate_proposal'                   => $this->generateProposalJson($title, $desc, $clientName),
            'generate_budget'                     => $this->generateBudgetJson($title, $desc, $clientName),
            'improve_proposal'                    => $this->improveProposalJson($title, $desc, $clientName),
            'summarize_budget'                    => $this->summarizeBudgetJson($title, $desc, $clientName),
            'copywriting'                         => $this->generateCopywriting($title, $desc, $clientName),
            'project_plan'                        => $this->generateProjectPlan($title, $desc, $clientName),
            'lead_qualification'                  => $this->generateLeadQualification($title, $desc, $clientName),
            'data_analysis'                       => $this->generateDataAnalysis($title, $desc, $clientName),
            'creative_briefing'                   => $this->generateCreativeBriefing($title, $desc, $clientName),
            default                               => $this->generateGenericOutput($title, $desc, $clientName, $taskType, $employee, $skillList),
        };

        return [
            'success'          => true,
            'provider'         => 'mock',
            'model'            => config('ai.model', 'mock-growth-agent'),
            'prompt_preview'   => "[mock] {$taskType} — {$title}",
            'response'         => $response,
            'response_summary' => mb_substr(strip_tags($response), 0, 300),
            'input_tokens'     => null,
            'output_tokens'    => null,
            'total_tokens'     => null,
            'estimated_cost'   => 0.0,
            'error_message'    => null,
            'raw_response'     => null,
        ];
    }

    private function generateBlogPostJson(string $title, ?string $desc, string $clientName): string
    {
        $keyword = $desc ?? 'marketing digital com inteligência artificial';
        $slug    = \Illuminate\Support\Str::slug($title);

        $data = [
            'title'              => $title,
            'slug'               => $slug,
            'subtitle'           => "Guia completo sobre {$keyword} para empresas que querem crescer",
            'excerpt'            => "Descubra como {$keyword} pode transformar os resultados da sua empresa. Neste artigo, exploramos estratégias práticas e casos reais de sucesso com {$clientName}.",
            'seo_title'          => "{$title} — Guia Definitivo | {$clientName}",
            'seo_description'    => "Aprenda tudo sobre {$keyword} com exemplos práticos, estratégias comprovadas e dicas exclusivas da {$clientName}. Leia agora e transforme seus resultados.",
            'focus_keyword'      => $keyword,
            'secondary_keywords' => [
                "{$keyword} para empresas",
                "como usar {$keyword}",
                "estratégias de {$keyword}",
                "{$keyword} no Brasil",
            ],
            'content'            => $this->generateBlogContent($title, $keyword, $clientName),
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateBlogContent(string $title, string $keyword, string $clientName): string
    {
        return "<h2>Introdução</h2>

<p>No cenário digital atual, dominar {$keyword} se tornou essencial para empresas que desejam se destacar da concorrência. A {$clientName} tem ajudado dezenas de negócios a implementar estratégias eficazes e mensuráveis nessa área.</p>

<p>Neste guia completo, você vai encontrar tudo o que precisa saber para aplicar {$keyword} de forma inteligente, desde os conceitos básicos até as táticas mais avançadas utilizadas pelos melhores especialistas do mercado.</p>

<h2>O que é {$keyword} e por que é importante</h2>

<p>Antes de entrar nas estratégias práticas, é fundamental entender o que é {$keyword} e qual o seu papel no crescimento de negócios modernos. Em essência, trata-se de um conjunto de técnicas e práticas que, quando bem executadas, geram resultados consistentes e escaláveis.</p>

<p>Empresas que investem em {$keyword} de forma estruturada registram, em média, um crescimento de 40% a 60% na geração de oportunidades qualificadas. Esses números não são coincidência — são resultado de planejamento, execução e otimização contínua.</p>

<h2>Principais benefícios para o seu negócio</h2>

<p>A adoção de estratégias de {$keyword} traz uma série de vantagens competitivas. Entre os principais benefícios que nossos clientes relatam, destacamos:</p>

<p>Primeiramente, o aumento significativo na visibilidade orgânica. Quando sua empresa aparece nos primeiros resultados de busca para termos relevantes do seu setor, a percepção de autoridade cresce naturalmente, atraindo clientes que já estão no momento de decisão.</p>

<p>Em segundo lugar, a redução do custo de aquisição de clientes. Estratégias bem executadas de {$keyword} tendem a ter um ROI muito superior às mídias pagas tradicionais, especialmente no médio e longo prazo.</p>

<h2>Estratégias práticas que funcionam</h2>

<p>A teoria é importante, mas o que realmente faz a diferença são as ações práticas. Com base na experiência da {$clientName} com clientes de diferentes setores, listamos as estratégias que consistentemente entregam os melhores resultados:</p>

<p>A criação de conteúdo relevante e otimizado é o pilar central de qualquer estratégia eficaz. Isso significa produzir artigos, vídeos e materiais que respondam genuinamente às dúvidas do seu público-alvo, utilizando as palavras-chave certas no contexto correto.</p>

<p>A consistência na publicação é outro fator crítico. Empresas que publicam conteúdo de forma regular e previsível constroem uma audiência engajada e sinalizam para os algoritmos que são fontes confiáveis de informação.</p>

<h2>Como a IA está transformando essa área</h2>

<p>A inteligência artificial está revolucionando a forma como empresas abordam {$keyword}. Ferramentas inteligentes permitem agora analisar volumes massivos de dados, identificar padrões de comportamento e personalizar experiências em uma escala que seria impossível manualmente.</p>

<p>Na {$clientName}, utilizamos IA para otimizar cada etapa do processo — desde a pesquisa de palavras-chave até a análise de performance. O resultado é uma operação mais eficiente, com decisões baseadas em dados reais e não em suposições.</p>

<h2>Erros comuns que você deve evitar</h2>

<p>Conhecer os erros mais frequentes pode poupar meses de trabalho desperdiçado. O primeiro e mais comum é a falta de planejamento estratégico. Muitas empresas iniciam suas ações de {$keyword} sem uma pesquisa adequada de palavras-chave ou uma compreensão clara do seu público-alvo.</p>

<p>Outro erro frequente é ignorar a análise de dados. Sem monitorar regularmente as métricas corretas, é impossível identificar o que está funcionando e o que precisa ser ajustado. Ferramentas como Google Analytics 4 e Search Console são aliadas indispensáveis.</p>

<h2>Próximos passos para implementar</h2>

<p>Agora que você compreende os fundamentos e as melhores práticas de {$keyword}, é hora de agir. Comece com um diagnóstico honesto da situação atual da sua empresa — entenda onde você está antes de definir para onde quer ir.</p>

<p>Em seguida, defina objetivos claros e mensuráveis. Não basta querer 'aparecer no Google' — é preciso especificar quais termos, em qual prazo e com qual nível de busca mensal. Metas bem definidas permitem estratégias bem executadas.</p>

<h2>Conclusão</h2>

<p>Implementar uma estratégia sólida de {$keyword} é um investimento que se paga ao longo do tempo, gerando fluxo constante de oportunidades e fortalecendo a presença digital da sua empresa de forma sustentável.</p>

<p>A {$clientName} está pronta para ajudar o seu negócio a dar esse passo. Entre em contato com nossa equipe e descubra como podemos criar uma estratégia personalizada para os seus objetivos.</p>";
    }

    private function generateSeoContentPlanJson(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'title'       => $title,
            'description' => $desc ?? "Plano de conteúdo SEO para {$clientName}",
            'weeks'       => [
                ['week' => 1, 'theme' => 'Autoridade e posicionamento', 'posts' => 2],
                ['week' => 2, 'theme' => 'Educação e tutoriais', 'posts' => 2],
                ['week' => 3, 'theme' => 'Casos de sucesso', 'posts' => 2],
                ['week' => 4, 'theme' => 'Geração de leads', 'posts' => 2],
            ],
            'keywords'    => [
                'Palavra-chave principal',
                'Variação long tail 1',
                'Variação long tail 2',
                'Termo relacionado',
            ],
            'notes'       => "Plano gerado para {$clientName} em modo mock. Substitua pelos dados reais.",
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateKeywordClusterJson(string $title, ?string $desc, string $clientName): string
    {
        $mainKeyword = $desc ?? $title;
        $data = [
            'cluster_name'   => $title,
            'main_keyword'   => $mainKeyword,
            'keywords'       => [
                ['keyword' => $mainKeyword, 'type' => 'pillar', 'intent' => 'commercial'],
                ['keyword' => "o que é {$mainKeyword}", 'type' => 'supporting', 'intent' => 'informational'],
                ['keyword' => "como usar {$mainKeyword}", 'type' => 'supporting', 'intent' => 'informational'],
                ['keyword' => "{$mainKeyword} para empresas", 'type' => 'supporting', 'intent' => 'commercial'],
                ['keyword' => "{$mainKeyword} preço", 'type' => 'supporting', 'intent' => 'transactional'],
                ['keyword' => "melhor {$mainKeyword}", 'type' => 'supporting', 'intent' => 'commercial'],
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function improveBlogPost(string $title, ?string $desc, string $clientName): string
    {
        $original = $desc ?? 'Conteúdo original não fornecido.';
        return <<<OUTPUT
# ✍️ Post de Blog Melhorado — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

## Melhorias Aplicadas

1. **Título otimizado:** Inclusão de palavra-chave principal no início
2. **Introdução reescrita:** Gancho mais forte com dado ou pergunta provocadora
3. **Subtítulos H2/H3:** Estrutura hierárquica para melhor escaneabilidade
4. **Parágrafos:** Máximo 3-4 linhas para facilitar leitura mobile
5. **CTA interno:** Adicionado ao meio e ao fim do artigo
6. **SEO técnico:** Meta description e focus keyword otimizados

## Conteúdo Melhorado

{$original}

> ✅ Versão melhorada gerada em modo mock. Revise e personalize antes de publicar.
OUTPUT;
    }

    private function generateMetaDescription(string $title, ?string $desc, string $clientName): string
    {
        $keyword = $desc ?? $title;
        return "Descubra tudo sobre {$keyword} com a {$clientName}. Estratégias práticas, exemplos reais e dicas exclusivas para transformar os resultados do seu negócio. Leia agora!";
    }

    private function generateSeoAuditMockJson(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'title'   => $title,
            'score'   => 68,
            'summary' => "Auditoria SEO gerada em modo mock para {$clientName}. Conecte ferramentas reais (GSC, Semrush, Ahrefs) para dados precisos.",
            'recommendations' => [
                [
                    'title'       => 'Otimizar meta descriptions',
                    'description' => 'Várias páginas estão sem meta description ou com textos duplicados. Defina descrições únicas e com chamada para ação para cada página relevante.',
                    'priority'    => 'high',
                ],
                [
                    'title'       => 'Melhorar velocidade de carregamento',
                    'description' => 'O site apresenta tempo de carregamento superior a 3 segundos em mobile. Otimize imagens, ative cache e considere um CDN.',
                    'priority'    => 'critical',
                ],
                [
                    'title'       => 'Estrutura de headings',
                    'description' => 'Algumas páginas têm múltiplos H1 ou pulam níveis de heading. Organize a hierarquia corretamente: H1 > H2 > H3.',
                    'priority'    => 'medium',
                ],
                [
                    'title'       => 'Construir backlinks de qualidade',
                    'description' => 'O perfil de backlinks atual é limitado. Considere guest posts, parcerias estratégicas e relações públicas digitais.',
                    'priority'    => 'medium',
                ],
                [
                    'title'       => 'Schema markup',
                    'description' => 'Implemente dados estruturados (schema.org) para melhorar a apresentação nos resultados de busca com rich snippets.',
                    'priority'    => 'low',
                ],
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateSocialPosts(string $title, ?string $desc, string $clientName, $employee): string
    {
        $employeeName = $employee?->name ?? 'Social Media IA';
        return <<<OUTPUT
# ✅ Saída gerada por: {$employeeName}
## Tarefa: {$title}
**Cliente:** {$clientName} | **Modo:** Mock | **Aguardando aprovação humana**

---

## 💡 Ideia 1 — Posicionamento de Autoridade

**Tema:** Como a IA está mudando a operação de empresas que crescem
**Gancho:** "Você ainda opera sua empresa manualmente?"
**Legenda:**
> Enquanto concorrentes perdem horas em tarefas repetitivas, empresas inteligentes usam IA para criar conteúdo, analisar dados e fechar clientes.
> Na {$clientName}, transformamos operação em crescimento. 📈
> Quer ver como funciona? Link na bio.

**CTA:** Solicite uma demonstração gratuita
**Hashtags sugeridas:** #InteligênciaArtificial #Automação #MarketingDigital #GrowthHacking #AgênciaDigital

---

## 💡 Ideia 2 — Prova Social / Resultado

**Tema:** Resultado real de cliente com automação + IA
**Gancho:** "O que acontece quando você para de fazer tudo na mão?"
**Legenda:**
> Um de nossos clientes reduziu em 60% o tempo gasto em criação de conteúdo, sem perder qualidade.
> O segredo? Funcionários IA trabalhando 24h, entregando rascunhos prontos para aprovação.
> {$clientName} — tecnologia que trabalha enquanto você dorme. 🤖

**CTA:** Conheça os resultados
**Hashtags sugeridas:** #ResultadosReais #AutomaçãoIA #EficiênciaDigital

---

## 💡 Ideia 3 — Educacional / Curiosidade

**Tema:** O que são Funcionários IA e como eles ajudam sua empresa
**Gancho:** "Você tem um time de 8 especialistas trabalhando para você?"
**Legenda:**
> Social Media IA, SEO IA, Copywriter IA, Gestor de Tráfego IA…
> São os Funcionários IA da {$clientName}. Cada um especialista, todos trabalhando de forma integrada.
> Aprove o que faz sentido, publique com confiança. 🎯

**CTA:** Saiba mais
**Hashtags sugeridas:** #FuncionáriosIA #AutomaçãoDigital #LymityIA

---

> ⚠️ **Rascunho para aprovação.** Nenhum conteúdo foi publicado. Revise antes de autorizar.
OUTPUT;
    }

    private function generateSocialCalendar(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 📅 Calendário Editorial Social — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## 🎯 Resumo Estratégico do Mês

**Tema central:** Autoridade + Geração de Leads
**Distribuição de objetivos:**
- 30% Autoridade (educar, posicionar como referência)
- 25% Relacionamento (aproximar, humanizar)
- 25% Captação de Leads (gerar interesse qualificado)
- 20% Engajamento (interação, comunidade)

**Formatos prioritários:** Carrossel, Reels, Feed, Stories

---

## 📋 8 Ideias de Posts

| Semana | Tema | Objetivo | Formato | Plataforma |
|---|---|---|---|---|
| S1 | "O que é possível com IA no seu negócio" | Awareness | Carrossel | Instagram/LinkedIn |
| S1 | "Bastidores: como funciona nossa operação" | Relacionamento | Reels | Instagram |
| S2 | "3 resultados que nossos clientes alcançaram" | Autoridade | Carrossel | Instagram/LinkedIn |
| S2 | "Você sabe quanto custa não ter estratégia?" | Leads | Feed | Instagram |
| S3 | "IA no marketing: mitos e verdades" | Educacional | Carrossel | LinkedIn |
| S3 | "Antes e depois: transformação digital real" | Prova Social | Reels | Instagram |
| S4 | "Por que sua empresa precisa de IA agora" | Autoridade | Thread | LinkedIn/Threads |
| S4 | "Solicite um diagnóstico gratuito" | Leads | Story + CTA | Instagram |

---

## 📊 Distribuição Semanal

- **Semana 1:** 2 posts (apresentação + bastidores)
- **Semana 2:** 2 posts (resultados + urgência)
- **Semana 3:** 2 posts (educação + prova social)
- **Semana 4:** 2 posts (autoridade + captação)

---

## 💡 Temas Semanais Sugeridos

1. **Semana 1:** Posicionamento e awareness
2. **Semana 2:** Prova social e resultados
3. **Semana 3:** Educação e autoridade
4. **Semana 4:** Captação e urgência

---

> ⚠️ **Rascunho para aprovação.** Nenhum post foi publicado ou agendado automaticamente.
OUTPUT;
    }

    private function generateSocialVariants(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 🔄 Variações por Plataforma — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## 📸 Instagram
**Tom:** Visual, emocional, com emojis
**Legenda:**
> Sua empresa já usa IA para crescer? 🤖
> Enquanto outros perdem tempo com tarefas manuais, empresas inteligentes deixam a IA trabalhar — e focam no que realmente importa.
>
> Na {$clientName}, cada funcionário IA cria, analisa e entrega. Você aprova antes de publicar. 🎯
>
> Quer ver como funciona? Link na bio.

**Hashtags:** #InteligênciaArtificial #MarketingDigital #AutomaçãoIA #GrowthHacking #LymityIA
**CTA:** Acesse o link na bio

---

## 💼 LinkedIn
**Tom:** Profissional, focado em dados e resultado
**Legenda:**
> Empresas que ainda dependem de processos manuais estão perdendo competitividade.
>
> Dados recentes mostram que equipes que adotam IA no marketing digital reduzem em até 60% o tempo de produção de conteúdo — sem perder qualidade.
>
> A {$clientName} oferece uma plataforma de Funcionários IA que trabalham 24h, sempre sob supervisão humana e com aprovação antes de qualquer publicação.
>
> Curioso para saber mais? Comente abaixo ou envie uma mensagem direta.

**Hashtags:** #InteligênciaArtificial #MarketingB2B #Inovação #Automação
**CTA:** Solicite uma demonstração

---

## 🎵 TikTok
**Tom:** Dinâmico, direto, com gancho forte
**Legenda:**
> POV: Sua empresa tem 8 especialistas IA trabalhando enquanto você dorme 😮
> {$clientName} — IA que trabalha, humanos que aprovam.

**Hashtags:** #IA #AutomaçãoDigital #MarketingDigital #TechTok
**CTA:** Siga para mais conteúdo sobre IA

---

## 🧵 Threads
**Tom:** Conversacional, opiniativo, provocador
**Legenda:**
> Empresas que ainda fazem tudo manual em 2025 vão ficar para trás.
>
> Não é pessimismo. É matemática.
>
> IA para marketing não é tendência. É necessidade.
>
> {$clientName} ajuda empresas a automatizarem sua operação de marketing — com aprovação humana em cada etapa.

**CTA:** Me conta: sua empresa já usa IA?

---

> ⚠️ **Rascunho para aprovação.** Nenhuma variação foi publicada automaticamente.
OUTPUT;
    }

    private function improveSocialPost(string $title, ?string $desc, string $clientName): string
    {
        $original = $desc ?? 'Conteúdo original não fornecido.';
        return <<<OUTPUT
# ✨ Post Melhorado — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## 📝 Versão Original
> {$original}

---

## 🚀 Versão Melhorada

> **Gancho:** Você sabia que a maioria das empresas desperdiça até 40% do tempo em tarefas que a IA faz em segundos?
>
> Na {$clientName}, transformamos isso em realidade para nossos clientes.
>
> Funcionários IA especializados criam, analisam e entregam — enquanto você foca em crescer.
>
> E o melhor: você aprova TUDO antes de publicar. Sem surpresas. 🎯
>
> Quer saber como funciona? Link na bio.

---

## 💡 O que foi melhorado

1. **Gancho mais forte:** Dado numérico gera curiosidade imediata
2. **Proposta de valor clara:** Explicação do diferencial em 1 frase
3. **Prova de controle:** "você aprova tudo" reduz objeção de confiança
4. **CTA direto:** Direcionamento claro para próxima ação
5. **Tom equilibrado:** Profissional mas acessível

---

## 🔄 CTA Alternativo
- "Solicite um diagnóstico gratuito"
- "Agende uma conversa de 15 minutos"
- "Veja cases de resultado"

---

## #️⃣ Hashtags Otimizadas
#InteligênciaArtificial #AutomaçãoDigital #MarketingIA #GrowthMarketing #AgênciaDigital #LymityIA #ResultadosReais #MarketingComIA

---

> ⚠️ **Rascunho para aprovação.** Nenhum post foi publicado ou agendado.
OUTPUT;
    }

    private function generateSeoPlan(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 🔍 Plano SEO — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## Diagnóstico Inicial
- Análise de presença orgânica: **pendente de dados reais**
- Estimativa de tráfego atual: a definir com integração GSC/GA4
- Principais concorrentes orgânicos: a mapear

---

## 🎯 Palavras-chave Sugeridas

| Palavra-chave | Volume Est. | Dificuldade | Intenção |
|---|---|---|---|
| agência de marketing com IA | Alto | Média | Comercial |
| funcionários IA para empresas | Médio | Baixa | Informacional |
| automação de marketing digital | Alto | Alta | Comercial |
| como usar IA no marketing | Alto | Média | Informacional |
| crescimento digital com IA | Médio | Baixa | Comercial |

---

## 📝 Estrutura de Conteúdo Recomendada

1. **Blog principal:** "O que são Funcionários IA e como eles transformam empresas"
2. **Página de serviços:** Otimização com keyword "agência digital com inteligência artificial"
3. **FAQ estruturado:** Perguntas frequentes com schema markup
4. **Linkbuilding interno:** Conectar blog → serviços → casos de sucesso

---

## ✅ Próximos Passos
1. Conectar Google Search Console
2. Mapear páginas prioritárias para otimização
3. Criar calendário de publicações SEO (1 post/semana)
4. Auditar velocidade e Core Web Vitals

> ⚠️ **Rascunho para aprovação.** Nenhuma alteração foi feita no site.
OUTPUT;
    }

    private function generateAdsAnalysis(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 🎯 Análise de Campanhas — {$title}
**Cliente:** {$clientName} | **Modo:** Mock (sem dados reais de conta)

---

## Resumo Executivo
Análise simulada com base em benchmarks do setor de marketing digital B2B.

---

## 📊 Métricas de Referência (Benchmark)

| Métrica | Benchmark do Setor | Meta Sugerida |
|---|---|---|
| CTR (Google Search) | 3–5% | ≥ 4% |
| CPC médio | R$ 4–8 | < R$ 6 |
| Taxa de conversão LP | 2–5% | ≥ 3% |
| ROAS | 3–5x | ≥ 4x |
| Custo por lead | R$ 40–120 | < R$ 80 |

---

## ⚠️ Pontos de Atenção Simulados

1. **Segmentação:** Verificar se audiências de retargeting estão configuradas
2. **Criativos:** Testar variações A/B com diferentes propostas de valor
3. **Landing Pages:** Garantir alinhamento entre anúncio e página destino
4. **Budget:** Distribuição recomendada: 60% fundo de funil, 40% topo

---

## 💡 Recomendações

- Criar campanha de retargeting para visitantes do site há 7–30 dias
- Testar extensões de chamada e snippets estruturados no Google Ads
- Configurar conversões no GA4 antes de escalar budget
- Revisar palavras-chave negativas mensalmente

> ⚠️ **Rascunho para aprovação.** Nenhuma campanha foi alterada ou criada.
OUTPUT;
    }

    private function generateCopywriting(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# ✍️ Copywriting — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## 📣 Headlines Sugeridas

1. "Transforme sua empresa com IA que realmente funciona"
2. "Seu time de marketing rodando 24h, com aprovação humana"
3. "Pare de fazer manual o que a IA faz em segundos"
4. "Da estratégia à publicação, com inteligência artificial"
5. "Resultados reais, sem depender de sorte ou intuição"

---

## 💬 Promessa Principal

> **{$clientName}** entrega crescimento digital previsível com funcionários IA especializados, operando de forma integrada e sempre sob supervisão humana.

---

## 🏗️ Estrutura de Landing Page

**Hero Section:**
- Headline: "Sua agência digital com IA. Resultados reais."
- Subheadline: "8 especialistas IA trabalhando para o seu negócio."
- CTA primário: "Solicite uma demonstração"

**Benefícios (3 colunas):**
- ⚡ Velocidade — Conteúdo criado em segundos, aprovado por humanos
- 🎯 Precisão — Estratégias baseadas em dados e IA
- 🔒 Controle — Você aprova tudo antes da publicação

**Prova Social:**
- Cases de resultado + depoimentos

**CTA Final:**
- "Comece hoje. Sem compromisso."

---

## 📧 Copy de E-mail de Prospecção (versão curta)

Assunto: Como empresas como a sua estão crescendo com IA

Olá [Nome],

Vi que você trabalha com [segmento] e quis compartilhar algo que pode fazer diferença.

A {$clientName} usa Funcionários IA para criar conteúdo, analisar campanhas e gerar relatórios — sempre com aprovação humana.

Posso te mostrar em 15 minutos como funciona?

[CTA]

> ⚠️ **Rascunho para aprovação.** Nenhum e-mail foi enviado.
OUTPUT;
    }

    private function generateProjectPlan(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 🗂️ Plano de Projeto — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## 📋 Checklist de Tarefas

### Fase 1 — Diagnóstico (Semana 1)
- [ ] Reunião de kickoff com cliente
- [ ] Levantamento de objetivos e KPIs
- [ ] Análise de presença digital atual
- [ ] Mapeamento de concorrentes
- [ ] Definição de personas

### Fase 2 — Estratégia (Semana 2)
- [ ] Criação de calendário editorial
- [ ] Definição de canais prioritários
- [ ] Briefing criativo aprovado
- [ ] Plano de mídia paga
- [ ] Setup de ferramentas e acessos

### Fase 3 — Execução (Semanas 3–6)
- [ ] Produção de conteúdo (aprovação por ciclo)
- [ ] Ativação de campanhas pagas
- [ ] SEO on-page nas páginas prioritárias
- [ ] Relatório semanal de performance

### Fase 4 — Otimização (contínuo)
- [ ] Análise de resultados mensais
- [ ] Ajustes de estratégia
- [ ] Escalonamento do que está funcionando

---

## 🏁 Próximos Passos Imediatos

| Ação | Responsável | Prazo |
|---|---|---|
| Confirmar onboarding | Cliente | 3 dias |
| Enviar acessos | TI Cliente | 5 dias |
| Aprovação do briefing | Cliente + Agência | 7 dias |

> ⚠️ **Rascunho para aprovação.** Nenhuma ação foi iniciada.
OUTPUT;
    }

    private function generateLeadQualification(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 📞 Qualificação de Lead — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## Framework BANT Aplicado

**B — Budget (Orçamento)**
- Perguntas sugeridas: "Vocês têm budget definido para marketing digital?"
- Sinal positivo: menciona verba, tempo de contrato
- Sinal negativo: "ainda não temos orçamento"

**A — Authority (Autoridade)**
- Quem toma a decisão de contratação?
- É o dono, diretor de marketing, ou precisa de aprovação?

**N — Need (Necessidade)**
- Qual o maior desafio de marketing hoje?
- Já contrataram agência antes? Resultado?

**T — Timeline (Urgência)**
- Quando pretendem começar?
- Há algum evento ou sazonalidade relevante?

---

## Score do Lead (Simulado)

| Critério | Pontuação |
|---|---|
| Cargo de decisão | +20 |
| Budget mencionado | +25 |
| Necessidade clara | +20 |
| Urgência declarada | +15 |
| Setor alinhado | +20 |
| **Total estimado** | **— a preencher** |

---

## Próximo Passo Recomendado

Agendar discovery call de 30 minutos com roteiro de qualificação.

> ⚠️ **Rascunho para aprovação.** Nenhum lead foi contactado.
OUTPUT;
    }

    private function generateDataAnalysis(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 📊 Análise de Dados — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## Diagnóstico (Simulado)

Análise baseada em estrutura padrão. Conecte as ferramentas analíticas para dados reais.

---

## KPIs Recomendados para Monitoramento

| KPI | Frequência | Meta |
|---|---|---|
| Tráfego orgânico | Semanal | +10%/mês |
| Taxa de conversão | Semanal | ≥ 3% |
| Custo por lead | Mensal | Redução de 15% |
| Engajamento social | Semanal | +5%/mês |
| Receita atribuída | Mensal | +20%/trimestre |

---

## 💡 Insights Preliminares

1. **Canal mais eficiente** (a definir com dados reais)
2. **Melhor dia/hora para posts** (a definir com histórico)
3. **Produto/serviço com maior ROI** (a mapear)
4. **Audiência mais responsiva** (a segmentar)

---

## 📈 Visualizações Recomendadas

- Dashboard Google Looker Studio conectado ao GA4
- Relatório semanal automatizado por e-mail
- Mapa de jornada do cliente por canal

> ⚠️ **Rascunho para aprovação.** Nenhum dado foi alterado ou exportado.
OUTPUT;
    }

    private function generateCreativeBriefing(string $title, ?string $desc, string $clientName): string
    {
        $objective = $desc ?? 'Criar materiais visuais alinhados à identidade da marca.';
        return <<<OUTPUT
# 🎨 Briefing Criativo — {$title}
**Cliente:** {$clientName} | **Modo:** Mock

---

## Objetivo do Projeto
{$objective}

---

## Direção de Arte Sugerida

**Estilo:** Tecnológico, premium, minimalista
**Paleta:** Escuros + acentos vibrantes (azul, verde-neon ou dourado)
**Tipografia:** Sans-serif moderna (Inter, Plus Jakarta Sans, Satoshi)
**Tom visual:** Sofisticado, inovador, confiável

---

## Referências de Estilo
- Linear.app (UI limpa e escura)
- Vercel (tipografia forte + espaço branco)
- Stripe (clareza + elegância)

---

## Formatos Necessários

| Formato | Tamanho | Uso |
|---|---|---|
| Post feed | 1080×1080 | Instagram/LinkedIn |
| Stories | 1080×1920 | Instagram/WhatsApp |
| Banner | 1200×628 | Facebook/LinkedIn |
| Thumbnail blog | 1280×720 | Site |

---

## Mensagem Central
> "{$clientName}: IA que trabalha, humanos que aprovam."

## CTA Visual Principal
Botão ou elemento de destaque: "Solicite uma demonstração"

> ⚠️ **Rascunho para aprovação.** Nenhum material foi produzido ou publicado.
OUTPUT;
    }

    private function generateGoogleAdsCampaign(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'platform'  => 'google_ads',
            'mode'      => 'sandbox',
            'strategy'  => "Campanha Google Ads para {$clientName} com foco em captação de leads via busca intencional. Estrutura de funil: topo (awareness), meio (consideração) e fundo (conversão).",
            'campaign'  => [
                'name'       => "{$title} — Google Search",
                'type'       => 'SEARCH',
                'budget_day' => 50,
                'start'      => now()->addDays(3)->toDateString(),
            ],
            'groups'    => [
                ['name' => 'Grupo Fundo de Funil', 'keywords_count' => 5, 'bid_strategy' => 'CPA alvo'],
                ['name' => 'Grupo Meio de Funil',  'keywords_count' => 4, 'bid_strategy' => 'CPC manual'],
            ],
            'keywords'  => [
                ['keyword' => "agência digital com IA",             'match' => 'exact',   'type' => 'positive'],
                ['keyword' => "marketing digital para empresas",    'match' => 'phrase',  'type' => 'positive'],
                ['keyword' => "automação de marketing",             'match' => 'broad',   'type' => 'positive'],
                ['keyword' => "como captar leads online",           'match' => 'phrase',  'type' => 'positive'],
                ['keyword' => "agência barata",                     'match' => 'exact',   'type' => 'negative'],
                ['keyword' => "marketing freelancer",               'match' => 'phrase',  'type' => 'negative'],
            ],
            'creatives' => [
                ['headline' => "Sua Agência Digital com IA", 'description' => "Funcionários IA especializados. Aprovação humana. Resultados reais.", 'cta' => 'Solicitar Demo'],
                ['headline' => "Marketing com Inteligência Artificial", 'description' => "Cresça com IA. Conteúdo, anúncios e SEO automatizados.", 'cta' => 'Saiba Mais'],
            ],
            'extensions'        => ['callout', 'sitelink', 'call', 'location'],
            'expected_metrics'  => [
                'impressions_week' => 8500,
                'clicks_week'      => 340,
                'ctr'              => '4.0%',
                'avg_cpc'          => 'R$ 4,50',
                'conversions_week' => 17,
                'cpa'              => 'R$ 90,00',
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateMetaAdsCampaign(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'platform' => 'meta_ads',
            'mode'     => 'sandbox',
            'strategy' => "Campanha Meta Ads para {$clientName}. Funil completo: cold audience (interesses) + lookalike + remarketing. Formatos: carrossel + vídeo curto.",
            'audiences' => [
                ['name' => 'Cold — Empreendedores BR',     'size' => '500k–1M',   'type' => 'interest'],
                ['name' => 'Lookalike 1% — Clientes',     'size' => '200k–400k', 'type' => 'lookalike'],
                ['name' => 'Remarketing — Site 30 dias',  'size' => '5k–20k',    'type' => 'custom'],
            ],
            'creatives' => [
                ['format' => 'single_image', 'headline' => "IA que trabalha para você", 'cta' => 'Saiba Mais'],
                ['format' => 'carousel',     'headline' => "3 razões para usar {$clientName}", 'cta' => 'Solicitar Demo'],
                ['format' => 'video_short',  'headline' => "Veja como funciona em 30s", 'cta' => 'Assista Agora'],
            ],
            'copies' => [
                "Sua empresa ainda faz marketing manualmente? Conheça os Funcionários IA da {$clientName}.",
                "IA especializada em marketing + aprovação humana = resultados reais. Saiba mais.",
                "Reduza até 60% do tempo em criação de conteúdo. {$clientName} faz isso acontecer.",
            ],
            'funnel' => ['topo' => '50%', 'meio' => '30%', 'fundo' => '20%'],
            'expected_metrics' => [
                'impressions_week' => 25000,
                'reach_week'       => 18000,
                'clicks_week'      => 500,
                'ctr'              => '2.0%',
                'cpl'              => 'R$ 35,00',
                'leads_week'       => 40,
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateAdCreatives(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 🎨 Criativos para Anúncios — {$title}
**Cliente:** {$clientName} | **Modo:** Sandbox

## Google Ads — Anúncios Responsivos de Pesquisa

**Títulos (até 30 caracteres cada):**
1. Agência Digital com IA
2. Marketing Automatizado
3. Resultados com Inteligência
4. Sua Empresa no Próximo Nível
5. IA que Trabalha para Você

**Descrições (até 90 caracteres cada):**
1. Funcionários IA especializados com aprovação humana. Resultados reais.
2. Cresça com automação inteligente. SEO, social, anúncios integrados.

## Meta Ads — Copies por Formato

**Single Image:**
> "Você sabia que empresas com IA crescem 3x mais rápido? A {$clientName} prova isso todo dia."
> CTA: Saiba Mais

**Carrossel:**
> Slide 1: "IA para Marketing Digital"
> Slide 2: "Conteúdo criado em segundos"
> Slide 3: "Aprovação humana em tudo"
> Slide 4: "Resultados mensuráveis"
> CTA: Solicitar Demonstração

> ⚠️ Rascunho para aprovação. Nenhum anúncio foi publicado.
OUTPUT;
    }

    private function generateAdKeywords(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'campaign'  => $title,
            'client'    => $clientName,
            'mode'      => 'sandbox',
            'keywords'  => [
                ['keyword' => 'agência digital com inteligência artificial', 'match' => 'exact',   'cpc_est' => 'R$ 3,50', 'volume' => 'médio'],
                ['keyword' => 'marketing digital para pequenas empresas',    'match' => 'phrase',  'cpc_est' => 'R$ 2,80', 'volume' => 'alto'],
                ['keyword' => 'automação de marketing digital',             'match' => 'broad',   'cpc_est' => 'R$ 4,20', 'volume' => 'médio'],
                ['keyword' => 'como captar leads online',                   'match' => 'phrase',  'cpc_est' => 'R$ 1,90', 'volume' => 'alto'],
                ['keyword' => 'agência de marketing resultados',            'match' => 'exact',   'cpc_est' => 'R$ 5,00', 'volume' => 'baixo'],
                ['keyword' => 'agência barata',                             'match' => 'negative', 'reason' => 'baixa intenção de compra'],
                ['keyword' => 'marketing freelancer',                       'match' => 'negative', 'reason' => 'perfil não alinhado'],
            ],
            'notes' => 'Lista gerada em modo sandbox. Valide com ferramentas reais (Google Keyword Planner, SEMrush) antes de usar.',
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateAudienceSuggestions(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'campaign' => $title,
            'client'   => $clientName,
            'mode'     => 'sandbox',
            'audiences' => [
                [
                    'name'        => 'Empreendedores e Gestores de Marketing',
                    'platform'    => 'meta_ads',
                    'type'        => 'interest',
                    'size_est'    => '500k–1M',
                    'interests'   => ['marketing digital', 'empreendedorismo', 'gestão empresarial'],
                    'demographics' => ['age' => '25-54', 'location' => 'Brasil'],
                ],
                [
                    'name'        => 'Lookalike 1% — Base de Clientes',
                    'platform'    => 'meta_ads',
                    'type'        => 'lookalike',
                    'size_est'    => '200k–400k',
                    'source'      => 'lista de clientes existentes',
                ],
                [
                    'name'        => 'Remarketing — Visitantes 30 dias',
                    'platform'    => 'meta_ads',
                    'type'        => 'custom',
                    'size_est'    => '5k–20k',
                    'condition'   => 'visitou o site nos últimos 30 dias',
                ],
                [
                    'name'        => 'In-Market: Serviços de Marketing',
                    'platform'    => 'google_ads',
                    'type'        => 'in_market',
                    'description' => 'Usuários pesquisando ativamente por serviços de marketing',
                ],
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function analyzeCampaignMetrics(string $title, ?string $desc, string $clientName): string
    {
        return <<<OUTPUT
# 📊 Análise de Métricas — {$title}
**Cliente:** {$clientName} | **Modo:** Sandbox

## Resumo de Performance (Simulado)

| Métrica | Resultado | Benchmark | Status |
|---|---|---|---|
| CTR | 3,8% | 3–5% | ✅ Dentro do esperado |
| CPC | R$ 4,20 | R$ 4–8 | ✅ Eficiente |
| CPA | R$ 85,00 | R$ 40–120 | ✅ Aceitável |
| ROAS | 3,2x | 3–5x | ⚠️ Pode melhorar |
| Taxa de conversão LP | 4,1% | 2–5% | ✅ Boa |

## Insights Principais

1. **CTR acima da média** — Criativos estão com bom desempenho de cliques
2. **ROAS abaixo do potencial** — Avaliar melhorias na landing page e oferta
3. **CPA estável** — Manter estrutura de segmentação atual

## Recomendações

- Aumentar budget 20% no grupo com menor CPA
- Pausar palavras-chave com CPA > R$ 150
- Testar novo formato de criativo (vídeo curto)
- Revisar landing page: clareza da oferta e velocidade

> ⚠️ Análise gerada em modo sandbox. Conecte dados reais para análise precisa.
OUTPUT;
    }

    private function suggestBudgetChange(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'campaign'    => $title,
            'client'      => $clientName,
            'mode'        => 'sandbox',
            'suggestion'  => [
                'type'       => 'increase',
                'current'    => 50,
                'suggested'  => 75,
                'increase'   => '50%',
                'rationale'  => 'Campanha com CPA abaixo do target e CTR acima da média nos últimos 7 dias. Escalar orçamento pode multiplicar resultados mantendo eficiência.',
            ],
            'projections' => [
                'leads_week_current'   => 20,
                'leads_week_projected' => 32,
                'cpa_projected'        => 'R$ 82,00',
                'roas_projected'       => '3,8x',
            ],
            'conditions'  => 'Recomendação válida apenas se CTR manter-se acima de 3,5% e CPA abaixo de R$ 100.',
            'requires_approval' => true,
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateGenericOutput(string $title, ?string $desc, string $clientName, string $taskType, $employee, string $skillList): string
    {
        $employeeName = $employee?->name ?? 'Funcionário IA';
        $descText     = $desc ?? 'Nenhuma descrição adicional fornecida.';
        return <<<OUTPUT
# 🤖 Saída Gerada — {$title}
**Funcionário:** {$employeeName} | **Tipo:** {$taskType} | **Cliente:** {$clientName}
**Skills disponíveis:** {$skillList}

---

## Diagnóstico

Tarefa recebida e processada em modo mock.
Tipo de tarefa "{$taskType}" processado com framework genérico.

**Contexto fornecido:**
{$descText}

---

## Sugestão de Abordagem

1. **Entendimento do objetivo:** Qual é o resultado esperado desta tarefa?
2. **Dados necessários:** Quais informações são precisas para execução completa?
3. **Recursos envolvidos:** Quais ferramentas, canais ou acessos são necessários?
4. **Linha do tempo:** Qual o prazo esperado e critérios de sucesso?

---

## Plano de Ação Sugerido

- [ ] Alinhar escopo detalhado com responsável
- [ ] Coletar dados e acessos necessários
- [ ] Executar tarefa com marcos intermediários
- [ ] Entregar para revisão e aprovação humana
- [ ] Implementar ajustes após feedback
- [ ] Documentar resultado final

---

## Próximos Passos Imediatos

1. Revisar este rascunho e aprovar ou solicitar ajustes
2. Fornecer dados adicionais se necessário
3. Definir prazo e responsável pela aprovação

> ⚠️ **Rascunho para aprovação.** Nenhuma ação externa foi executada.
OUTPUT;
    }

    private function generateProposalJson(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'title'       => $title,
            'description' => "Proposta comercial para {$clientName} focada em crescimento digital com inteligência artificial. " . ($desc ?? ''),
            'terms'       => "Pagamento: mensal, antecipado.\nVigência: 3 meses (renovável).\nReajuste: IGPM anual.\nCancelamento: aviso prévio de 30 dias.\nSuporte: horário comercial, resposta em até 24h.\nOs serviços serão executados com aprovação humana em cada etapa relevante.",
            'recommendation' => "Recomendamos início imediato pelo Setup Estratégico para garantir baseline de dados antes das campanhas.",
            'items' => [
                [
                    'name'        => 'Setup Estratégico e Diagnóstico',
                    'description' => 'Auditoria completa, mapeamento de oportunidades, definição de KPIs e plano de ação 90 dias',
                    'quantity'    => 1,
                    'unit_price'  => 1800.00,
                ],
                [
                    'name'        => 'Gestão de Tráfego Pago (Google + Meta)',
                    'description' => 'Criação, otimização e relatórios mensais de campanhas Google Ads e Meta Ads',
                    'quantity'    => 1,
                    'unit_price'  => 2400.00,
                ],
                [
                    'name'        => 'SEO e Conteúdo com IA',
                    'description' => 'Produção de 8 artigos/mês otimizados, link building e monitoramento de posições',
                    'quantity'    => 1,
                    'unit_price'  => 1600.00,
                ],
                [
                    'name'        => 'Automação e Nutrição de Leads',
                    'description' => 'Configuração de fluxos de automação, landing pages e sequência de e-mails',
                    'quantity'    => 1,
                    'unit_price'  => 1200.00,
                ],
                [
                    'name'        => 'Relatório de Performance Mensal',
                    'description' => 'Dashboard executivo com análise de resultados e recomendações estratégicas',
                    'quantity'    => 1,
                    'unit_price'  => 600.00,
                ],
            ],
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function generateBudgetJson(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'title'       => $title,
            'description' => "Orçamento mensal de operação digital para {$clientName}. " . ($desc ?? ''),
            'justification' => 'Distribuição baseada em benchmarks do setor para empresas em fase de aceleração.',
            'items' => [
                [
                    'category'    => 'media',
                    'name'        => 'Investimento Google Ads',
                    'description' => 'Verba de mídia para campanhas de search e performance',
                    'amount'      => 3500.00,
                ],
                [
                    'category'    => 'media',
                    'name'        => 'Investimento Meta Ads',
                    'description' => 'Verba de mídia para Facebook e Instagram',
                    'amount'      => 2500.00,
                ],
                [
                    'category'    => 'production',
                    'name'        => 'Produção de Criativos',
                    'description' => 'Peças estáticas, vídeos curtos e stories mensais',
                    'amount'      => 1200.00,
                ],
                [
                    'category'    => 'service',
                    'name'        => 'Gestão e Operação',
                    'description' => 'Fee mensal de gestão da agência',
                    'amount'      => 2800.00,
                ],
                [
                    'category'    => 'tool',
                    'name'        => 'Ferramentas e Plataformas',
                    'description' => 'Licenças de SEO, automação e analytics',
                    'amount'      => 600.00,
                ],
            ],
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function improveProposalJson(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'action'      => 'improve_proposal',
            'target'      => $title,
            'instruction' => $desc ?? 'Melhoria geral',
            'suggestions' => [
                'Reforçar a proposta de valor única da solução com IA.',
                'Incluir estimativas de ROI baseadas em benchmarks do setor.',
                'Adicionar depoimentos ou resultados de clientes semelhantes.',
                'Tornar os termos mais claros e objetivos.',
                'Destacar o diferencial da aprovação humana em cada etapa.',
            ],
            'improved_terms' => "Pagamento: mensal, antecipado via boleto/PIX.\nVigência: 3 meses com opção de renovação automática.\nResultados esperados: aumento de 30-50% em leads qualificados nos primeiros 90 dias.\nGarantia de satisfação: revisão gratuita nas primeiras 2 semanas.",
            'notes'       => "Proposta revisada com foco em conversão. Recomenda-se adicionar casos de sucesso relevantes antes do envio ao cliente.",
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function summarizeBudgetJson(string $title, ?string $desc, string $clientName): string
    {
        $data = [
            'action'  => 'summarize_budget',
            'title'   => $title,
            'summary' => "O orçamento de {$clientName} está distribuído de forma estratégica, priorizando mídia paga (maior ROI imediato) e operação de qualidade. A proporção mídia/gestão está dentro do benchmark recomendado de 60/40.",
            'insights' => [
                'Investimento em mídia representa 55% do orçamento total — dentro do ideal para fase de aquisição.',
                'Fee de gestão competitivo considerando o escopo entregue.',
                'Ferramentas representam menos de 5% — eficiência operacional adequada.',
                'Recomenda-se revisão trimestral para rebalancear verba conforme performance.',
            ],
            'recommendation' => 'Aprovação recomendada. Orçamento alinhado com objetivos de crescimento.',
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
