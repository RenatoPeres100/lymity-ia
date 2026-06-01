# Motor Autônomo de Rotinas de Agentes IA

## Conceito

```
AiEmployee (quem)       → Blog Writer IA, Social Media IA, Copywriter IA
AgentRoutine (o quê)    → configuração: frequência, quantidade, dias, horário
AgentRoutineRun (quando)→ registro de cada execução real
AiTask (tarefa)         → operação criada pela execução
Conteúdo (resultado)    → BlogPost, SocialPost
ApprovalRequest         → solicitação de revisão humana obrigatória
```

## Fluxo completo

```
Scheduler → content:run-publishing-cycle (a cada minuto)
  ↓
agents:run-due-routines
  ↓ para cada rotina devida:
  └→ cria AgentRoutineRun com run_key único
  └→ BlogRoutineHandler / SocialRoutineHandler / CopywriterRoutineHandler
      └→ gera N conteúdos com Gemini (quantity_per_run)
      └→ status pending_approval ou draft
      └→ cria ApprovalRequest por conteúdo
      └→ calcula next_run_at
  ↓
blog:publish-due    → publica blogs aprovados/agendados
social:publish-due  → publica posts sociais aprovados/agendados
```

**A rotina NÃO publica. Ela cria conteúdo e solicita aprovação.**

---

## Campos da rotina (agent_routines)

| Campo | Descrição |
|-------|-----------|
| `ai_employee_id` | Funcionário IA responsável |
| `routine_type` | `blog_post_creation`, `social_post_creation`, `copy_improvement` |
| `frequency` | `daily`, `weekly`, `monthly`, `manual` |
| `days_of_week` | `["monday","wednesday","friday"]` |
| `time_of_day` | `"09:00"` — horário de execução |
| `quantity_per_run` | Quantidade de itens por execução |
| `approval_lead_days` | Quantos dias de antecedência em relação à publicação |
| `publication_time` | Horário alvo de publicação (opcional) |
| `status` | `active`, `paused`, `disabled` |
| `requires_approval` | Se gera ApprovalRequest para cada conteúdo |
| `next_run_at` | Próxima execução calculada |
| `last_run_at` | Última execução |
| `last_error` | Último erro registrado |

---

## Run Key (duplicate guard)

Formato: `routine:{id}:slot:{YYYY-MM-DD-HH-mm}`

Se já existir um `AgentRoutineRun` com o mesmo `run_key`, a execução é ignorada. Impede duplicidade quando o cron roda múltiplas vezes no mesmo minuto.

---

## Campos de execução (agent_routine_runs)

| Campo | Descrição |
|-------|-----------|
| `run_key` | Chave única de idempotência |
| `status` | `pending`, `running`, `completed`, `failed`, `skipped`, `canceled` |
| `items_requested` | Quantidade solicitada |
| `items_created` | Quantidade criada com sucesso |
| `approvals_created` | Aprovações geradas |
| `scheduled_for` | Data/hora de quando deveria rodar |
| `error_message` | Erro, se houver |

---

## Commands

```bash
# Executar rotinas devidas (usado pelo scheduler)
php artisan agents:run-due-routines

# Opções:
--dry-run        # Simula sem criar conteúdo
--routine=ID     # Executa apenas esta rotina
--force          # Força execução mesmo que next_run_at seja futuro
--now="2026-06-03 09:00"  # Simula data/hora

# Diagnóstico
php artisan agents:diagnose-routines

# Recalcular próximas execuções
php artisan agents:recalculate-next-runs
php artisan agents:recalculate-next-runs --routine=ID
php artisan agents:recalculate-next-runs --dry-run

# Ciclo completo (agents + blog publish + social publish)
php artisan content:run-publishing-cycle
```

---

## Blog Writer IA

O `BlogRoutineHandler` para cada item:
1. Gera plano (título, subtitle, seo, outline) via Gemini JSON
2. Gera conteúdo HTML completo
3. Cria `BlogPost` com `status=pending_approval`
4. Calcula `scheduled_at` = `now() + approval_lead_days` no horário de publicação
5. Cria `ApprovalRequest`

Temas são variados automaticamente e não repetem os últimos 20 posts.

---

## Social Media IA

O `SocialRoutineHandler` para cada item:
1. Gera legenda, hashtags, CTA e brief via Gemini JSON
2. Cria `SocialPost` com `status=pending_approval`, `image_status=missing`
3. Se `metadata.auto_generate_image=true`: tenta gerar imagem com Gemini (pode falhar sem bloquear o fluxo)
4. Cria `ApprovalRequest`

Imagem é **sempre posterior** ao texto — a rotina não bloqueia por falta de imagem.

---

## Copywriter IA

O `CopywriterRoutineHandler`:
1. Busca BlogPosts e SocialPosts em `pending_approval` ou `draft` sem `revision_notes`
2. Gera sugestões via Gemini
3. Salva em `revision_notes` (BlogPost) ou `metadata.copywriter_suggestion` (SocialPost)
4. **NÃO aprova, NÃO publica**

---

## Aprovação após conteúdo criado

1. Admin acessa `/admin/approvals`
2. Revisa o conteúdo
3. Aprova → conteúdo vai para `approved` ou `scheduled`
4. `blog:publish-due` / `social:publish-due` publicam no horário

Se o conteúdo tiver `scheduled_at` futuro → `status=scheduled`
Se não tiver `scheduled_at` → `status=approved` (publicável imediatamente)

---

## Agendamento e Scheduler

Laravel Scheduler (routes/console.php):
```php
Schedule::command('agents:run-due-routines')->everyMinute()->withoutOverlapping();
Schedule::command('blog:publish-due')->everyMinute()->withoutOverlapping();
Schedule::command('social:publish-due')->everyMinute()->withoutOverlapping();
```

O cron do sistema executa `php artisan schedule:run` a cada minuto.

---

## Erros comuns

| Erro | Causa | Solução |
|------|-------|---------|
| "Provider de IA não configurado" | `GEMINI_API_KEY` ausente ou `AI_PROVIDER≠google` | Verificar `.env` |
| "high demand" / 429 | Rate limit do Gemini free tier | Aguardar ou fazer upgrade |
| "Array to string conversion" | Gemini retornou array onde esperava string | Handler normaliza automaticamente |
| Duplicate guard ativado | Mesmo run_key já existe | Correto — impede duplicidade |
| Rotina não executa | `active=false` ou `status=paused` | Ativar em `/admin/agents/routines` |

---

*Última atualização: 2026-06-01*
