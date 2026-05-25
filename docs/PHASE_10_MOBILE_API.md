# Phase 10 — Mobile Approval API e PWA

## Objetivo

Preparar o sistema para futuro app nativo iPhone/Android e fornecer uma interface web mobile-first focada em aprovações, acompanhamento e comunicação do cliente.

---

## Autenticação da API

A API usa **sessão web (cookie-based)**, compartilhada com a aplicação Laravel existente.

### Como testar via navegador logado

1. Acesse `http://187.124.133.195:8000/login` e faça login.
2. Acesse diretamente: `http://187.124.133.195:8000/api/me`

### Como testar via curl com sessão

```bash
# 1. Obter CSRF token
CSRF=$(curl -s -c cookies.txt http://187.124.133.195:8000/api/csrf-cookie | grep -oP '"token":"\K[^"]+' || \
       curl -s -c cookies.txt -I http://187.124.133.195:8000/ 2>&1 | grep -i xsrf | head -1)

# 2. Login
curl -c cookies.txt -b cookies.txt \
  -X POST http://187.124.133.195:8000/login \
  -H "X-XSRF-TOKEN: $(cat cookies.txt | grep XSRF | awk '{print $7}')" \
  -d "email=admin@lymity.local&password=password"

# 3. Fazer chamadas à API
curl -b cookies.txt http://187.124.133.195:8000/api/me
curl -b cookies.txt http://187.124.133.195:8000/api/client/approvals
```

### Próximos passos para Sanctum (app nativo)

Se/quando instalar Laravel Sanctum (`composer require laravel/sanctum`):
1. Adicionar `HasApiTokens` ao modelo `User`
2. Trocar middleware `api_auth` por `auth:sanctum` nas rotas
3. Endpoint `POST /api/login` retorna token para uso em `Authorization: Bearer <token>`

---

## Rotas API (`/api`)

Todas protegidas por middleware `api_auth` (sessão web autenticada, não-ai, ativo).

| Método | Rota | Nome |
|--------|------|------|
| GET | `/api/me` | `api.me` |
| GET | `/api/client/dashboard` | `api.client.dashboard` |
| GET | `/api/client/notifications` | `api.client.notifications` |
| GET | `/api/client/approvals` | `api.client.approvals.index` |
| GET | `/api/client/approvals/{id}` | `api.client.approvals.show` |
| POST | `/api/client/approvals/{id}/approve` | `api.client.approvals.approve` |
| POST | `/api/client/approvals/{id}/reject` | `api.client.approvals.reject` |
| POST | `/api/client/approvals/{id}/request-changes` | `api.client.approvals.request-changes` |
| GET | `/api/client/social/posts` | `api.client.social.posts` |
| GET | `/api/client/ads/campaigns` | `api.client.ads.campaigns` |
| GET | `/api/client/blog/posts` | `api.client.blog.posts` |
| GET | `/api/client/budgets` | `api.client.budgets` |
| GET | `/api/client/proposals` | `api.client.proposals` |

**Segurança:** cliente só acessa dados do próprio `client_id`. `admin_geral` acessa tudo. `ai_employee` retorna 403.

---

## Rotas PWA Web (`/app`)

Middleware: `auth` + `active` + `client_access`

| Método | Rota | Nome |
|--------|------|------|
| GET | `/app` | `app.dashboard` |
| GET | `/app/approvals` | `app.approvals.index` |
| GET | `/app/approvals/{id}` | `app.approvals.show` |
| POST | `/app/approvals/{id}/approve` | `app.approvals.approve` |
| POST | `/app/approvals/{id}/reject` | `app.approvals.reject` |
| POST | `/app/approvals/{id}/request-changes` | `app.approvals.request-changes` |
| POST | `/app/approvals/{id}/comments` | `app.approvals.comment` |
| GET | `/app/calendar` | `app.calendar` |
| GET | `/app/reports` | `app.reports` |
| GET | `/app/profile` | `app.profile` |

---

## Controllers

### API
- `App\Http\Controllers\Api\Auth\MeController` — GET /api/me
- `App\Http\Controllers\Api\Client\DashboardController` — dashboard summary
- `App\Http\Controllers\Api\Client\ApprovalController` — CRUD + approve/reject/request-changes
- `App\Http\Controllers\Api\Client\SocialPostController`
- `App\Http\Controllers\Api\Client\CampaignController`
- `App\Http\Controllers\Api\Client\BlogPostController`
- `App\Http\Controllers\Api\Client\BudgetController`
- `App\Http\Controllers\Api\Client\ProposalController`
- `App\Http\Controllers\Api\Client\NotificationController`

### Web App
- `App\Http\Controllers\App\AppDashboardController`
- `App\Http\Controllers\App\AppApprovalController`
- `App\Http\Controllers\App\AppCalendarController`
- `App\Http\Controllers\App\AppReportController`
- `App\Http\Controllers\App\AppProfileController`

---

## Resources (JSON)

Localizados em `app/Http/Resources/`:

- `UserResource` — id, name, email, role, user_type, client_id, company_id
- `ApprovalResource` — id, title, description, approval_type, status, sensitive_level, due_at, client, requested_by, created_at, actions_count, comments_count, payload
- `SocialPostResource` — id, title, objective, content_type, main_caption, status, scheduled_at, published_at
- `CampaignResource` — id, name, platform, objective, status, daily_budget, total_budget, start_date, end_date
- `BlogPostResource` — id, title, slug, excerpt, type, status, seo_title, focus_keyword, published_at
- `BudgetResource` — id, title, description, month, year, status, total_amount
- `ProposalResource` — id, title, description, status, total_amount, valid_until
- `NotificationResource` — id, title, message, type, status, action_url, created_at

---

## Services

### MobileDashboardService (`app/Services/Mobile/`)
- `getClientSummary(User $user): array` — contadores para dashboard
- `getPendingApprovals(User $user): Collection` — aprovações pendentes
- `getCalendarItems(User $user): array` — posts, campanhas e aprovações por data
- `getReportsSummary(User $user): array` — resumo de todas as métricas

---

## Layout Mobile

`resources/views/layouts/mobile-app.blade.php`

Características:
- Mobile-first, sem dependência de Tailwind pesado
- CSS puro inline — sem impacto em outros layouts
- Bottom navigation com 5 itens
- Header compacto com logo, título e avatar
- Cards grandes com boa legibilidade
- Badges coloridos por status
- Botões Aprovar (verde), Reprovar (vermelho), Pedir Ajuste (azul)
- Formulários em-página para ações rápidas
- Suporte a `safe-area-inset` para iPhones com notch

---

## PWA

### manifest.json (`public/manifest.json`)
- `name`: Lymity IA
- `start_url`: /app
- `display`: standalone
- `theme_color`: #0f172a
- Ícones 192x192 e 512x512 incluídos
- Shortcuts para /app/approvals e /app/calendar

### Service Worker (`public/sw.js`)
- Cache de shell estático (/app, /app/approvals)
- Network-first para páginas /app
- Cache-first para assets estáticos (.css, .js, imagens)
- **Nunca cacheia /api/, /login, /logout ou POST**
- Remoção automática de caches antigos no activate
- Falha silenciosa se SW não for suportado

### Ícones (`public/icons/`)
- `icon-192.png` — 192×192px (gerado com GD)
- `icon-512.png` — 512×512px (gerado com GD)

---

## Regras de Segurança

- `client_id` verificado em todos os controllers (API e Web)
- `admin_geral` tem acesso total para suporte/teste
- `ai_employee` bloqueado com 403 na API
- Usuário inativo bloqueado com 403 na API
- Sem publicação real externa pelo app
- Todas as ações de aprovação usam `ApprovalService` e geram `ApprovalAction` + `ActivityLog`
- API não expõe dados sem autenticação (retorna 401)

---

## Como testar no celular

1. Conectar celular na mesma rede Wi-Fi que o servidor (ou usar IP público)
2. Abrir: `http://187.124.133.195:8000/login`
3. Login com `cliente@lymity.local` / `password`
4. Acessar: `http://187.124.133.195:8000/app`
5. Para instalar como PWA: no Chrome/Safari, usar "Adicionar à tela inicial"

---

## Logs

Aprovações via app geram:
- `ApprovalAction` — registro da ação com usuário, notas e timestamp
- `ActivityLog` — log geral do módulo (via `ApprovalService`)
- Comentários salvos em `ApprovalComment`

---

## Regressão

Testado: todas as rotas das fases 1–9 retornam 200 ou 302 (zero 500s).
Verificado: contagens de banco sem perda de dados.

---

## Próximos passos para app nativo

1. Instalar Laravel Sanctum: `composer require laravel/sanctum`
2. Publicar config: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
3. Adicionar `HasApiTokens` em `User`
4. Criar `POST /api/login` retornando token Bearer
5. Trocar middleware `api_auth` por `auth:sanctum` em `routes/api.php`
6. Criar `POST /api/logout` para revogar token
7. Desenvolver app React Native ou Flutter consumindo a API
