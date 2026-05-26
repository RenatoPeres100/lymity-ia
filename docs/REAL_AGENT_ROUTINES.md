# Real Agent Routines — Phase 5

## Objetivo

Transformar os funcionários IA ativos da Lymity em agentes operacionais com rotina real, agenda, execução controlada, criação de conteúdo, aprovação e logs.

Foco exclusivo nesta fase:
- Agência Lymity (sem clientes externos)
- Blog da agência
- Instagram da agência
- Social Media IA, Copywriter IA, Blog Writer IA

---

## Agentes Ativos

| Slug | Nome | Status |
|---|---|---|
| `social-media-ia` | Social Media IA | active |
| `copywriter-ia` | Copywriter IA | active |
| `blog-writer-ia` | Blog Writer IA | active |

Demais agentes permanecem cadastrados com status `paused`.

---

## Agency Brand Context

### O que é
O contexto da marca é a base de conhecimento que os agentes IA usam para gerar conteúdo alinhado com a identidade da Lymity.

### Campos
- `brand_name` — Nome da marca
- `positioning` — Posicionamento estratégico
- `tone_of_voice` — Tom de comunicação
- `target_audience` — Público-alvo
- `main_services` — Principais serviços
- `forbidden_terms` — Termos proibidos
- `preferred_terms` — Termos preferidos
- `cta_examples` — Exemplos de Call to Action
- `content_guidelines` — Diretrizes de conteúdo
- `visual_guidelines` — Diretrizes visuais

### Bloqueio
Se o Brand Context estiver vazio ou incompleto (faltam brand_name, positioning, tone_of_voice, target_audience), todas as rotinas são bloqueadas.

### Tela
`GET /admin/agency/brand-context`

---

## Tabela `agent_routines`

Armazena as rotinas configuradas para cada agente.

| Campo | Tipo | Descrição |
|---|---|---|
| `ai_employee_id` | FK | Agente responsável |
| `company_id` | FK nullable | Empresa alvo |
| `client_id` | FK nullable | Cliente (null = agência) |
| `routine_type` | enum | `social_post_creation`, `blog_post_creation`, `copy_improvement`, `content_review` |
| `frequency` | enum | `daily`, `weekly`, `monthly` |
| `days_of_week` | json | Ex: `["monday","tuesday"]` |
| `time_of_day` | time | Horário de execução |
| `content_quantity` | int | Quantidade por execução |
| `active` | bool | Rotina ativa/pausada |
| `requires_approval` | bool | Exige aprovação humana |
| `next_run_at` | datetime | Próxima execução agendada |
| `last_run_at` | datetime | Última execução realizada |

---

## Tabela `agent_routine_runs`

Histórico de cada execução de rotina.

| Campo | Tipo | Descrição |
|---|---|---|
| `agent_routine_id` | FK | Rotina executada |
| `ai_employee_id` | FK | Agente que executou |
| `status` | enum | `queued`, `running`, `completed`, `failed` |
| `started_at` | datetime | Início da execução |
| `finished_at` | datetime | Fim da execução |
| `output_summary` | text | Resumo do resultado |
| `error_message` | text | Mensagem de erro, se houver |

---

## AgentRoutineService

**Arquivo:** `app/Services/Agents/AgentRoutineService.php`

### Métodos

| Método | Descrição |
|---|---|
| `getDueRoutines()` | Busca rotinas com `next_run_at <= now()` |
| `runRoutine($routine)` | Executa uma rotina completa |
| `ensureCanGenerate($routine)` | Guard: bloqueia se provider não configurado ou brand context incompleto |
| `getAgencyBrandContext($routine)` | Carrega o contexto da marca |
| `calculateNextRun($routine)` | Calcula próxima execução baseada em frequency e days_of_week |
| `registerRoutineRun($routine)` | Cria AgentRoutineRun com status queued |
| `completeRoutineRun($run, $summary)` | Marca run como completed |
| `failRoutineRun($run, $error)` | Marca run como failed |
| `createSocialPostFromRoutine($routine, $run)` | Cria SocialPost + ApprovalRequest |
| `createBlogPostFromRoutine($routine, $run)` | Cria BlogPost + ApprovalRequest |
| `createCopyReviewFromRoutine($routine, $run)` | Revisa conteúdos pendentes |

---

## RunAgentRoutineJob

**Arquivo:** `app/Jobs/RunAgentRoutineJob.php`

- Recebe `agent_routine_id` via construtor
- Carrega a rotina do banco
- Chama `AgentRoutineService->runRoutine()`
- Captura qualquer exceção sem quebrar o worker
- Registra falha em caso de erro crítico
- `tries = 1`, `timeout = 120s`

---

## Command: `agents:run-due-routines`

**Arquivo:** `app/Console/Commands/RunDueAgentRoutinesCommand.php`

```bash
# Modo padrão (enfileira jobs para o worker processar)
php artisan agents:run-due-routines

# Modo síncrono (executa direto, útil para debug)
php artisan agents:run-due-routines --sync
```

Fluxo:
1. Busca rotinas com `next_run_at <= now()` e `active = true`
2. Para cada rotina: despacha `RunAgentRoutineJob` (ou executa direto com `--sync`)
3. Imprime resultado com ícones ✓ / ✗ / ⏳
4. Registra logs de execução

---

## Scheduler

**Arquivo:** `routes/console.php`

```php
Schedule::command('agents:run-due-routines')->everyMinute()->withoutOverlapping();
```

O crontab da VPS já deve ter:
```
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

---

## Bloqueio sem Provider Real

Quando `AI_REAL_ENABLED=false` ou `AI_PROVIDER=mock`:

1. `ensureCanGenerate()` lança `RuntimeException` com mensagem clara
2. `runRoutine()` chama `failRoutineRun()` com o erro
3. `AgentRoutineRun.status = 'failed'`
4. `AgentRoutineRun.error_message` explica o problema
5. **Nenhum conteúdo é criado** (sem SocialPost, sem BlogPost)
6. ActivityLog `agent_routine_blocked_missing_ai_provider` é registrado

Para ativar geração real:
```env
AI_REAL_ENABLED=true
AI_PROVIDER=anthropic  # ou openai
ANTHROPIC_API_KEY=sk-ant-...
```

---

## Bloqueio sem Brand Context

Quando `AgencyBrandContext` está vazio ou incompleto:

1. `ensureCanGenerate()` verifica `isComplete()`
2. Campos obrigatórios: `brand_name`, `positioning`, `tone_of_voice`, `target_audience`
3. Se incompleto: lança `RuntimeException`
4. Run marcado como `failed`
5. ActivityLog `agent_routine_blocked_missing_brand_context` registrado

Para configurar: `GET /admin/agency/brand-context`

---

## Fluxo de Aprovação

Quando um agente cria conteúdo com `requires_approval = true`:

1. `SocialPost` ou `BlogPost` criado com status `pending_approval`
2. `ApprovalRequest` criada:
   - `approvable_type` = classe do modelo
   - `approvable_id` = id do conteúdo
   - `approval_type` = `post` ou `blog`
   - `status` = `pending`
   - `payload` = `{routine_id, run_id, company_id, agent}`
3. Painel `/admin/approvals` exibe a aprovação pendente
4. Admin revisa e aprova/rejeita manualmente
5. Conteúdo aprovado entra no pipeline de publicação

**Conteúdo nunca é publicado automaticamente.**

---

## Como Testar

### 1. Verificar brand context
```bash
php artisan tinker
>>> App\Models\AgencyBrandContext::first()?->isComplete()
# Deve retornar true
```

### 2. Verificar rotinas
```bash
php artisan tinker
>>> App\Models\AgentRoutine::all(['id','title','active','next_run_at'])
```

### 3. Executar rotinas manualmente (sem provider = falha controlada)
```bash
php artisan agents:run-due-routines --sync
# Resultado esperado: todos failed com "Provider de IA não configurado"
```

### 4. Ativar provider real e testar
```bash
# .env
AI_REAL_ENABLED=true
AI_PROVIDER=anthropic
ANTHROPIC_API_KEY=sk-ant-...

php artisan config:cache
php artisan agents:run-due-routines --sync
# Resultado: SocialPost e BlogPost criados com status pending_approval
# ApprovalRequest criada para cada conteúdo
```

### 5. Verificar resultados
```bash
php artisan tinker
>>> App\Models\AgentRoutineRun::latest()->first()
>>> App\Models\ApprovalRequest::where('status','pending')->count()
```

---

## Rotinas Iniciais

| Agente | Tipo | Frequência | Dias |
|---|---|---|---|
| Social Media IA | `social_post_creation` | Diário | Seg–Sex |
| Blog Writer IA | `blog_post_creation` | Semanal | Ter e Qui |
| Copywriter IA | `copy_improvement` | Diário | Seg–Sex |

---

## Próximos Passos

- [ ] Configurar `AI_REAL_ENABLED=true` com provider real (Anthropic ou OpenAI)
- [ ] Integrar prompts específicos por tipo de rotina no `AgentRoutineService`
- [ ] Geração de imagens para posts via IA (Designer IA)
- [ ] Agendamento de publicação após aprovação
- [ ] Dashboard de performance das rotinas
- [ ] Notificações por email quando aprovação é criada
- [ ] Histórico de conteúdo gerado por agente (métricas)
