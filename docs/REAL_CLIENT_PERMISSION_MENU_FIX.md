# Real Client Permission Menu Fix

## Problema

O menu do painel cliente exibia sempre os mesmos itens básicos (Meu Painel, Aprovações, Blog, Arquivos, App Mobile) independente das permissões salvas no banco de dados para o usuário.

## Causa Raiz

1. **Permissões `client.*` inexistentes**: O sistema só tinha permissões admin (`users.*`, `blog.*`, `approvals.*`), que são do painel administrativo. Não existiam permissões com prefixo `client.*`.
2. **Sidebar estática**: O bloco de menu do cliente em `components/layouts/app.blade.php` exibia todos os itens sem verificar `hasPermission()`.
3. **Rotas inexistentes**: Módulos como Brand Context, Rotinas, Funcionários IA, Tarefas IA, Logs IA e Usuários não tinham rotas no painel cliente.

## Solução Implementada

### 1. PermissionSeeder — 27 novas permissões `client.*`

Arquivo: `database/seeders/PermissionSeeder.php`

| Módulo | Permissões |
|--------|-----------|
| `client_dashboard` | `client.dashboard.view` |
| `client_approvals` | `client.approvals.view/approve/comment` |
| `client_blog` | `client.blog.view/create/update/approve/schedule/publish` |
| `client_brand_context` | `client.brand_context.view/update` |
| `client_routines` | `client.routines.view/manage` |
| `client_ai` | `client.ai_employees.view/use`, `client.ai_tasks.view/create`, `client.ai_logs.view` |
| `client_files` | `client.files.view/upload` |
| `client_users` | `client.users.view/create/update/disable/reset_password` |
| `client_app` | `client.app.view` |

### 2. User Model — `hasPermission()` com isActive check

Arquivo: `app/Models/User.php`

```php
public function hasPermission(string $key): bool
{
    if (!$this->isActive())     return false;
    if ($this->isAdminGeral())  return true;
    return $this->permissions()->where('key', $key)->exists();
}
public function hasAnyPermission(array $keys): bool { ... }
public function hasAllPermissions(array $keys): bool { ... }
```

### 3. UserPermissionPresetService

Arquivo: `app/Services/Users/UserPermissionPresetService.php`

- `getDefaultClientPermissions()` — 25 permissões para role `cliente`
- `getDefaultCollaboratorPermissions($function)` — 5+ permissões para role `colaborador`
- `syncDefaultClientPermissions(User $user)` — aplica preset ao usuário
- `syncDefaultCollaboratorPermissions(User $user, array $keys)` — idem para colaborador

### 4. Novos Controllers no painel cliente

| Controller | Rota | Permissão |
|-----------|------|-----------|
| `ClientBrandContextController` | `GET /client/brand-context` | `client.brand_context.view` |
| `ClientRoutineController` | `GET /client/routines` | `client.routines.view` |
| `ClientAiEmployeeController` | `GET /client/ai-employees` | `client.ai_employees.view` |
| `ClientAiTaskController` | `GET /client/ai-tasks` | `client.ai_tasks.view` |
| `ClientAiLogController` | `GET /client/ai-logs` | `client.ai_logs.view` |
| `TeamController` (alias) | `GET /client/users` | `client.users.view` |

### 5. Sidebar reconstruída com gates

Arquivo: `resources/views/components/layouts/app.blade.php`

```blade
@if($cu->hasPermission('client.brand_context.view') && Route::has('client.brand-context.index'))
    <a href="{{ route('client.brand-context.index') }}">Brand Context</a>
@endif
```

Todos os itens do menu cliente agora verificam:
1. `hasPermission('client.X.view')` — usuário tem a permissão?
2. `Route::has('client.X.index')` — a rota existe?

## Presets por Perfil

### Cliente Principal
Recebe automaticamente 25 permissões `client.*` ao ser criado.

### Colaborador
Recebe por padrão:
- `client.dashboard.view`
- `client.approvals.view`
- `client.blog.view`
- `client.files.view`
- `client.app.view`

O Cliente pode adicionar mais permissões ao Colaborador via `/admin/users/{id}/permissions`.

## Comandos de Diagnóstico

```bash
# Diagnóstico completo de um usuário
php artisan permissions:diagnose-client email@exemplo.com

# Listar todos os usuários clientes e suas permissões
php artisan permissions:refresh-client

# Ver permissões de um usuário específico
php artisan permissions:refresh-client email@exemplo.com

# Reaplicar preset padrão
php artisan permissions:refresh-client email@exemplo.com --defaults
```

## Exemplo de Saída do Diagnóstico

```
DIAGNÓSTICO: cliente.permissoes@lymity.local
USER      = cliente.permissoes@lymity.local
ROLE      = cliente
CAN_PANEL = yes
PERMISSIONS = 25

MENU:
[OK] Meu Painel - client.dashboard
[OK] Aprovações - client.approvals.index
[OK] Blog - client.blog.index
[OK] Brand Context - client.brand-context.index
[OK] Rotinas - client.routines.index
[OK] Funcionários IA - client.ai-employees.index
[OK] Tarefas IA - client.ai-tasks.index
[OK] Logs IA - client.ai-logs.index
[OK] Arquivos - client.files.index
[OK] Usuários - client.users.index
[OK] App Mobile - app.dashboard
```

## Usuário de Teste

```
Email: cliente.permissoes@lymity.local
Senha: password123
```

Acesse `https://ia.lymity.com.br/login` e veja todos os 11 itens do menu.

## Próximos Passos

- Adicionar permissões `client.social.*` quando módulo social for exposto ao cliente
- Criar UI no admin para visualizar quais permissões `client.*` cada cliente tem
- Adicionar suporte a "perfis de colaborador" (Aprovador, Editor, etc.) via preset selecionável no formulário de criação
