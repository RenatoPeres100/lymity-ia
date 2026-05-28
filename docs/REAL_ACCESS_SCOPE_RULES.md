# Real Access Scope Rules — Lymity IA

## Papéis e Escopos

| Role                | Painel  | Vê Companies | Vê Clients      | Vê Users              |
|---------------------|---------|--------------|-----------------|------------------------|
| admin_geral         | Admin   | Todas        | Todos           | Todos                  |
| agencia_admin       | Admin   | Própria      | company_id igual| company + clients     |
| agencia_operador    | Admin   | Própria      | company_id igual| company + clients     |
| social_media        | Admin   | Própria      | company_id igual| company + clients     |
| copywriter          | Admin   | Própria      | company_id igual| company + clients     |
| blog_writer         | Admin   | Própria      | company_id igual| company + clients     |
| seo                 | Admin   | Própria      | company_id igual| company + clients     |
| designer            | Admin   | Própria      | company_id igual| company + clients     |
| cliente_admin       | Client  | Nenhuma      | Próprio client  | Mesmo client_id        |
| cliente_colaborador | Client  | Nenhuma      | Próprio client  | Próprio apenas         |
| viewer (client)     | Client  | Nenhuma      | Próprio client  | Próprio apenas         |
| ai_employee         | Nenhum  | Nenhuma      | Conforme tarefa | Nenhum                 |

## Regra de Ouro

**NUNCA use `Model::query()`, `Model::count()` ou `Model::latest()` em painel/admin/API sem aplicar `visibleTo(auth()->user())`, exceto em contexto explicitamente validado como admin_geral.**

## AccessScopeService

Arquivo: `app/Services/Auth/AccessScopeService.php`

```php
$scope = app(AccessScopeService::class);

// Checar escopo
$scope->isGlobalAdmin($user);
$scope->isAgencyUser($user);
$scope->isClientUser($user);
$scope->getCompanyId($user);
$scope->getClientId($user);

// Validações
$scope->canSeeCompany($user, $companyId);
$scope->canSeeClient($user, $clientId);
$scope->canSeeUser($user, $targetUser);

// Queries seguras
$scope->scopeClients($user, $query);
$scope->scopeUsers($user, $query);
$scope->scopeBlogPosts($user, $query);
// ... etc
```

## Uso do visibleTo nos Models

```php
// CORRETO — sempre usar visibleTo em listagens
Client::visibleTo(auth()->user())->paginate();
BlogPost::visibleTo(auth()->user())->latest()->get();
ApprovalRequest::visibleTo(auth()->user())->where('status', 'pending')->count();
User::visibleTo(auth()->user())->where('status', 'active')->get();

// ERRADO — nunca usar query global em contexto de painel
Client::paginate();           // ← PROIBIDO
BlogPost::count();            // ← PROIBIDO
ApprovalRequest::latest()->get(); // ← PROIBIDO
```

## Queries Seguras em Controllers

```php
public function index(Request $request): View
{
    $user  = Auth::user();
    $query = Client::visibleTo($user)
        ->with(['company'])
        ->orderBy('name');

    $clients = $query->paginate(20);
    $stats   = ['total' => Client::visibleTo($user)->count()];

    return view('admin.clients.index', compact('clients', 'stats'));
}

public function show(Client $client): View
{
    $this->authorize('view', $client); // policy verifica escopo
    // ...
}

public function store(StoreClientRequest $request): RedirectResponse
{
    $data = app(AccessScopeService::class)
        ->applyOwnershipOnCreate(Auth::user(), $request->validated(), Client::class);

    $client = Client::create($data);
    // ...
}
```

## Middlewares

| Alias          | Classe                     | Uso                        |
|----------------|----------------------------|----------------------------|
| active         | EnsureUserIsActive         | Todas as rotas autenticadas|
| admin.panel    | EnsureAdminPanelAccess     | /admin/*                   |
| client.panel   | EnsureClientPanelAccess    | /client/* e /app/*         |
| company.scope  | EnsureCanAccessCompany     | Rotas com {company}        |
| client.scope   | EnsureCanAccessClient      | Rotas com {client}         |

## Policies Disponíveis

- `UserPolicy` — CRUD de usuários
- `ClientPolicy` — CRUD de clientes
- `BlogPostPolicy` — posts de blog
- `ContentBriefPolicy` — briefings de conteúdo
- `SocialPostPolicy` — posts sociais
- `ApprovalRequestPolicy` — aprovações
- `ActivityLogPolicy` — logs de atividade
- `AiTaskPolicy` — tarefas IA
- `AgentRoutinePolicy` — rotinas de agente
- `ExternalFilePolicy` — arquivos externos
- `BrandContextPolicy` — contexto de marca

## Como Criar Controllers Seguros

```php
// 1. Listagem — sempre visibleTo
$items = Model::visibleTo($user)->paginate();

// 2. Show — sempre policy
$this->authorize('view', $model);

// 3. Create — aplicar ownership
$data = $scope->applyOwnershipOnCreate($user, $request->validated());

// 4. Update/Delete — policy garante escopo
$this->authorize('update', $model);
$this->authorize('delete', $model);
```

## Como Criar Dashboards Seguros

```php
// ERRADO
$stats = ['total' => Client::count()];

// CORRETO
$user  = Auth::user();
$stats = [
    'total'    => Client::visibleTo($user)->count(),
    'active'   => Client::visibleTo($user)->active()->count(),
];
```

## Logs de Acesso Negado

Eventos registrados automaticamente:
- `access.denied.admin_panel` — client tentou acessar /admin
- `access.denied.client_panel` — não autorizado a /client
- `access.denied.cross_company` — tentativa de acesso a outra company
- `access.denied.cross_client` — tentativa de acesso a outro client
- `access.denied.inactive_user` — usuário inativo tentou acessar
- `access.denied.ai_employee_panel` — ai_employee tentou acessar painel humano

## Usuários de Teste

Execute antes de testar: `php artisan db:seed --class=AccessScopeTestSeeder`

| Email                          | Role          | Escopo          | Senha       |
|-------------------------------|---------------|-----------------|-------------|
| admin_global_test@lymity.local | admin_geral  | Tudo            | password123 |
| agencia_admin_a@lymity.local   | agencia_admin| Company A       | password123 |
| operador_a@lymity.local        | social_media | Company A       | password123 |
| agencia_admin_b@lymity.local   | agencia_admin| Company B       | password123 |
| cliente_admin_a1@lymity.local  | cliente_admin| Cliente A1      | password123 |
| cliente_admin_b1@lymity.local  | cliente_admin| Cliente B1      | password123 |

## Comando de Diagnóstico

```bash
php artisan access:diagnose-scope
```

Saída esperada:
```
[OK] AccessScopeService exists and resolves
[OK] Client::scopeVisibleTo exists
[OK] agencia_admin_a NÃO vê Cliente B1 (correto — cross-company bloqueado)
[OK] cliente_admin_a1 NÃO vê aprovação B1 (correto — cross-client bloqueado)
ACCESS_SCOPE_VALIDATION=OK
```

## Exemplos de Teste via Tinker

```bash
# Agency A vê apenas seus clientes
php artisan tinker --execute="
\$u = App\Models\User::where('email','agencia_admin_a@lymity.local')->first();
echo App\Models\Client::visibleTo(\$u)->pluck('name')->implode(', ');
"

# Admin global vê tudo
php artisan tinker --execute="
\$u = App\Models\User::where('email','admin_global_test@lymity.local')->first();
echo 'Clients: '.App\Models\Client::visibleTo(\$u)->count();
"
```
