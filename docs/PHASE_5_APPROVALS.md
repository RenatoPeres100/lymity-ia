# Fase 5 — Sistema Central de Aprovações

## Objetivo

Centralizar todas as aprovações da plataforma Lymity IA em um único sistema robusto:
posts, campanhas, orçamentos, blog, páginas de website, propostas, ações externas, tarefas IA e qualquer ação sensível futura.

---

## Models Criados

| Model | Tabela | Descrição |
|---|---|---|
| `ApprovalRequest` | `approval_requests` | Solicitação central de aprovação |
| `ApprovalAction` | `approval_actions` | Histórico de ações (aprovado, rejeitado, etc.) |
| `ApprovalComment` | `approval_comments` | Comentários na aprovação |
| `AppNotification` | `app_notifications` | Notificações internas do painel |

---

## Migrations Criadas

- `2026_05_23_500001_create_approval_requests_table`
- `2026_05_23_500002_create_approval_actions_table`
- `2026_05_23_500003_create_approval_comments_table`
- `2026_05_23_500004_create_app_notifications_table`

---

## Rotas Admin

```
GET    /admin/approvals                          → lista com filtros
GET    /admin/approvals/create                   → formulário de criação
POST   /admin/approvals                          → criar aprovação
GET    /admin/approvals/{id}                     → detalhe + histórico + comentários
POST   /admin/approvals/{id}/approve             → aprovar
POST   /admin/approvals/{id}/reject              → rejeitar
POST   /admin/approvals/{id}/request-changes     → pedir alteração
POST   /admin/approvals/{id}/cancel              → cancelar
POST   /admin/approvals/{id}/comments            → adicionar comentário
```

## Rotas Cliente

```
GET    /client/approvals                         → lista (filtrado pelo próprio client_id)
GET    /client/approvals/{id}                    → detalhe + histórico
POST   /client/approvals/{id}/approve            → aprovar
POST   /client/approvals/{id}/reject             → rejeitar
POST   /client/approvals/{id}/request-changes    → pedir alteração
POST   /client/approvals/{id}/comments           → comentar
```

---

## ApprovalService

**Arquivo:** `app/Services/Approval/ApprovalService.php`

### Métodos

| Método | Descrição |
|---|---|
| `createApproval(array $data)` | Cria nova ApprovalRequest + ApprovalAction (created) + notificações |
| `approve(request, user, notes)` | Aprova + sync approvable + log + notificação |
| `reject(request, user, notes)` | Rejeita + sync approvable + log + notificação |
| `requestChanges(request, user, notes)` | Pede alteração + sync + log + notificação |
| `cancel(request, user, notes)` | Cancela + log + notificação |
| `canUserApprove(user, request)` | Verifica permissão por role + client_id |
| `logApprovalAction(...)` | Registra ApprovalAction |
| `syncApprovableStatus(...)` | Atualiza o model relacionado conforme decisão |
| `notifyRelevantUsers(...)` | Cria AppNotification para admins e solicitante |

---

## Regras de Aprovação por Perfil

| Role | Pode aprovar |
|---|---|
| `admin_geral` | Tudo |
| `agencia_admin` | Tudo (incluindo aprovações de cliente) |
| `agencia_operador` | Somente se tiver permissão `approvals.approve` |
| `cliente_admin` | Somente aprovações do próprio `client_id` |
| `cliente_colaborador` | Somente se `client_id` coincidir e tiver `approvals.approve` |
| `viewer` | Não aprova |
| `ai_employee` | Não aprova |

---

## Integração com AiTask

Quando uma `AiTask` é executada com `requires_approval = true`:

1. Após a execução, o status da tarefa vai para `waiting_approval`.
2. Uma `ApprovalRequest` é criada automaticamente pelo `AiTaskService`.
3. O `approvable_type = AiTask::class`, `approvable_id = task.id`.
4. Ao aprovar a `ApprovalRequest`: a tarefa vai para `approved` + log registrado.
5. Ao rejeitar: a tarefa vai para `rejected` + log registrado.
6. Ao pedir alteração: a tarefa permanece em `waiting_approval`.

**Importante:** Tarefas `sensitive_action = true` geram `sensitive_level = high` e `approval_type = external_action`.

---

## Integração com BlogPost

Quando `approvable_type = BlogPost`:
- **Aprovado** → `status = approved`
- **Rejeitado** → `status = draft` (BlogPost não tem enum `rejected`)
- **Pedir alteração** → `status = draft`

---

## Integração com ClientWebsitePage

Quando `approvable_type = ClientWebsitePage`:
- **Aprovado** → `status = approved`
- **Rejeitado** → `status = draft`
- **Pedir alteração** → `status = draft`

---

## Notificações Internas

Usa `AppNotification` (tabela `app_notifications`). Não envia e-mail nesta fase.

Notificado quando:
- Aprovação criada → admin_geral e agencia_admin
- Aprovação aprovada/rejeitada/alteração solicitada → admins + cliente_admin do cliente + solicitante

**Nota:** Não usa o sistema nativo de notifications do Laravel para evitar conflito com a tabela `notifications`. Usa a tabela `app_notifications` com model `AppNotification`.

---

## Logs

Usa `ActivityLog::create()` (tabela existente das fases anteriores).

`module = approvals`, `action = approved | rejected | requested_changes | canceled`.

---

## Seeders

`ApprovalRequestSeeder` cria 4 registros demo:
1. Post pendente (low)
2. Campanha crítica pendente (critical)
3. Página website pendente (medium) — se existir página demo
4. Proposta aprovada (high) — status `approved`

---

## Credenciais de Teste

```
Admin Geral:
  email: admin@lymity.local
  senha: password

Cliente:
  email: cliente@lymity.local
  senha: password
```

---

## Como Testar Visualmente

**URL base:** `http://187.124.133.195:8000`

### Admin
1. Login como `admin@lymity.local`
2. `/admin/approvals` → ver lista com filtros e cards de stats
3. `/admin/approvals/create` → criar aprovação manual
4. Abrir aprovação → aprovar / rejeitar / pedir alteração
5. Verificar histórico de ApprovalActions na tela
6. Adicionar comentário

### Cliente
1. Login como `cliente@lymity.local`
2. `/client/approvals` → ver apenas aprovações do próprio cliente
3. Abrir aprovação → aprovar
4. Tentar acessar aprovação de outro cliente → deve retornar 403

### Fluxo AiTask → ApprovalRequest
1. `/admin/ai-tasks/create` com `requires_approval = true`
2. Executar a tarefa (botão "Executar")
3. Verificar status `waiting_approval`
4. Ir para `/admin/approvals` → ApprovalRequest criada automaticamente

---

## Comandos de Teste

```bash
# Verificar counts
php artisan tinker --execute="echo 'ApprovalRequests: '.App\Models\ApprovalRequest::count();"

# Criar e aprovar via service
php artisan tinker --execute="..."

# Testar integração AiTask
php artisan tinker --execute="..."

# Regressão fases anteriores
php artisan ai:run-schedules
```

---

## Testes de Regressão

Todas as rotas das fases 1–4 continuam funcionando (302 para autenticadas, 200 para públicas). Nenhum 500.

---

## Próximos Passos — Fase 6

- Notificações em tempo real (WebSocket / Pusher)
- E-mail de notificação de aprovação
- Dashboard de aprovações com gráficos/métricas
- Integração com campanhas (Google Ads / Meta Ads)
- Módulo de relatórios com aprovação de publicação
- API para clientes externos consultarem status de aprovação
