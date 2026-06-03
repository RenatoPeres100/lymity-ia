# Prospecção CRM / SDR IA — Documentação

## Objetivo do módulo

Módulo de prospecção comercial da Lymity IA. Permite cadastro e gestão de leads, pipeline kanban, atividades, follow-ups, notas e uso assistido de IA para análise, qualificação e geração de mensagens personalizadas.

**A IA não envia mensagens automaticamente.** Toda ação externa permanece com o humano.

---

## Pipeline Padrão

Pipeline: **Pipeline Comercial Lymity**

Etapas (em ordem):
1. Novo lead
2. Pesquisado
3. Primeiro contato feito
4. Respondeu
5. Qualificado
6. Reunião agendada
7. Proposta enviada
8. Negociação
9. Fechado (is_won=true)
10. Perdido (is_lost=true)

Para instalar: `php artisan prospecting:install-default-pipeline`

---

## Permissões

| Permissão | Descrição |
|---|---|
| prospecting.view | Ver módulo de prospecção |
| prospecting.create | Criar leads |
| prospecting.update | Editar leads / mover etapas |
| prospecting.delete | Excluir leads |
| prospecting.pipeline.view | Ver pipeline kanban |
| prospecting.pipeline.manage | Gerenciar etapas |
| prospecting.activities.view | Ver atividades |
| prospecting.activities.create | Criar atividades |
| prospecting.activities.update | Atualizar atividades |
| prospecting.ai.analyze | Analisar lead com IA |
| prospecting.ai.qualify | Qualificar lead com IA |
| prospecting.ai.generate_message | Gerar mensagem IA |
| prospecting.ai.suggest_next_action | Sugerir próxima ação |
| prospecting.logs.view | Ver logs de prospecção |

---

## Models

| Model | Tabela | Descrição |
|---|---|---|
| ProspectPipeline | prospect_pipelines | Pipeline de etapas |
| ProspectStage | prospect_stages | Etapa do pipeline |
| ProspectLead | prospect_leads | Lead / contato comercial |
| ProspectActivity | prospect_activities | Atividades e follow-ups |
| ProspectNote | prospect_notes | Notas internas |
| ProspectAiInsight | prospect_ai_insights | Insights gerados pela IA |
| ProspectMessageSuggestion | prospect_message_suggestions | Mensagens sugeridas pela IA |

---

## Rotas principais

| Método | URL | Nome | Descrição |
|---|---|---|---|
| GET | /admin/prospecting | admin.prospecting.dashboard | Dashboard |
| GET | /admin/prospecting/pipeline | admin.prospecting.pipeline | Kanban |
| GET | /admin/prospecting/leads | admin.prospecting.leads.index | Listagem |
| GET | /admin/prospecting/leads/create | admin.prospecting.leads.create | Formulário |
| POST | /admin/prospecting/leads | admin.prospecting.leads.store | Criar lead |
| GET | /admin/prospecting/leads/{lead} | admin.prospecting.leads.show | Detalhes |
| PATCH | /admin/prospecting/leads/{lead}/move-stage | admin.prospecting.leads.move-stage | Mover etapa |
| POST | /admin/prospecting/leads/{lead}/ai/analyze | admin.prospecting.ai.analyze | Analisar com IA |
| POST | /admin/prospecting/leads/{lead}/ai/qualify | admin.prospecting.ai.qualify | Qualificar com IA |
| POST | /admin/prospecting/leads/{lead}/ai/generate-message | admin.prospecting.ai.generate-message | Gerar mensagem |
| POST | /admin/prospecting/leads/{lead}/ai/suggest-next-action | admin.prospecting.ai.suggest-next-action | Sugerir ação |
| POST | /admin/prospecting/leads/{lead}/ai/summarize | admin.prospecting.ai.summarize | Resumir histórico |

---

## Fluxo diário de prospecção

1. Acessar `/admin/prospecting` — verificar follow-ups atrasados
2. Abrir leads quentes — revisar próxima ação
3. Criar atividades pendentes
4. Usar SDR IA para analisar ou qualificar leads que precisam de atenção
5. Gerar mensagens personalizadas para novos contatos
6. Copiar mensagem aprovada e enviar manualmente (WhatsApp, e-mail, etc.)
7. Registrar outcome da atividade
8. Mover lead para próxima etapa quando avançar

---

## Como usar o SDR IA

Na tela do lead, o painel SDR IA oferece:

- **Analisar lead**: identifica oportunidades, dores e recomendações
- **Qualificar lead**: sugere fit_score e interest_level com justificativa
- **Sugerir próxima ação**: indica o próximo passo ideal com prazo sugerido
- **Resumir histórico**: consolida atividades e notas em um resumo
- **Gerar mensagem**: cria mensagem personalizada para canal e objetivo escolhidos

Todos os resultados são salvos em `ProspectAiInsight` ou `ProspectMessageSuggestion`.

---

## O que a IA pode fazer nesta fase

- Analisar dados do lead e identificar oportunidades
- Sugerir score de fit (0–100) e nível de interesse
- Recomendar próxima ação com data sugerida
- Escrever mensagens personalizadas para WhatsApp, e-mail, Instagram, LinkedIn ou script de ligação
- Resumir histórico de atividades e notas

## O que a IA NÃO faz nesta fase

- Enviar mensagens automaticamente
- Fazer scraping ou buscar dados externos
- Disparar e-mail ou WhatsApp
- Criar propostas ou contratos
- Gerenciar campanhas de Ads

---

## Segurança e isolamento

- `admin_geral` vê todos os leads (todos os company_id)
- Usuários da agência veem apenas leads da sua `company_id`
- Clientes (`cliente`, `colaborador`) não acessam o módulo Prospecção
- `ai_employee` não acessa o painel humano
- Toda query usa `scopeVisibleTo` para garantir isolamento
- Acesso por URL manual a lead de outra company retorna 403

---

## Commands

```bash
# Instalar pipeline padrão
php artisan prospecting:install-default-pipeline

# Com leads demo (apenas local/staging)
php artisan prospecting:install-default-pipeline --demo

# Diagnóstico completo
php artisan prospecting:diagnose
```

---

## Testes executados (2026-06-03)

- ✓ Migrations executadas (7 tabelas)
- ✓ PermissionSeeder: 15 permissões criadas
- ✓ Pipeline padrão: 1 pipeline, 10 etapas
- ✓ SDR IA: encontrado (slug: sdr-ia)
- ✓ Gemini configurado: AI_PROVIDER=google
- ✓ Rotas registradas: 23 rotas
- ✓ HTTP: todas as rotas retornam 200/302, zero 500
- ✓ Regressão: blog, social, approvals, system health — todos OK
