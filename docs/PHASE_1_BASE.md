# Fase 1 — Base SaaS Funcional

## Objetivo

Criar a base funcional da plataforma SaaS com autenticação, estrutura multiempresa/multicliente, permissões, dashboards e layout premium.

---

## Models criados

| Model | Tabela | Descrição |
|---|---|---|
| `User` | `users` | Atualizado com company_id, client_id, role, user_type, status, last_login_at |
| `Company` | `companies` | Empresa/agência principal |
| `Client` | `clients` | Clientes atendidos pela agência |
| `Role` | `roles` | Definição dos roles do sistema |
| `Permission` | `permissions` | Permissões modulares |
| `UserPermission` | `user_permissions` | Pivot usuário ↔ permissão |
| `AgencySetting` | `agency_settings` | Configurações globais key/value |

---

## Migrations criadas

| Arquivo | Descrição |
|---|---|
| `0001_01_01_000000_create_users_table` | Tabela base de usuários (Laravel padrão) |
| `2025_05_22_000001_create_companies_table` | Tabela de empresas/agências |
| `2025_05_22_000002_create_clients_table` | Tabela de clientes |
| `2025_05_22_000003_add_extra_columns_to_users_table` | Colunas extras na tabela users |
| `2025_05_22_000004_create_roles_table` | Tabela de roles |
| `2025_05_22_000005_create_permissions_table` | Tabela de permissões |
| `2025_05_22_000006_create_user_permissions_table` | Pivot user ↔ permission |
| `2025_05_22_000007_create_agency_settings_table` | Configurações da agência |

---

## Seeders criados

| Seeder | Descrição |
|---|---|
| `RoleSeeder` | 12 roles do sistema |
| `PermissionSeeder` | 26 permissões em 9 módulos |
| `AdminUserSeeder` | Empresa "Lymity AI Agency" + usuário admin |
| `DemoClientSeeder` | Cliente "Cliente Demonstração" + usuário cliente demo |

---

## Rotas disponíveis

| Método | Rota | Controller | Middleware |
|---|---|---|---|
| GET | `/` | HomeController@index | — |
| GET | `/login` | LoginController@showLoginForm | guest |
| POST | `/login` | LoginController@login | guest |
| POST | `/logout` | LoginController@logout | auth |
| GET | `/dashboard` | DashboardController@index | auth, active |
| GET | `/admin/users` | Admin\UserController@index | auth, active, agency |
| GET | `/admin/clients` | Admin\ClientController@index | auth, active, agency |
| GET | `/admin/settings` | Admin\SettingController@index | auth, active, agency, admin_geral |
| GET | `/client/dashboard` | Client\DashboardController@index | auth, active, client_access |

---

## Middlewares criados

| Middleware | Alias | Comportamento |
|---|---|---|
| `EnsureUserIsActive` | `active` | Bloqueia usuários inativos e ai_employee no login |
| `EnsureAdminGeral` | `admin_geral` | Permite somente role admin_geral |
| `EnsureAgencyAccess` | `agency` | Permite roles internas da agência |
| `EnsureClientAccess` | `client_access` | Permite cliente_admin, cliente_colaborador com client_id |

---

## Credenciais de teste

| Tipo | E-mail | Senha | Role |
|---|---|---|---|
| Admin Geral | admin@lymity.local | password | admin_geral |
| Cliente Demo | cliente@demo.local | password | cliente_admin |

---

## Como testar visualmente

1. Acesse `http://187.124.133.195:8000/` — Landing page premium
2. Clique em "Acessar plataforma" ou acesse `/login`
3. Faça login com `admin@lymity.local` / `password`
4. Explore o dashboard em `/dashboard`
5. Acesse `/admin/users` — tabela de usuários
6. Acesse `/admin/clients` — tabela de clientes
7. Acesse `/admin/settings` — configurações da agência
8. Acesse `/client/dashboard` — painel do cliente
9. Saia e faça login com `cliente@demo.local` / `password`
10. O cliente tem acesso somente ao `/client/dashboard`

---

## Decisões técnicas

- **Autenticação manual** em vez de Laravel Breeze para controle total sobre validações de role e ai_employee.
- **user_type** separa agência (`agency`), cliente (`client`) e IA (`ai`) — evita colisão com o campo `role`.
- **Layouts Blade** em `resources/views/components/layouts/` para compatibilidade com a sintaxe `<x-layouts.app>` do Laravel 13.
- **Tailwind CSS v4** compilado via Vite — sem CDN, tudo local.
- **Redis** configurado para sessão, cache e filas — pronto para a Fase 3 (workers IA).
- **Permissões simples** por coluna `role` + tabela `user_permissions` — sem Spatie para manter a fase leve. Spatie pode ser adicionado na Fase 2 se necessário.

---

## Roles do sistema

```
admin_geral → agencia_admin → agencia_operador
            ↓
social_media / gestor_trafego / seo / copywriter / designer
            ↓
cliente_admin → cliente_colaborador → viewer
            ↓
ai_employee (sistema, sem login)
```

---

## Próximos passos — Fase 2

- [ ] CRUD completo de clientes (criar, editar, arquivar)
- [ ] CRUD completo de usuários (criar, editar, alterar senha)
- [ ] Convite de usuários por e-mail
- [ ] Gestão de permissões granulares por usuário
- [ ] Módulo de aprovações (create, approve, reject)
- [ ] Módulo de logs de auditoria
- [ ] Módulo de configurações editáveis
- [ ] 2FA opcional
- [ ] Notificações in-app
- [ ] Onboarding de clientes

---

*Documentação criada automaticamente — Fase 1 — 2026-05-22*
