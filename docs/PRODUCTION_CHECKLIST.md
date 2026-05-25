# Checklist de Produção — Lymity IA

Use esta lista antes de cada deploy em produção.

## Configuração de Ambiente

- [ ] `APP_ENV=production` no `.env`
- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_KEY` preenchida (não vazia, não padrão)
- [ ] `APP_URL` com domínio real e HTTPS
- [ ] `DB_*` configurado e conectado
- [ ] `QUEUE_CONNECTION` configurada (`database` ou `redis`)
- [ ] `CACHE_STORE` configurada
- [ ] `SESSION_DRIVER` configurada
- [ ] `AI_PROVIDER` configurada corretamente
- [ ] `MAIL_*` configurado se envio de e-mail for necessário

## Banco de Dados

- [ ] Banco criado e usuário com permissões corretas
- [ ] `php artisan migrate --force` executado sem erros
- [ ] Seed inicial rodado (`php artisan db:seed`) se necessário
- [ ] Backup configurado (cron de mysqldump ou ferramenta de backup)

## Permissões de Arquivo

- [ ] `storage/` gravável: `chmod -R 775 storage/`
- [ ] `bootstrap/cache/` gravável: `chmod -R 775 bootstrap/cache/`
- [ ] Proprietário correto: `chown -R www-data:www-data /var/www/lymity-ia`

## Cache

- [ ] `php artisan config:cache` executado
- [ ] `php artisan route:cache` executado
- [ ] `php artisan view:cache` executado
- [ ] `php artisan event:cache` executado

## Nginx

- [ ] Nginx aponta para `/var/www/lymity-ia/public` como `root`
- [ ] `index index.php index.html`
- [ ] `try_files $uri $uri/ /index.php?$query_string` configurado
- [ ] Bloco PHP-FPM configurado
- [ ] `.env` bloqueado (não acessível via HTTP)
- [ ] `client_max_body_size` ajustado para uploads
- [ ] Configuração testada: `nginx -t`

## HTTPS

- [ ] Certificado SSL instalado (Let's Encrypt via Certbot recomendado)
- [ ] Redirecionamento HTTP → HTTPS ativo
- [ ] `APP_URL=https://...` no `.env`

## Queue Workers

- [ ] Supervisor instalado: `apt install supervisor`
- [ ] Arquivo de configuração copiado: `supervisor/laravel-worker.conf.example`
- [ ] Configuração revisada (usuário, caminho, PHP path)
- [ ] `sudo supervisorctl reread && sudo supervisorctl update`
- [ ] Workers rodando: `sudo supervisorctl status`

## Scheduler

- [ ] Linha no crontab adicionada:
  ```
  * * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Crontab verificado: `crontab -l`

## Monitoramento

- [ ] `php artisan system:health-check` retorna OK
- [ ] `/admin/system-health` acessível e sem erros
- [ ] Logs gravando em `storage/logs/laravel.log`
- [ ] Rotação de logs configurada (logrotate)
- [ ] Worker log em `storage/logs/worker.log`

## Segurança Final

- [ ] `.env` não acessível via HTTP (testado com `curl https://seudominio.com.br/.env`)
- [ ] `APP_DEBUG=false` confirmado
- [ ] Sem senhas expostas em logs
- [ ] Firewall configurado (apenas portas 80, 443 e SSH expostas)
- [ ] SSH com autenticação por chave (não senha)

## Pós-Deploy

- [ ] Acessar `/admin/dashboard` e verificar funcionamento
- [ ] Acessar `/admin/system-health` e confirmar todos os checks OK
- [ ] Testar login com credenciais corretas
- [ ] Verificar filas processando: `php artisan queue:monitor`
- [ ] Monitorar logs por 10 minutos: `tail -f storage/logs/laravel.log`
