<?php

namespace App\Services\Ai;

use App\Models\AiTask;

class MockAiProvider
{
    public function generate(AiTask $task): string
    {
        $employee  = $task->employee;
        $client    = $task->client;
        $taskType  = $task->getEffectiveTaskType();
        $title     = $task->title;
        $desc      = $task->description;
        $clientName = $client?->name ?? 'Lymity IA';
        $skillList  = $employee?->skills->pluck('name')->implode(', ') ?? '—';

        return match ($taskType) {
            'generate_social_post','social_post' => $this->generateSocialPosts($title, $desc, $clientName, $employee),
            'generate_social_calendar'           => $this->generateSocialCalendar($title, $desc, $clientName),
            'generate_social_variants'           => $this->generateSocialVariants($title, $desc, $clientName),
            'improve_social_post'                => $this->improveSocialPost($title, $desc, $clientName),
            'seo_plan'             => $this->generateSeoPlan($title, $desc, $clientName),
            'ads_analysis'         => $this->generateAdsAnalysis($title, $desc, $clientName),
            'copywriting'          => $this->generateCopywriting($title, $desc, $clientName),
            'project_plan'         => $this->generateProjectPlan($title, $desc, $clientName),
            'lead_qualification'   => $this->generateLeadQualification($title, $desc, $clientName),
            'data_analysis'        => $this->generateDataAnalysis($title, $desc, $clientName),
            'creative_briefing'    => $this->generateCreativeBriefing($title, $desc, $clientName),
            default                => $this->generateGenericOutput($title, $desc, $clientName, $taskType, $employee, $skillList),
        };
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
}
