# Queue Workers — Lymity IA

## O que é o Queue Worker?

O `queue:work` é o processo que processa jobs da fila em background. Na Lymity IA, ele é responsável por executar tarefas dos Funcionários IA, enviar e-mails, processar conteúdo gerado por IA e outras operações assíncronas.

## Iniciar manualmente (desenvolvimento)

```bash
php artisan queue:work
```

Para parar quando a fila estiver vazia:

```bash
php artisan queue:work --stop-when-empty
```

## Opções importantes

```bash
php artisan queue:work \
  --sleep=3 \        # Espera 3s entre tentativas quando a fila está vazia
  --tries=3 \        # Número máximo de tentativas por job
  --timeout=120 \    # Timeout máximo por job em segundos
  --queue=default    # Fila específica (padrão: default)
```

## Supervisor (produção)

Em produção, use Supervisor para garantir que o worker rode continuamente.

```bash
# Copiar configuração de exemplo
sudo cp supervisor/laravel-worker.conf.example /etc/supervisor/conf.d/laravel-worker.conf

# Revisar e ajustar o arquivo conforme o ambiente
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

# Recarregar configurações do Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Verificar status
sudo supervisorctl status
```

## Logs do worker

Os logs do worker são gravados em:

```bash
storage/logs/worker.log
storage/logs/laravel.log
```

Para monitorar em tempo real:

```bash
tail -f storage/logs/worker.log
tail -f storage/logs/laravel.log
```

## Reiniciar workers após deploy

Após deploy, sempre reinicie os workers para carregar o código novo:

```bash
php artisan queue:restart
```

O Supervisor detectará o sinal e reiniciará os processos automaticamente.

## Retry de jobs com falha

Ver jobs com falha:

```bash
php artisan queue:failed
```

Reprocessar um job específico:

```bash
php artisan queue:retry {id}
```

Reprocessar todos os jobs com falha:

```bash
php artisan queue:retry all
```

Limpar jobs com falha antigos:

```bash
php artisan queue:flush
```

## Cuidados em produção

- **Nunca pare o worker abruptamente** durante processamento de um job crítico; use `queue:restart` que espera o job atual terminar.
- **Monitore a tabela `failed_jobs`** regularmente via painel admin.
- **Ajuste `--timeout`** para jobs longos de IA (geração de conteúdo pode demorar mais de 30s).
- **Use filas separadas** para prioridades diferentes: `--queue=high,default,low`.
- **Redis** é recomendado em produção para maior performance; configure `QUEUE_CONNECTION=redis` no `.env`.

## Testar a fila

```bash
# Disparar job de teste
php artisan queue:test

# Em outro terminal, processar a fila
php artisan queue:work --stop-when-empty
```
