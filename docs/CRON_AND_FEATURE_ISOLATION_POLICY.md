# Política de Isolamento de Features e Integridade de Crons

## Regra Absoluta

**Nenhuma nova funcionalidade pode quebrar funcionalidades existentes.**

Se uma nova feature causar falha em Blog, Social, Instagram, AgentTasks, Aprovações ou Scheduler, o desenvolvedor deve:
1. Isolar a feature imediatamente por feature flag
2. Garantir que o command da feature retorne `SUCCESS` quando desabilitada
3. Rodar `php artisan lymity:regression-check` antes de finalizar

---

## Cron do Servidor

O cron do Laravel deve apontar **sempre** para `/var/www/lymity-ia`:

```cron
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

Para verificar: `crontab -l`

Para corrigir:
```bash
(crontab -l 2>/dev/null | grep -v "schedule:run"; echo "* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

---

## Feature Flags Obrigatórias para Módulos de Publicação

Toda nova integração de publicação deve ter feature flags em `config/features.php`:

```php
// Exemplo: módulo fictício "WhatsApp"
'whatsapp_connection'        => false, // tela de conexão visível
'whatsapp_text_publishing'   => false, // criação de post permitida
'whatsapp_publishing_scheduler' => false, // agendado no scheduler
```

A flag `*_publishing_scheduler` controla se o command de publicação aparece no scheduler (`routes/console.php`).

---

## Threads — Isolamento

Threads está isolado por dupla proteção:

| Camada | Onde | Efeito |
|--------|------|--------|
| Feature flag `threads_publishing_scheduler` | `config/features.php` | Se `false`, o command não é agendado em `routes/console.php` |
| Env `THREADS_PUBLISHING_ENABLED` | `.env` / `config/threads.php` | Se `false`, o command retorna `SUCCESS` sem publicar |
| Feature flag `threads_text_publishing` | `config/features.php` | Se `false`, o command retorna `SUCCESS` sem publicar |

**Estado padrão (seguro):**
```php
'threads_publishing_scheduler' => false, // config/features.php
```
```dotenv
THREADS_PUBLISHING_ENABLED=false  # .env
```

**Para habilitar Threads em produção**, siga esta ordem:
1. Conectar canal em `/admin/social/threads`
2. Testar publicação manual
3. Setar `THREADS_PUBLISHING_ENABLED=true` no `.env`
4. Setar `threads_publishing_scheduler=true` em `config/features.php`
5. Rodar `php artisan config:clear && php artisan optimize:clear`

---

## content:run-publishing-cycle — Resiliência

O comando `content:run-publishing-cycle` executa módulos de forma isolada:

```
[0] agents:run-due-tasks      → try/catch isolado
[1] agents:run-due-routines   → try/catch isolado
[2] content:fix-approved-scheduled → try/catch isolado
[3] blog:publish-due          → try/catch isolado
[4] social:publish-due        → try/catch isolado
[5] threads:publish-due       → try/catch isolado + condicionado a threads_publishing_scheduler
```

Um erro em Threads **não para** Blog nem Social.
Um erro em Blog **não para** Social.
Um erro em Social **não para** AgentTasks.

---

## Comandos Principais e Frequência

| Comando | Frequência | Independência |
|---------|-----------|--------------|
| `agents:run-due-tasks` | A cada minuto | Não depende de Threads |
| `blog:publish-due` | A cada minuto | Independente |
| `social:publish-due` | A cada minuto | Não usa Threads |
| `agents:run-due-routines` | A cada minuto | Independente |
| `system:health-check` | Hourly | Independente |
| `instagram:refresh-tokens` | Daily 03:00 | Independente |
| `approvals:send-pending-reminders` | Hourly | Independente |
| `threads:publish-due` | A cada minuto (quando `threads_publishing_scheduler=true`) | Isolado |

---

## Comandos de Diagnóstico

### `php artisan lymity:cron-health`
Verifica:
- Cron do servidor instalado corretamente
- Todos os comandos principais no scheduler
- Redis/queue
- AgentTasks ativas e próximas execuções
- Últimos AgentTaskRuns
- Falhas recentes (24h)
- Isolamento Threads
- Últimos erros de schedule no log

**Exit code 0** = sistema saudável  
**Exit code 1** = problemas críticos detectados

### `php artisan lymity:regression-check`
Executa checklist completa de todos os módulos:
- system:health-check
- agents:diagnose-execution-engine
- lymity:cron-health
- lymity:stability-check
- agents:run-due-tasks --dry-run
- instagram:diagnose-publishing
- threads:diagnose
- approvals:diagnose-email
- prospecting:diagnose

Exibe resultado final: **OK / WARN / FAIL / SKIP** por módulo.

---

## Checklist Obrigatório Antes de Finalizar Qualquer Feature

Antes de fazer `git push` com uma nova feature:

```bash
# 1. Limpar caches
php artisan optimize:clear
php artisan config:clear

# 2. Verificar scheduler
php artisan schedule:list

# 3. Rodar regressão completa
php artisan lymity:regression-check

# 4. Testar comandos principais
php artisan agents:run-due-tasks --dry-run
php artisan blog:publish-due
php artisan social:publish-due
php artisan threads:publish-due  # deve retornar SUCCESS mesmo desabilitado
```

**Critério de aceitação:**  
Nenhum dos comandos principais pode falhar por causa da nova feature.  
`lymity:regression-check` deve retornar sem FAIL.

---

## Política de Novo Módulo

Ao criar um novo módulo de publicação (ex: YouTube, TikTok):

1. Adicionar feature flags em `config/features.php`:
   - `nome_connection` → controla a tela de conexão
   - `nome_publishing` → controla criação de conteúdo
   - `nome_publishing_scheduler` → controla o agendamento (padrão: `false`)

2. O command `nome:publish-due` deve:
   - Verificar `config('features.nome_publishing', false)` → retornar SUCCESS se false
   - Verificar `config('nome.publishing_enabled', false)` → retornar SUCCESS se false
   - Processar cada item em `try/catch` individual
   - Nunca lançar exception não capturada do `handle()`

3. Em `routes/console.php`, agendar condicionalmente:
   ```php
   if (config('features.nome_publishing_scheduler', false)) {
       Schedule::command('nome:publish-due')->everyMinute()->withoutOverlapping()->runInBackground();
   }
   ```

4. Rodar `php artisan lymity:regression-check` antes do push.
