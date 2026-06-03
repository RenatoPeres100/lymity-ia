# Real Phase — Approval Email Notifications

## Objetivo

Notificar automaticamente por e-mail os aprovadores corretos quando um conteúdo precisar de aprovação no sistema Lymity IA.

---

## Variáveis de Ambiente

Configure no `.env` da produção:

```env
# SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.seuprovedor.com
MAIL_PORT=587
MAIL_USERNAME=notificacoes@lymity.com.br
MAIL_PASSWORD=sua-senha-aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="notificacoes@lymity.com.br"
MAIL_FROM_NAME="Lymity IA"

# Notificações de aprovação
APPROVAL_EMAIL_NOTIFICATIONS_ENABLED=true
APPROVAL_EMAIL_SIGNED_LINK_TTL_HOURS=72
APPROVAL_EMAIL_REMINDER_ENABLED=true
APPROVAL_EMAIL_REMINDER_HOURS_BEFORE_DUE=24
APPROVAL_EMAIL_MAX_REMINDERS=3
```

Enquanto `MAIL_MAILER=log`, os e-mails são gravados em `storage/logs/laravel.log` e não enviados por SMTP — útil para testes.

---

## Fluxo de Envio

1. `ApprovalService::createApproval()` cria a `ApprovalRequest` e despacha `SendApprovalEmailNotificationJob`
2. O Job chama `ApprovalEmailNotificationService::sendCreatedNotification()`
3. O service resolve os destinatários corretos
4. Para cada destinatário: gera URLs assinadas, cria log em `approval_email_notifications` e envia o e-mail
5. Após envio bem-sucedido: atualiza `approval_requests.notification_status = 'sent'` e `notified_at`

---

## Destinatários

| Conteúdo | Destinatários |
|---|---|
| Agência (client_id = null) | Todos os usuários com role `admin_geral` ou `agencia_admin` ativos |
| Cliente (client_id preenchido) | Usuários com role `cliente` ou `cliente_admin` do mesmo `client_id` + colaboradores com permissão `approvals.approve` |

**Regra de isolamento:** conteúdos de um cliente nunca notificam usuários de outro cliente.

---

## Links Assinados (Segurança)

Os botões do e-mail geram URLs assinadas com Laravel `temporarySignedRoute`:

```
GET /approval-email/{approval_id}/confirm/{action}?expires=...&uid=...&signature=...
```

**Por que GET não aprova diretamente?**

- Bots, pré-visualizadores de e-mail e proxies podem fazer GET em links automaticamente
- Um GET direto que executa ação é vulnerável a CSRF e pré-carregamento involuntário
- O GET apenas exibe a **tela de confirmação** — a ação é executada apenas por POST

**Fluxo seguro:**
1. Usuário clica no link do e-mail → GET abre tela de confirmação
2. Usuário lê o resumo e clica "Confirmar"
3. POST executa a ação via `ApprovalService`

**TTL:** configurável via `APPROVAL_EMAIL_SIGNED_LINK_TTL_HOURS` (padrão: 72h)

---

## Como Testar SMTP

```bash
# 1. Configure MAIL_* no .env com credenciais reais
# 2. Rode o diagnóstico
php artisan approvals:diagnose-email

# 3. Envie e-mail de teste
php artisan approvals:test-email seu@email.com

# 4. Se MAIL_MAILER=log, inspecione o log
tail -50 storage/logs/laravel.log
```

---

## Como Reenviar E-mail

**Via painel admin:**
1. Acesse `/admin/approvals/{id}`
2. Na seção "Notificações por E-mail", clique em "Reenviar e-mail de aprovação"

**Via command (futuro):**
```bash
# Não existe command dedicado — use tinker:
php artisan tinker --execute="
  \$approval = App\Models\ApprovalRequest::find(ID);
  \$service = app(App\Services\Approvals\ApprovalEmailNotificationService::class);
  \$service->sendCreatedNotification(\$approval, force: true);
"
```

---

## Lembretes

O comando `approvals:send-pending-reminders` roda **a cada hora** (scheduler):

- Busca aprovações com `status=pending`
- Verifica se `needsReminder()` retorna true (prazo próximo OU última notificação > 24h)
- Respeita `APPROVAL_EMAIL_MAX_REMINDERS`
- Incrementa `reminder_count` e atualiza `last_reminder_at`

```bash
# Rodar manualmente:
php artisan approvals:send-pending-reminders
```

---

## Commands

| Command | Descrição |
|---|---|
| `approvals:diagnose-email` | Diagnóstico completo do sistema de e-mail |
| `approvals:test-email {email}` | Envia e-mail de teste sem criar aprovação real |
| `approvals:send-pending-reminders` | Envia lembretes para aprovações pendentes |

---

## Logs

Todos os eventos são registrados em `activity_logs` (módulo `approvals`):

| Ação | Quando |
|---|---|
| `approval_email_queued` | Job despachado ao criar aprovação |
| `approval_email_sent` | E-mail enviado com sucesso |
| `approval_email_failed` | Falha ao enviar e-mail |
| `approval_email_skipped` | E-mail não enviado (desabilitado ou sem destinatário) |
| `approval_email_reminder_sent` | Lembrete enviado |
| `approval_email_action_opened` | Link do e-mail aberto (tela de confirmação) |
| `approval_email_action_confirmed` | Ação confirmada via e-mail |
| `approval_email_resend_requested` | Admin solicitou reenvio |

Tabela de log detalhado: `approval_email_notifications`

---

## Erros Comuns

| Erro | Causa | Solução |
|---|---|---|
| "Connection refused" | SMTP não acessível | Verificar MAIL_HOST e firewall |
| "Authentication failed" | Credenciais inválidas | Verificar MAIL_USERNAME e MAIL_PASSWORD |
| Link expirado | TTL venceu | Pedir reenvio ou acessar painel |
| "Nenhum destinatário" | Sem admins ativos | Verificar usuários com role admin_geral |
| E-mail no log mas não na caixa | MAIL_MAILER=log | Mudar para smtp em produção |

---

## Regressão

Após deploy, verificar:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan approvals:diagnose-email
php artisan blog:publish-due
php artisan social:publish-due
php artisan content:run-publishing-cycle
```

Rotas principais sem 500:
- `/` — site institucional
- `/login` — autenticação
- `/admin/approvals` — lista aprovações
- `/admin/approvals/{id}` — detalhes com seção de e-mail
- `/blog` — blog público
