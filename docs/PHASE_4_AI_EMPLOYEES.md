# Fase 4 — Núcleo Operacional dos Funcionários IA

## Visão geral

A Fase 4 implementa o núcleo operacional da plataforma: execução de tarefas por funcionários IA, memória persistente, agendamentos automáticos e logs detalhados. Todo output gerado é um **rascunho para aprovação humana** — nenhuma ação externa é realizada em modo mock.

---

## Banco de Dados

### Tabelas novas

| Tabela | Descrição |
|---|---|
| `ai_memories` | Base de conhecimento dos funcionários IA (regras, preferências, insights) |
| `ai_feedback` | Avaliações humanas das saídas geradas |
| `ai_work_schedules` | Agendamentos recorrentes de execução |

### Colunas adicionadas (incrementais)

| Tabela | Colunas |
|---|---|
| `ai_employees` | `autonomy_level`, `default_client_id`, `max_tasks_per_day`, `requires_approval_default`, enum `status` expandido |
| `ai_skills` | `category` |
| `ai_employee_skill` | `level`, unique constraint |
| `ai_tasks` | `task_type`, `requires_approval`, `sensitive_action`, `output`, `error_message`, `metadata`, `approved_by`, `approved_at`, enums expandidos |
| `ai_task_logs` | `ai_employee_id`, `client_id` |

---

## Configuração de Provider IA

```env
AI_PROVIDER=mock       # mock | openai | anthropic (futuro)
AI_API_KEY=            # Chave da API real (quando aplicável)
AI_MODEL=mock-growth-agent
```

Arquivo: `config/ai.php`

---

## Arquitetura de Serviços

```
AiProviderManager
  └── resolve() → MockAiProvider (ou futuros providers)

AiTaskService
  ├── createManualTask(employee, data) → AiTask [status: queued]
  ├── runTask(task) → AiTask [status: waiting_approval | completed]
  ├── approveTask(task, user?) → AiTask [status: completed]
  ├── rejectTask(task, reason, user?) → AiTask [status: rejected]
  └── cancelTask(task) → AiTask [status: canceled]

AiLogService
  ├── info / success / warning / error
  └── Todos os logs vinculados a task + employee + client

AiMemoryService
  ├── getRelevantMemories(employeeId, clientId)
  ├── buildMemoryContext() → string para prompt
  ├── store / update / delete

AiWorkSchedulerService
  └── runDueSchedules() → dispara RunAiTaskJob para cada schedule vencido

AiEmployeeService
  └── create / update / pause / activate / disable
```

---

## Fluxo de Execução de Tarefa

```
createManualTask()     →  status: queued
         ↓
   runTask()          →  status: running
         ↓
   MockAiProvider      →  gera output em PT-BR
         ↓
requires_approval?
  SIM  → status: waiting_approval  (aguarda revisão humana)
  NÃO  → status: completed
         ↓
approveTask() → status: completed
rejectTask()  → status: rejected
cancelTask()  → status: canceled
```

---

## Jobs

| Job | Trigger |
|---|---|
| `RunAiTaskJob` | Dispatch manual ou pelo Scheduler |
| `ProcessAiWorkScheduleJob` | Um schedule específico |
| `GenerateAiDailyPlanJob` | Planejamento diário para todos os ativos |

---

## Comandos Artisan

```bash
# Executar tarefa específica
php artisan ai:run-task {task_id}

# Processar todos os agendamentos vencidos
php artisan ai:run-schedules
```

---

## Rotas do Painel Admin

| Método | Rota | Ação |
|---|---|---|
| GET | /admin/ai-employees | Listagem |
| GET | /admin/ai-employees/create | Formulário de criação |
| POST | /admin/ai-employees | Criar |
| GET | /admin/ai-employees/{id} | Detalhes |
| GET | /admin/ai-employees/{id}/edit | Editar |
| PUT | /admin/ai-employees/{id} | Atualizar |
| DELETE | /admin/ai-employees/{id} | Remover |
| POST | /admin/ai-employees/{id}/pause | Pausar |
| POST | /admin/ai-employees/{id}/activate | Ativar |
| POST | /admin/ai-employees/{id}/disable | Desativar |
| GET | /admin/ai-tasks | Listagem de tarefas |
| POST | /admin/ai-tasks | Criar tarefa |
| GET | /admin/ai-tasks/{id} | Detalhes da tarefa |
| POST | /admin/ai-tasks/{id}/run | Executar |
| POST | /admin/ai-tasks/{id}/approve | Aprovar |
| POST | /admin/ai-tasks/{id}/reject | Rejeitar |
| POST | /admin/ai-tasks/{id}/cancel | Cancelar |
| POST | /admin/ai-tasks/{id}/feedback | Feedback |
| GET | /admin/ai-logs | Logs de execução |
| GET | /admin/ai-schedules | Agendamentos |
| GET | /admin/ai-memories | Memórias |

---

## Funcionários IA (8)

| Slug | Role Key | Especialidade |
|---|---|---|
| social-media-ia | social_media_ai | Conteúdo para redes sociais |
| copywriter-ia | copywriter_ai | Copy persuasivo e estratégico |
| gestor-de-trafego-ia | traffic_manager_ai | Google Ads / Meta Ads |
| seo-ia | seo_ai | SEO técnico e editorial |
| designer-ia | designer_ai | Direção de arte e briefings |
| sdr-ia | sdr_ai | Qualificação de leads |
| analista-ia | analyst_ai | Análise de dados e relatórios |
| gerente-de-projeto-ia | project_manager_ai | Coordenação de tarefas |

---

## Tipos de Tarefa (MockAiProvider)

| task_type | Saída gerada |
|---|---|
| social_post | 3 ideias de posts com legenda e hashtags |
| seo_plan | Plano SEO com palavras-chave e pauta |
| ads_analysis | Análise de campanha com recomendações |
| copywriting | Headlines, copy body e CTAs |
| project_plan | Plano de projeto com marcos e tarefas |
| lead_qualification | Score, qualificação e próxima ação |
| data_analysis | Dashboard de KPIs e insights |
| creative_briefing | Briefing visual completo |
| general / outros | Output genérico com sugestão de abordagem |

---

## Regras de Segurança

- **Nenhum output é publicado automaticamente** — todo conteúdo é rascunho para aprovação.
- **Ações sensíveis** (`sensitive_action=true`) exigem aprovação obrigatória.
- **Execução mock** nunca envia mensagens, altera campanhas ou mexe em verbas.
- **Logs são criados em toda etapa** (criação, execução, aprovação, rejeição, cancelamento).
- **Sem force push** e sem sobrescrita de funcionalidades de fases anteriores.

---

## Testes

```bash
php artisan test tests/Feature/Phase4AiEmployeesTest.php
# 19/19 testes passando
```

Cobertura:
- T01–T03: Modelos e labels
- T04–T10: Ciclo completo de tarefa (criar → executar → aprovar/rejeitar/cancelar → logs)
- T11–T13: AiMemory e AiWorkSchedule
- T14–T19: Rotas do painel e saída mock

---

## Seeds de Demonstração

```bash
php artisan db:seed --class=AiWorkScheduleSeeder   # 5 agendamentos
php artisan db:seed --class=AiMemorySeeder         # 7 memórias
```
