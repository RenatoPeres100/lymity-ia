# Scheduler — Lymity IA

## O que é o Scheduler?

O Laravel Task Scheduler permite agendar comandos Artisan para execução automática. Na Lymity IA, ele executa os agendamentos dos Funcionários IA, health checks periódicos e outras tarefas de manutenção.

## Configurar no Cron da VPS

Adicione **uma única linha** ao crontab do servidor:

```bash
crontab -e
```

Adicione:

```
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

Esta linha executa o scheduler a cada minuto. O Laravel decide internamente quais tarefas devem rodar com base nas definições em `routes/console.php`.

## Verificar o crontab atual

```bash
crontab -l
```

## Tarefas agendadas no projeto

| Tarefa | Frequência | Descrição |
|--------|-----------|-----------|
| `ai:run-schedules` | A cada 5 minutos | Executa agendamentos dos Funcionários IA |
| `system:health-check` | A cada hora | Verifica saúde do sistema e registra log |

## Testar o scheduler manualmente

```bash
# Executar todas as tarefas devidas agora
php artisan schedule:run

# Ver lista de tarefas agendadas
php artisan schedule:list

# Testar uma tarefa específica sem aguardar o horário
php artisan schedule:test
```

## Arquivo de configuração

O scheduler é configurado em `routes/console.php`.

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('ai:run-schedules')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('system:health-check')->hourly()->withoutOverlapping();
```

## Overlapping (sobreposição)

O `->withoutOverlapping()` garante que a tarefa não seja iniciada se uma execução anterior ainda estiver em andamento. Importante para tarefas longas como `ai:run-schedules`.

## Logs do scheduler

Para registrar saída do scheduler:

```
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> storage/logs/scheduler.log 2>&1
```

> **Atenção:** Isso pode gerar arquivos de log grandes. Prefira `>> /dev/null 2>&1` e confie nos logs internos do Laravel.

## Verificar se o scheduler está funcionando

1. Acesse `/admin/system-health` no painel
2. Confira o campo "Último Health Check" — deve estar atualizado
3. Ou rode: `php artisan schedule:run` e verifique os logs
