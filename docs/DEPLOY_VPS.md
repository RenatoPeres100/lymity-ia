# Deploy na VPS — Lymity IA

## Estrutura do Projeto

```
/var/www/lymity-ia/
├── app/              # Código PHP (Controllers, Models, Services, Jobs, Commands)
├── bootstrap/cache/  # Cache de bootstrap (deve ter permissão de escrita)
├── config/           # Configurações Laravel
├── database/         # Migrations, Seeders, Factories
├── docs/             # Documentação do projeto
├── nginx/            # Exemplo de configuração Nginx
├── public/           # Document root (único diretório exposto pelo Nginx)
├── resources/        # Views, CSS, JS
├── routes/           # Rotas (web.php, api.php, console.php)
├── scripts/          # Scripts auxiliares de deploy e health check
├── storage/          # Logs, cache, sessões (deve ter permissão de escrita)
└── supervisor/       # Exemplo de configuração Supervisor
```

## Pré-requisitos na VPS

- PHP 8.2+ com extensões: pdo, pdo_mysql, mbstring, xml, curl, zip, bcmath, tokenizer
- Composer 2.x
- MySQL 8.0+ ou MariaDB 10.6+
- Redis (opcional, para cache e filas avançadas)
- Nginx ou Apache
- Supervisor (para queue workers persistentes)
- Node.js + npm (se houver assets front-end)

## Comandos de Deploy

### 1. Clonar ou atualizar o repositório

```bash
git clone https://github.com/seu-usuario/lymity-ia.git /var/www/lymity-ia
# ou
cd /var/www/lymity-ia && git pull origin main
```

### 2. Instalar dependências PHP

```bash
cd /var/www/lymity-ia
composer install --no-dev --optimize-autoloader
```

### 3. Instalar e compilar assets front-end (se aplicável)

```bash
npm install
npm run build
```

### 4. Configurar .env

```bash
cp .env.production.example .env
# Edite com as credenciais reais
nano .env
```

### 5. Gerar APP_KEY

```bash
php artisan key:generate
```

### 6. Executar migrations

```bash
php artisan migrate --force
```

### 7. Popular dados iniciais (apenas primeiro deploy)

```bash
php artisan db:seed --force
```

### 8. Configurar cache de configuração

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 9. Definir permissões

```bash
chown -R www-data:www-data /var/www/lymity-ia
chmod -R 755 /var/www/lymity-ia
chmod -R 775 /var/www/lymity-ia/storage
chmod -R 775 /var/www/lymity-ia/bootstrap/cache
```

### 10. Reiniciar queue workers

```bash
php artisan queue:restart
# O Supervisor reiniciará os workers automaticamente após queue:restart
```

### 11. Verificar saúde do sistema

```bash
php artisan system:health-check
bash scripts/deploy-check.sh
```

## Atualização Contínua (zero-downtime simplificado)

```bash
cd /var/www/lymity-ia
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan system:health-check
```

## Links Úteis

- Painel Admin: https://seudominio.com.br/admin/dashboard
- Health Check: https://seudominio.com.br/admin/system-health
- Documentação de filas: docs/QUEUE_WORKERS.md
- Documentação do scheduler: docs/SCHEDULER.md
- Checklist de produção: docs/PRODUCTION_CHECKLIST.md
