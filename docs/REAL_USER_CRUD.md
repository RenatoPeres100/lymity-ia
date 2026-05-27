# REAL USER CRUD — Gestão de Usuários

## Objetivo

CRUD operacional completo de usuários, roles e permissões para o painel administrativo da Lymity IA.

---

## Roles disponíveis

| Role | Descrição |
|---|---|
| `admin_geral` | Acesso irrestrito a toda a plataforma |
| `agencia_admin` | Admin interno da agência |
| `agencia_operador` | Operador interno com permissões configuráveis |
| `social_media` | Especialista social media |
| `copywriter` | Redator |
| `blog_writer` | Escritor de blog |
| `seo` | Especialista SEO |
| `designer` | Designer |
| `gestor_trafego` | Gestor de tráfego pago |
| `cliente_admin` | Admin do cliente |
| `cliente_colaborador` | Colaborador do cliente |
| `viewer` | Apenas leitura |
| `ai_employee` | Funcionário IA (não pode fazer login) |

---

## User Types

| user_type | Descrição |
|---|---|
| `internal` | Usuário interno da agência |
| `client` | Usuário do cliente |
| `ai_employee` | Funcionário IA automatizado |

---

## Status de usuário

- `active` — acesso normal
- `inactive` — sem acesso
- `blocked` — sem acesso, acesso negado explicitamente

---

## Permissões disponíveis

### Módulo: users
- `users.view` — Ver usuários
- `users.create` — Criar usuários
- `users.update` — Editar usuários
- `users.disable` — Inativar usuários
- `users.reset_password` — Resetar senhas
- `users.manage_permissions` — Gerenciar permissões

### Módulo: approvals
- `approvals.view`, `approvals.approve`, `approvals.reject`

### Módulo: blog
- `blog.view`, `blog.create`, `blog.approve`, `blog.publish`

### Módulo: social
- `social.view`, `social.create`, `social.approve`, `social.schedule`

### Módulo: instagram
- `instagram.connect`, `instagram.publish`

### Módulo: logs
- `logs.view`

### Módulo: system
- `system.health`

---

## Regras de segurança

1. `admin_geral` acessa tudo, sem verificação de permissão.
2. `agencia_admin` não pode editar nem criar `admin_geral`.
3. `cliente_admin` só vê usuários do próprio `client_id`.
4. `ai_employee` não faz login (bloqueado no LoginController e EnsureUserIsActive).
5. Usuário não pode inativar ou bloquear a própria conta.
6. Senha nunca é logada nem exibida após criação.
7. Todas as ações importantes geram `ActivityLog`.

---

## Quem pode criar quem

| Ator | Pode criar |
|---|---|
| `admin_geral` | Qualquer role |
| `agencia_admin` | Todos exceto `admin_geral` |
| `cliente_admin` (com permissão `users.create`) | `cliente_colaborador`, `viewer` |

---

## Como criar usuário interno

1. Acesse `/admin/users/create`
2. Selecione **Tipo: Interno (Agência)**
3. Selecione o **Role** desejado
4. Preencha nome, e-mail, cargo
5. Defina senha ou deixe em branco para gerar automaticamente
6. Selecione a empresa
7. Clique em **Criar usuário**

A senha temporária é exibida uma única vez após a criação.

---

## Como criar usuário cliente

1. Acesse `/admin/users/create`
2. Selecione **Tipo: Cliente**
3. Selecione `cliente_admin` ou `cliente_colaborador`
4. Vincule ao **Cliente** correto
5. Preencha os dados e crie

---

## Como resetar senha

1. Acesse `/admin/users/{id}`
2. Clique em **Resetar senha**
3. Informe e confirme a nova senha
4. Clique em **Redefinir senha**

Ação registrada em log. Senha anterior invalida-se imediatamente.

---

## Como inativar usuário

Via lista: botão **Inativar** na linha do usuário.  
Via show: botão **⏸ Inativar** no painel de ações.  
Via service: `UserManagementService::deactivate($user, $actor)`.

---

## Isolamento por cliente

Usuários `cliente_admin` e `cliente_colaborador` são filtrados automaticamente pelo `client_id`. Um `cliente_admin` não consegue ver, editar ou gerenciar usuários de outro cliente.

---

## Logs gerados

| Ação | Log |
|---|---|
| Criação | `user_created` |
| Edição | `user_updated` |
| Ativação | `user_activated` |
| Inativação | `user_deactivated` |
| Bloqueio | `user_blocked` |
| Reset senha | `user_password_reset` |
| Permissões | `user_permissions_updated` |

---

## Testes manuais

```bash
# Criar usuário interno
php artisan tinker --execute="..."

# Verificar logs
php artisan tinker --execute="echo App\Models\ActivityLog::where('module','users')->count();"

# Verificar usuários ativos
php artisan tinker --execute="echo App\Models\User::where('status','active')->count();"
```

---

## Próximos passos

- [ ] Notificação por e-mail ao criar usuário
- [ ] 2FA para admin_geral
- [ ] API REST para gestão de usuários
- [ ] Importação em massa via CSV
- [ ] Audit trail completo com diff de campos
