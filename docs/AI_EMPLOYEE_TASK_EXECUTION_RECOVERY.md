# AI Employee Task Execution — Guia de Recuperação

Guia de referência para diagnosticar e restaurar o motor de execução de tarefas dos Funcionários IA.

## Cadeia de Execução

```
crontab (1/min)
  └─ php artisan schedule:run
       └─ agents:run-due-tasks (a cada 5min, withoutOverlapping)
            └─ AgentTask [status=active, next_run_at<=now, frequency!=manual]
                 └─ RunAgentTaskJob → fila Redis (queue:work)
                      └─ AgentTaskExecutionService::runTaskNow()
                           ├─ AgentTaskRun (created, status=running)
                           ├─ AiEmployee (active?)
                           ├─ Agency Brand Context (active?)
                           ├─ AiExecutionContext (compact context)
                           ├─ AIProviderManager → GoogleGeminiProvider
                           ├─ generateText() → conteúdo gerado
                           ├─ GeneratedContentPackage (created)
                           ├─ ApprovalRequest (created, status=pending)
                           └─ AgentTaskRun (status=waiting_approval)
```

## Diferença: AgentTask vs AiEmployee

| Conceito       | Model          | status field | O que bloqueia |
|----------------|----------------|-------------|----------------|
| **AgentTask**  | `agent_tasks`  | `status=active` | Task não é executada |
| **AiEmployee** | `ai_employees` | `status=active` | Task falha ao criar o run |

Ambos precisam estar `active`. Uma task com funcionário inativo falhará silenciosamente.

## Pré-requisitos para execução

1. **Cron instalado** — `crontab -l` deve conter `lymity-ia && php artisan schedule:run`
2. **Queue worker rodando** — `sudo supervisorctl status` → `RUNNING`
3. **Redis online** — `redis-cli ping` → `PONG`
4. **AgentTask.status = active** e `next_run_at <= now()`
5. **AiEmployee.status = active** vinculado à task
6. **Agency Brand Context ativo** (pelo menos um registro com `active=true`)
7. **AI_PROVIDER=google** e `GEMINI_API_KEY` configurados

## Comandos de Diagnóstico

```bash
# Saúde completa do motor de execução (começa aqui)
php artisan agents:task-execution-health

# Diagnóstico de task específica (cobre tudo: employee, brand, provider, limites)
php artisan agents:diagnose-task 2    # task ID 2
php artisan agents:diagnose-task 3    # task ID 3

# Ver tasks que seriam executadas agora
php artisan agents:run-due-tasks --dry-run

# Saúde geral do sistema (cron, Redis, scheduler, módulos)
php artisan lymity:cron-health

# Regressão completa (9 módulos)
php artisan lymity:regression-check
```

## Como Verificar o Cron

```bash
crontab -l | grep schedule:run
# Deve retornar:
# * * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

Se ausente ou apontando para outro diretório:
```bash
(crontab -l 2>/dev/null | grep -v "schedule:run"; echo "* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

## Como Verificar o Scheduler

```bash
php artisan schedule:list | grep agents
# Deve mostrar: agents:run-due-tasks (every 5 minutes)
```

## Como Verificar Queue e Redis

```bash
# Redis ping
redis-cli ping   # deve retornar PONG

# Supervisor
sudo supervisorctl status

# Jobs pendentes na fila
php artisan queue:pending

# Failed jobs
php artisan queue:failed
```

### Se supervisor estiver em FATAL:

```bash
# 1. Verificar a causa (geralmente PHP Fatal Error)
sudo supervisorctl tail lymity-worker:0 stderr

# 2. Testar bootstrap manualmente
php artisan list > /dev/null && echo "OK" || echo "BOOTSTRAP FALHOU"

# 3. Reiniciar após corrigir o código
sudo supervisorctl restart lymity-worker:*
```

## Como Reparar next_run_at

```bash
# Verificar tasks com schedule incorreto
php artisan agents:repair-task-schedules --dry-run

# Aplicar correção
php artisan agents:repair-task-schedules --fix
```

Problemas detectados:
- Task ativa com `next_run_at = null` e frequência ≠ manual
- Task com `next_run_at` mais de 7 dias no passado

## Como Corrigir Runs Travados

```bash
# Verificar (mostra runs in-progress por >2h)
php artisan agents:repair-task-execution --dry-run

# Aplicar correção (marca como failed + limpa cache locks)
php artisan agents:repair-task-execution --fix
```

## Como Executar Task Manualmente

### Via queue (normal)
```bash
php artisan agents:run-due-tasks --task=2 --force
```

### Via execução síncrona (para diagnóstico — não depende do worker)
```bash
php artisan agents:run-due-tasks --task=2 --force --sync
```
Com `--sync`, o job é executado diretamente no processo atual sem passar pela fila Redis.
Útil para depurar quando o worker está offline ou para testar a cadeia completa.

### Via dry-run (só mostra o que executaria)
```bash
php artisan agents:run-due-tasks --dry-run
php artisan agents:run-due-tasks --task=2 --dry-run
```

## Limpar Cache Locks do Scheduler

Se o scheduler estiver travado por `withoutOverlapping()`:
```bash
php artisan schedule:clear-cache
```

Ou via repair command:
```bash
php artisan agents:repair-task-execution --fix
```

## Causa Raiz Conhecida — PHP Fatal Error em Commands

Se um comando Artisan define um método com visibilidade mais restritiva que o método pai (`Illuminate\Console\Command`), o PHP lança Fatal Error ao carregar a aplicação, derrubando todos os supervisor workers.

**Métodos públicos do Command que NÃO podem ser redefinidos como private/protected:**
- `fail()` — use `err()` ou `fail_()` como alternativa
- `warn()` — use `caution()` ou `notice_()` como alternativa
- `info()`, `line()`, `error()`, `comment()`, `question()`

Sinal de alerta: supervisor em estado FATAL logo após deploy de um novo comando.

Diagnóstico:
```bash
php artisan list > /dev/null && echo "OK" || echo "FATAL"
sudo supervisorctl tail lymity-worker:0 stderr | grep Fatal
```

## AIProviderManager — Classes Paralelas (Atenção)

Existem **duas classes** `AiProviderManager` no projeto:

| Namespace | Caminho | Comportamento para AI_PROVIDER=google |
|-----------|---------|--------------------------------------|
| `App\Services\AI\AIProviderManager` | `app/Services/AI/AIProviderManager.php` | ✅ Retorna `GoogleGeminiProvider` |
| `App\Services\Ai\AiProviderManager` | `app/Services/Ai/AiProviderManager.php` | ❌ Retorna `MockAiProvider` |

O `AgentTaskExecutionService` usa corretamente o `App\Services\AI\AIProviderManager` (maiúsculo).  
Nunca usar o namespace lowercase `App\Services\Ai\` em novos serviços.

## Limites e Guardrails Ativos

| Guardrail | Valor | Onde |
|-----------|-------|------|
| Daily request limit | 10 req/dia | `config/ai.php` + `AgentTask::hasReachedDailyLimit()` |
| Monthly cost limit | $10/mês | `AiCostGuardService` |
| `AI_REQUIRE_APPROVAL` | true (prod) | Toda publicação requer aprovação |
| `THREADS_PUBLISHING_ENABLED` | false | Threads não publica automaticamente |
| `threads_publishing_scheduler` | false | threads:publish-due fora do scheduler |

## Status Esperado dos Runs

| Status | Descrição |
|--------|-----------|
| `queued` | Job na fila Redis, aguardando worker |
| `running` | Worker processando (normal < 5min) |
| `preparing_context` | Montando Brand Context |
| `generating_text` | Chamada ativa à API Gemini |
| `waiting_approval` | ✅ Concluído — aguarda revisão humana |
| `completed` | Aprovado e publicado |
| `failed` | Erro — ver `error_message` |

Run em `running` por mais de 2 horas → rodar `agents:repair-task-execution --fix`.

## Fluxo de Aprovação

```
waiting_approval → /admin/approvals → Aprovar → SocialPost/BlogPost.status=approved
                                    → Rejeitar → status=rejected
                                    → Após aprovação → agendamento automático (social:publish-due / blog:publish-due)
```

## Comandos de Recuperação Completa

Executar nesta ordem quando o sistema parece travado:

```bash
# 1. Verificar se o PHP inicia
php artisan list > /dev/null && echo "OK"

# 2. Verificar workers
sudo supervisorctl status

# 3. Reiniciar workers se FATAL
sudo supervisorctl restart lymity-worker:*

# 4. Verificar saúde geral
php artisan agents:task-execution-health

# 5. Reparar schedules se necessário
php artisan agents:repair-task-schedules --dry-run
php artisan agents:repair-task-schedules --fix

# 6. Reparar execução se necessário
php artisan agents:repair-task-execution --dry-run
php artisan agents:repair-task-execution --fix

# 7. Testar task manualmente (sync para não depender do worker)
php artisan agents:run-due-tasks --task=2 --force --sync

# 8. Regressão final
php artisan lymity:regression-check
```
