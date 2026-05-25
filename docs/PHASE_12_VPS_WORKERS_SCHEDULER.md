# Fase 12 — VPS, Workers e Scheduler

## Objetivo

Garantir que a plataforma Lymity IA funcione 24/7 na VPS, mesmo com o computador local desligado. Esta fase cobre:

- Configuração de queue workers persistentes via Supervisor
- Agendamento automático via cron + Laravel Scheduler
- Health check do sistema (command + tela admin)
- Scripts de verificação de deploy
- Documentação completa de produção
- Exemplos de configuração Nginx e Supervisor

## O que foi criado

### Documentação

| Arquivo | Descrição |
|---------|-----------|
| `docs/DEPLOY_VPS.md` | Guia completo de deploy na VPS |
| `docs/QUEUE_WORKERS.md` | Como configurar e monitorar workers |
| `docs/SCHEDULER.md` | Como configurar o scheduler no cron |
| `docs/ENVIRONMENT.md` | Variáveis de ambiente e segurança |
| `docs/PRODUCTION_CHECKLIST.md` | Checklist antes de ir para produção |

### Arquivos de Configuração de Exemplo

| Arquivo | Descrição |
|---------|-----------|
| `.env.production.example` | Template seguro de variáveis de ambiente |
| `supervisor/laravel-worker.conf.example` | Configuração do Supervisor para workers |
| `nginx/lymity.conf.example` | Configuração do Nginx para o projeto |

### Scripts

| Arquivo | Descrição |
|---------|-----------|
| `scripts/deploy-check.sh` | Verifica pré-requisitos antes do deploy |
| `scripts/health-check.sh` | Executa health check rápido do sistema |

### Código PHP

| Arquivo | Descrição |
|---------|-----------|
| `app/Services/System/SystemHealthService.php` | Service com toda a lógica de health check |
| `app/Console/Commands/SystemHealthCheckCommand.php` | Command `php artisan system:health-check` |
| `app/Console/Commands/QueueTestCommand.php` | Command `php artisan queue:test` |
| `app/Jobs/TestQueueJob.php` | Job de teste da fila |
| `app/Http/Controllers/Admin/SystemHealthController.php` | Controller da tela admin |
| `resources/views/admin/system-health/index.blade.php` | View da tela de health |

### Rotas

- `GET /admin/system-health` → Tela visual de saúde do sistema

### Scheduler (routes/console.php)

- `ai:run-schedules` — a cada 5 minutos
- `system:health-check` — a cada hora

## Como ativar em produção

### 1. Queue Workers (Supervisor)

```bash
sudo cp supervisor/laravel-worker.conf.example /etc/supervisor/conf.d/laravel-worker.conf
# Revisar: usuário, caminho, versão PHP
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
sudo supervisorctl status
```

### 2. Scheduler (Cron)

```bash
crontab -e
# Adicionar:
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Verificar funcionamento

```bash
php artisan system:health-check
php artisan queue:test
php artisan queue:work --stop-when-empty
php artisan schedule:run
```

## Credenciais de teste

```
URL Admin: http://IP:8000/admin/system-health
Login: admin@lymity.local
Senha: password
```
