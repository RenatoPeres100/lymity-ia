# Variáveis de Ambiente — Lymity IA

## Arquivo de Referência

Use `.env.production.example` como base para criar seu `.env` em produção.

```bash
cp .env.production.example .env
php artisan key:generate
```

## Variáveis Principais

### Aplicação

| Variável | Valor Prod | Descrição |
|----------|-----------|-----------|
| `APP_NAME` | `Lymity IA` | Nome exibido na interface |
| `APP_ENV` | `production` | Ambiente (local, staging, production) |
| `APP_KEY` | `base64:...` | Chave de criptografia — gerada com `key:generate` |
| `APP_DEBUG` | `false` | **NUNCA `true` em produção** — expõe erros e variáveis |
| `APP_URL` | `https://seudominio.com.br` | URL base da aplicação |
| `APP_TIMEZONE` | `America/Sao_Paulo` | Fuso horário |
| `APP_LOCALE` | `pt_BR` | Idioma padrão |

### Banco de Dados

| Variável | Descrição |
|----------|-----------|
| `DB_CONNECTION` | Driver: `mysql` (recomendado) |
| `DB_HOST` | Host do banco (geralmente `127.0.0.1`) |
| `DB_PORT` | Porta (MySQL: `3306`) |
| `DB_DATABASE` | Nome do banco |
| `DB_USERNAME` | Usuário do banco |
| `DB_PASSWORD` | Senha do banco — **nunca vazia em produção** |

### Filas

| Variável | Valor Prod | Descrição |
|----------|-----------|-----------|
| `QUEUE_CONNECTION` | `database` ou `redis` | `database` simples, `redis` para maior volume |

### Cache e Sessão

| Variável | Valor Prod | Descrição |
|----------|-----------|-----------|
| `CACHE_STORE` | `file` ou `redis` | Driver de cache |
| `SESSION_DRIVER` | `file` ou `redis` | Driver de sessão |
| `SESSION_LIFETIME` | `120` | Minutos de sessão ativa |

### IA

| Variável | Valores | Descrição |
|----------|---------|-----------|
| `AI_PROVIDER` | `mock`, `openai`, `claude` | Provider de IA |
| `AI_API_KEY` | sua-chave | Chave da API de IA — **nunca exposta** |
| `AI_MODEL` | ex: `gpt-4o`, `claude-opus-4-7` | Modelo padrão |

> **Em desenvolvimento:** Use `AI_PROVIDER=mock` para não consumir créditos.

### E-mail

| Variável | Descrição |
|----------|-----------|
| `MAIL_MAILER` | `smtp`, `sendmail`, `log` (dev) |
| `MAIL_HOST` | Servidor SMTP |
| `MAIL_PORT` | Porta SMTP (587 TLS, 465 SSL) |
| `MAIL_USERNAME` | Usuário SMTP |
| `MAIL_PASSWORD` | Senha SMTP — **protegida** |
| `MAIL_ENCRYPTION` | `tls` ou `ssl` |
| `MAIL_FROM_ADDRESS` | E-mail remetente |
| `MAIL_FROM_NAME` | Nome remetente |

### Redis (opcional, mas recomendado em produção)

| Variável | Padrão |
|----------|--------|
| `REDIS_HOST` | `127.0.0.1` |
| `REDIS_PASSWORD` | `null` |
| `REDIS_PORT` | `6379` |

## Segurança

- **Nunca versione o arquivo `.env`** — ele está no `.gitignore`
- **Nunca exponha `APP_KEY`** — use para criptografia de sessões e cookies
- **`APP_DEBUG=false`** em produção — evita vazamento de stack traces
- **Senhas de banco e APIs** devem ser únicas por ambiente
- **Rotacione `APP_KEY`** com cautela — invalida sessões existentes

## Verificar configuração

```bash
php artisan about
php artisan config:show app
php artisan system:health-check
```
