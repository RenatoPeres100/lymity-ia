# Real Performance Optimization

## O que foi otimizado

### 1. `User::hasPermission()` — in-memory cache

**Antes:** cada chamada a `hasPermission()` executava uma query separada no banco.  
A sidebar do cliente chama `hasPermission` ~11 vezes por render → **11 queries por página**.

**Depois:** as permission keys são carregadas uma única vez por request e armazenadas em `$permissionKeyCache` na instância do Model.

```php
// Uma query, independente de quantas vezes hasPermission() for chamado
$user->getPermissionKeys(); // ['client.dashboard.view', 'client.blog.view', ...]
$user->hasPermission('client.blog.view'); // in_array() — zero queries
```

**Resultado:** 34ms → 4ms (33 chamadas), 1 query em vez de 33.

**Invalidação:** `$user->clearPermissionCache()` é chamado automaticamente após `syncPermissions()` e `applyRolePreset()`.

### 2. Índices de banco de dados

Migration: `2026_05_28_200000_add_performance_indexes.php`

| Tabela | Índices adicionados |
|--------|---------------------|
| `users` | `role`, `status`, `user_type` |
| `clients` | `status`, `name` |
| `permissions` | `module` |
| `blog_posts` | `status`, `scheduled_at`, `published_at`, `type` |
| `social_posts` | `status`, `scheduled_at`, `published_at` |
| `approval_requests` | `status`, `approval_type`, `created_at` |
| `activity_logs` | `action`, `level`, `created_at` |
| `ai_task_logs` | `status`, `created_at` |

Todos criados com verificação prévia (`hasIndex()`) — sem erro se já existir.

### 3. DashboardStatsService — queries agregadas

**Antes:** Client Dashboard fazia 3 queries separadas de `COUNT` em `approval_requests`.  
**Depois:** `DashboardStatsService::getClientStats()` faz 1 query com `SUM()` condicional:

```sql
SELECT SUM(status = 'pending'), SUM(status = 'changes_requested'), SUM(status = 'approved')
FROM approval_requests WHERE client_id = ?
```

**User index:** substituídas 5 queries separadas por 1 query com `SUM()` condicional no `UserController::index()`.

### 4. Eager loading padronizado

| Controller | Relações carregadas |
|-----------|---------------------|
| `AdminApprovalController` | `client`, `requester`, `aiEmployee` |
| `AdminDashboardController` | `client`, `requester` / `aiEmployee` |
| `UserController` | `company`, `client` |
| `ClientDashboardController` | `requester` nos pending approvals |
| `ClientAiLogController` | query direta com `client_id` scoped |

### 5. Paginação garantida

| Controller | Paginação |
|-----------|-----------|
| `ActivityLogController` | `paginate(50)` ✓ |
| `ClientApprovalController` | `paginate(20)` ✓ |
| `ClientAiLogController` | `paginate(30)` ✓ |

### 6. Cache de produção (route, config, view)

`system:optimize-safe` aplica:
- `optimize:clear` (limpa tudo primeiro)
- `config:cache`
- `view:cache`
- `route:cache` (tentativa, com fallback se houver closures)

## Comandos criados

```bash
# Diagnóstico completo de performance
php artisan system:performance-audit

# Otimização segura (sem migrate)
php artisan system:optimize-safe

# Limpar permissões duplicadas
php artisan permissions:cleanup-duplicates
```

## O que NÃO foi cacheado (por segurança)

| O que | Motivo |
|-------|--------|
| Dashboard stats por cliente | Cache global misturaria dados de clientes diferentes |
| Menu sidebar | Depende de `client_id` e permissões individuais |
| visibleTo() queries | Scope é por user/client — cache global é inseguro |
| Dados de approval/blog | São sensíveis e devem refletir estado real |

## Como diagnosticar lentidão

```bash
# 1. Rodar o audit
php artisan system:performance-audit

# 2. Diagnosticar permissões de um cliente
php artisan permissions:diagnose-client email@exemplo.com

# 3. Verificar duplicatas
php artisan permissions:cleanup-duplicates

# 4. Ver logs de erro
tail -100 storage/logs/laravel.log | grep ERROR
```

## Como limpar permissões duplicadas

```bash
php artisan permissions:cleanup-duplicates
```

O comando encontra entradas duplicadas (`user_id + permission_id`) na tabela `user_permissions`, mantém o registro com menor `id` e remove os demais.

## Como rodar otimização segura

```bash
# Em produção, após deploy
php artisan system:optimize-safe

# Também reiniciar PHP-FPM para limpar OPcache
systemctl restart php8.3-fpm
```

## Cuidados em produção

1. **Não fazer `route:cache` com closures** — o comando `system:optimize-safe` detecta e avisa.
2. **Reiniciar PHP-FPM** após atualizar código — o OPcache do CLI e do FPM são separados.
3. **Redis já está configurado** para cache, queue e session — não mudar para `array` ou `file` em produção.
4. **`$permissionKeyCache`** é por instância de `User` — ao recarregar o usuário via `->fresh()` o cache é reiniciado automaticamente.
5. **Limpar cache** ao atualizar permissões — `syncPermissions()` e `applyRolePreset()` chamam `clearPermissionCache()` automaticamente.

## Resultado dos benchmarks

| Métrica | Antes | Depois |
|---------|-------|--------|
| `hasPermission` ×33 | ~34ms (33 queries) | ~5ms (1 query) |
| User index stats | 5 queries | 1 query |
| Client dashboard stats | 3 queries | 1 query |
| Performance audit checks | — | 35/35 OK |
