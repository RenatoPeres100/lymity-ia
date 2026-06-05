# Real AI Employee Task Engine

## Visão Geral

Esta fase transforma os Funcionários IA da Lymity de geradores de conteúdo avulso em **agentes operacionais reais**, que executam tarefas recorrentes com contexto, memória, economia de tokens, qualidade estratégica e aprovação estruturada.

---

## Arquitetura

```
AgentTask (tarefa recorrente)
  └── AiEmployee (funcionário vinculado)
  └── AgencyBrandContext (Brand Context compacto)
  └── AiMemory (memórias relevantes)
  └── AgentTaskRun (execução)
        └── AiExecutionContext (snapshot do contexto)
        └── GeneratedContentPackage (pacote completo)
              └── GeneratedContentAsset (imagens)
              └── ApprovalRequest (aprovação)
                    └── BlogPost | SocialPost (entidade final)
```

---

## Brand Context Compacto

**Arquivo:** `app/Services/AI/Context/BrandContextSnapshotService.php`  
**Model:** `AgencyBrandContext` (tabela `agency_brand_contexts`)

### Novos campos:
- `version` — incrementa a cada atualização
- `content_hash` — SHA-256 dos campos de conteúdo
- `compact_context` — versão compacta gerada automaticamente
- `compact_context_generated_at` — timestamp da última geração

### Fluxo:
1. Ao salvar Brand Context → `onBrandContextSaved()` → incrementa version, regenera hash e compact_context
2. Em cada execução de tarefa → `getCompactContext()` → usa cache ou regenera se necessário
3. Compact context vai direto no prompt (economiza tokens)

---

## AgentTask — Tarefa Operacional Recorrente

**Model:** `app/Models/AgentTask.php`  
**Tabela:** `agent_tasks`  
**Controller:** `app/Http/Controllers/Admin/AgentTaskController.php`  
**Tela:** `/admin/agent-tasks`

### Diferença vs AiTask antiga:
- `AiTask`: tarefa única/pontual gerada por um agente em resposta a uma ação
- `AgentTask`: **função recorrente** que executa automaticamente em dias/horários definidos

### Tipos de tarefa:
- `blog_post_recurring` — Posts de blog com SEO, CTA, imagem
- `instagram_post_recurring` — Posts Instagram com legenda, hashtags, imagem
- `instagram_carousel_recurring` — Carrosseis com N slides
- `copy_review_recurring` — Revisão de conteúdos
- `custom` — Tipo livre

### Campos principais:
- `operational_instructions` — Instruções detalhadas para a IA
- `frequency` — manual, daily, weekly, monthly
- `days_of_week` — JSON com dias da semana
- `time_of_day` — Horário de execução
- `requires_external_research` — Se pesquisa externa é necessária
- `requires_image` — Se deve gerar imagem
- `requires_approval` — **Sempre true em produção**
- `auto_schedule_after_approval` — Agendar após aprovação
- `next_run_at` — Calculado automaticamente

---

## Contexto Compacto da Tarefa

**Serviço:** `app/Services/AI/Context/AgentTaskContextService.php`

Cria uma versão compacta das instruções da tarefa para uso recorrente nos prompts, evitando enviar o texto completo a cada execução.

---

## Memória Operacional

**Model:** `app/Models/AiMemory.php` (expandido)  
**Serviço:** `app/Services/AI/Memory/AIMemoryService.php`  
**Tela:** `/admin/ai-memory`

### Tipos de memória:
- `approved_pattern` — Padrões que foram aprovados pelo admin
- `rejected_pattern` — Padrões rejeitados (alto peso no prompt)
- `feedback` — Feedback específico para ajustes
- `brand_rule` — Regras de marca
- `task_rule` — Regras específicas da tarefa
- `content_preference` / `visual_preference` — Preferências
- `warning` — Avisos críticos

### Regras:
- Máximo 5 memórias por execução
- Isolamento por `company_id` / `client_id`
- Memória de um cliente nunca aparece no prompt de outro
- Aprovações/rejeições geram memórias automaticamente

---

## Snapshot de Execução

**Model:** `app/Models/AiExecutionContext.php`  
**Tabela:** `ai_execution_contexts`

Registra exatamente o que foi usado em cada execução:
- Versão e hash do Brand Context usado
- Versão e hash do Task Context usado
- Memórias selecionadas (máx 5)
- Pesquisa externa capturada
- Hash e preview do prompt enviado
- Modelo e provider usados
- Tokens estimados e custo estimado

---

## Geração de Conteúdo

**Serviço:** `app/Services/AI/Execution/AgentTaskExecutionService.php`

### Fluxo de execução:
1. `preparing_context` — Carrega Brand Context + Task Context + Memórias → cria AiExecutionContext
2. `researching` — Se habilitado, busca pesquisa externa
3. `generating_text` — Envia prompt estruturado para Gemini, aguarda JSON
4. `generating_images` — Se `requires_image=true`, gera imagem(s)
5. `creating_approval` — Cria entidade (BlogPost/SocialPost) + GeneratedContentPackage + ApprovalRequest
6. `waiting_approval` — Aguarda revisão humana

### Prompt estruturado:
Construído por `app/Services/AI/Prompt/StructuredPromptBuilderService.php`:
1. Identidade do funcionário IA
2. Contexto compacto da marca
3. Contexto compacto da tarefa
4. 3–5 memórias relevantes
5. Dados externos resumidos (se disponível)
6. Regras de saída JSON
7. Regras de segurança

---

## Geração de Imagem Antes da Aprovação

**Serviço:** `app/Services/AI/Images/ContentPackageImageGenerationService.php`

Se `IMAGE_GENERATION_ENABLED=false` (padrão):
- Cria pacote com texto + prompt visual
- Marca `visual_status = pending_configuration`
- Aprovação mostra que imagem não foi gerada por falta de provider

Se `IMAGE_GENERATION_ENABLED=true`:
- Gera imagem destacada (blog) ou feed (Instagram) ou slides (carrossel)
- Salva em `GeneratedContentAsset`
- Imagem aparece na aprovação

---

## Pacote de Conteúdo para Aprovação

**Model:** `app/Models/GeneratedContentPackage.php`  
**Tabela:** `generated_content_packages`  
**Assets:** `app/Models/GeneratedContentAsset.php` / `generated_content_assets`

Reúne tudo que o aprovador precisa ver:
- Título, legenda/artigo, SEO, CTA, hashtags
- Status e URL da imagem (ou motivo da ausência)
- Fontes usadas na pesquisa
- Versão do Brand Context usado
- Tokens e custo estimado
- Tarefa e funcionário IA responsáveis

---

## Aprovação Integrada com Memória

**Serviço:** `app/Services/Approval/ApprovalService.php` (existente, não alterado)

Ao **aprovar**:
- Pacote status `approved`
- BlogPost/SocialPost → `approved` ou `scheduled`
- Cria memória `approved_pattern`
- Log `approval.package_approved`

Ao **rejeitar**:
- Pacote status `rejected`
- Cria memória `rejected_pattern` com feedback
- Log `approval.package_rejected`

Ao **pedir ajuste**:
- Cria memória `feedback`
- Log `approval.package_changes_requested`

---

## Scheduler e Recorrência

**Command:** `php artisan agents:run-due-tasks`

Opções:
- `--dry-run` — Lista tarefas que executariam sem executar
- `--task=ID` — Executa tarefa específica
- `--force` — Força execução mesmo fora do horário
- `--now="2026-06-05 09:00:00"` — Override do horário

**Job:** `app/Jobs/RunAgentTaskJob.php`  
Executa em fila (queue worker) para não bloquear o scheduler.

**Scheduler:** `routes/console.php`  
`agents:run-due-tasks` → a cada minuto, sem overlap.

---

## Controle de Custo

**Serviço:** `app/Services/AI/AICostGuardService.php` (existente, reutilizado)  
**Model:** `app/Models/AiUsageRecord.php` (expandido)  
**Tela:** `/admin/ai-usage`

Config em `.env`:
```
AI_DAILY_REQUEST_LIMIT=20
AI_MONTHLY_BUDGET_USD=20
```

Bloqueia execução se:
- Limite diário de requests atingido
- Orçamento mensal excedido

---

## Pesquisa Externa (Opcional)

**Config:** `config/external-research.php`  
**Interface:** `app/Services/Research/ExternalResearchProviderInterface.php`

```
EXTERNAL_RESEARCH_ENABLED=false
EXTERNAL_RESEARCH_PROVIDER=disabled
```

Comportamento quando desabilitada, conforme `if_research_unavailable`:
- `block_execution` — Falha controlada com erro claro
- `generate_evergreen` — Gera conteúdo atemporal sem alegar notícia recente
- `ask_human` — Cria pacote pedindo input humano

---

## Segurança por Escopo

- Toda tabela nova tem `company_id` / `client_id`
- Todos os models têm `scopeVisibleTo(User $user)`
- Controllers aplicam `visibleTo` antes de retornar dados
- Memória de empresa A nunca entra no prompt de empresa B
- Admin geral vê tudo; operadores veem apenas company_id
- Tokens e API keys nunca aparecem em telas/logs
- Prompt salvo apenas como preview sanitizado (max 1000 chars)

---

## Comandos

```bash
# Executar tarefas vencidas
php artisan agents:run-due-tasks

# Dry run
php artisan agents:run-due-tasks --dry-run

# Forçar tarefa específica
php artisan agents:run-due-tasks --task=ID --force

# Diagnosticar tarefa
php artisan agents:diagnose-task {ID}

# Reconstruir contextos compactos
php artisan agents:rebuild-compact-contexts

# Ciclo completo de publicação
php artisan content:run-publishing-cycle
```

---

## Telas

| URL | Função |
|-----|--------|
| `/admin/agent-tasks` | Lista de tarefas operacionais |
| `/admin/agent-tasks/create` | Criar nova tarefa |
| `/admin/agent-tasks/{id}` | Detalhe + últimas execuções |
| `/admin/agent-tasks/{id}/runs` | Histórico de execuções |
| `/admin/agent-tasks/{id}/packages` | Pacotes gerados |
| `/admin/ai-memory` | Central de memória IA |
| `/admin/ai-usage` | Uso e consumo de IA |
| `/admin/approvals` | Aprovações (inclui pacotes gerados) |

---

## Feature Flags

```php
// config/features.php
'ai_task_engine_real'          => true,   // Motor principal ativo
'ai_operational_memory'        => true,   // Memória operacional
'ai_context_snapshots'         => true,   // Snapshots de execução
'ai_task_recurring_execution'  => true,   // Execução recorrente
'ai_cost_guard'                => true,   // Controle de custo
'ai_content_package_approval'  => true,   // Aprovação por pacote

'auto_publish_without_approval' => false, // NUNCA publicar sem aprovação
'mock_content_generation'       => false, // NUNCA gerar conteúdo fake
'demo_mode'                     => false, // NUNCA modo demo em produção
```

---

## Erros Comuns

| Erro | Causa | Solução |
|------|-------|---------|
| "Gemini não configurado" | `GEMINI_API_KEY` ausente ou `AI_PROVIDER != google` | Configurar `.env` |
| "Tarefa não possui funcionário IA ativo" | Nenhum `AiEmployee` vinculado ou inativo | Vincular funcionário IA na tarefa |
| "A IA retornou conteúdo inválido" | Resposta não é JSON | Verificar prompt e tentar novamente |
| "Limite de uso de IA atingido" | `AI_DAILY_REQUEST_LIMIT` excedido | Aumentar limite ou aguardar reset |
| Imagem `pending_configuration` | `IMAGE_GENERATION_ENABLED=false` | Habilitar se necessário |

---

## Testes de Regressão

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan optimize:clear
php artisan agents:run-due-tasks --dry-run
php artisan agents:rebuild-compact-contexts
php artisan agents:diagnose-routines
php artisan content:run-publishing-cycle
php artisan blog:publish-due
php artisan social:publish-due
php artisan system:health-check
```
