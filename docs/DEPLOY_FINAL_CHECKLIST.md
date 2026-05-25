# Checklist de Deploy Final — Lymity IA

## Pré-Deploy

### Ambiente
- [ ] PHP 8.3+ instalado
- [ ] MySQL 8.0+ configurado
- [ ] Redis instalado e rodando
- [ ] Nginx instalado e configurado
- [ ] Supervisor instalado (para queue workers)
- [ ] Composer 2.x disponível
- [ ] Node.js + npm disponíveis (para assets, se necessário)

### Código
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Banco de Dados
```bash
php artisan migrate --force
php artisan db:seed --force   # opcional — apenas para ambientes de demo
```

### Storage
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Configuração .env (Produção)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

DB_HOST=127.0.0.1
DB_DATABASE=lymity_ia
DB_USERNAME=lymity_user
DB_PASSWORD=senha-forte-aqui

REDIS_HOST=127.0.0.1
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

AI_PROVIDER=mock  # ou openai, claude
# OPENAI_API_KEY=
# ANTHROPIC_API_KEY=

GOOGLE_DRIVE_ENABLED=false
# GOOGLE_DRIVE_CLIENT_ID=
# GOOGLE_DRIVE_CLIENT_SECRET=
# GOOGLE_DRIVE_REDIRECT_URI=
```

**NUNCA** deixar `APP_DEBUG=true` em produção.

## Supervisor (Queue Workers)

Arquivo: `/etc/supervisor/conf.d/lymity-queue.conf`

```ini
[program:lymity-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lymity-ia/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lymity-ia/storage/logs/queue.log
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start lymity-queue:*
```

## Cron (Laravel Scheduler)

```bash
crontab -e
# Adicionar:
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

## Nginx

```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /var/www/lymity-ia/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    # Bloquear acesso direto ao storage
    location ~* /storage/app/ {
        deny all;
    }
}
```

## Verificações Pós-Deploy

```bash
# Health check
php artisan system:health-check

# Verificar queue
php artisan queue:test

# Verificar rotas críticas
curl -I http://localhost/
curl -I http://localhost/login
curl -I http://localhost/admin/dashboard  # deve retornar 302

# Verificar logs
tail -f storage/logs/laravel.log
```

## Segurança

- [ ] `APP_DEBUG=false`
- [ ] `.env` não versionado (`/.env` no `.gitignore`)
- [ ] `vendor/` não versionado
- [ ] Permissões corretas em `storage/` e `bootstrap/cache/`
- [ ] Nginx bloqueando acesso a `storage/app/`
- [ ] HTTPS configurado (Let's Encrypt / Certbot)
- [ ] Firewall: apenas portas 80, 443 e SSH abertas

## Rollback

```bash
git checkout <commit-anterior>
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback  # se necessário
php artisan optimize:clear
```

## Monitoramento Recomendado

- Laravel Telescope (desenvolvimento)
- Sentry / Flare (produção)
- Uptime robot para `/` e `/login`
- Alertas de uso de CPU/RAM via Hostinger painel
