# Fluxo Operacional Real de Conteúdo — Lymity IA

## Visão Geral

Este documento descreve o fluxo real de criação, aprovação e publicação de conteúdo operado pela plataforma Lymity IA.

---

## Fluxo Completo

```
[Rotinas IA] → [Geração de Conteúdo] → [Aprovação] → [Agendamento] → [Publicação]
```

### 1. Geração automática via agentes IA

- Comando: `agents:run-due-routines`
- Os agentes IA (Social Media IA, Copywriter IA, etc.) executam rotinas definidas
- Geram rascunhos de posts sociais (`SocialPost`) e posts de blog (`BlogPost`)
- Status inicial: `draft`

### 2. Aprovação

- Admin recebe notificação e acessa `/admin/approvals`
- Aprova ou rejeita o conteúdo
- Após aprovação: `status = approved`, `approved_by`, `approved_at` preenchidos

### 3. Agendamento

- Admin acessa `/admin/social/posts/{id}` ou `/admin/blog/pipeline`
- Define `scheduled_at`
- Status muda para `scheduled`

### 4. Publicação automática (scheduler)

- `content:run-publishing-cycle` executa periodicamente:
  1. `agents:run-due-routines` — novas gerações
  2. `blog:publish-due` — publica posts de blog agendados
  3. `social:publish-due` — publica posts sociais agendados no Instagram

### 5. Publicação manual

- Admin acessa `/admin/social/posts/{id}`
- Clica em "Publicar agora no Instagram"
- Sistema valida canal, aprovação, imagem e executa publicação

---

## Fluxo Real do Instagram

### Pré-requisitos

1. Conta Instagram @lymity.ia do tipo Business/Creator
2. App Meta configurado em developers.facebook.com
3. Variáveis `META_APP_ID`, `META_APP_SECRET`, `META_REDIRECT_URI` no `.env`
4. Canal conectado via OAuth em `/admin/social/instagram`
5. `INSTAGRAM_PUBLISHING_ENABLED=true` após validação

### Fluxo de publicação no Instagram

```
SocialPost (status=scheduled)
    ↓ social:publish-due
    ↓ InstagramPublishingService.canPublish() → true
    ↓ validateChannel() → canal connected, token valid
    ↓ validatePost() → approved, public_image_url HTTPS
    ↓ post.markPublishing() → status = publishing
    ↓ createMediaContainer(instagram_user_id, public_image_url, caption)
    ↓ publishContainer(creation_id)
    ↓ post.markPublished(external_post_id) → status = published
    ↓ ActivityLog: instagram_publish_success
```

### Em caso de falha

```
    ↓ handleError(exception)
    ↓ post.markFailed(error_message) → status = failed
    ↓ ActivityLog: instagram_publish_failed
    ↓ SocialChannel.markError(error_message)
```

---

## Bloqueios de segurança

O sistema bloqueia publicação automaticamente se:

| Condição | Resultado |
|---|---|
| `INSTAGRAM_PUBLISHING_ENABLED=false` | Bloqueado, nenhuma chamada à API |
| Canal não conectado | Bloqueado, log de aviso |
| Token ausente ou expirado | Bloqueado, log de aviso |
| `instagram_user_id` ausente | Bloqueado |
| Post não aprovado | Bloqueado |
| Post `rejected/archived/failed` | Bloqueado |
| `public_image_url` ausente ou não HTTPS | Bloqueado |
| Legenda vazia | Bloqueado |
| `requires_approval=true` sem `approved_by` | Bloqueado |

---

## Comandos operacionais

```bash
# Ciclo completo (agentes + blog + social)
php artisan content:run-publishing-cycle

# Publicar posts sociais agendados
php artisan social:publish-due

# Publicar posts de blog agendados
php artisan blog:publish-due

# Executar rotinas de agentes IA
php artisan agents:run-due-routines

# Diagnóstico completo
php artisan content:diagnose
```

---

## Logs operacionais

Todos os eventos são registrados em `activity_logs`:

| Ação | Módulo | Quando |
|---|---|---|
| `instagram_oauth_started` | instagram | Início do OAuth |
| `instagram_oauth_connected` | instagram | OAuth concluído |
| `instagram_connection_checked` | instagram | Verificação de status |
| `instagram_disconnected` | instagram | Desconexão |
| `instagram_publish_started` | instagram | Início de publicação |
| `instagram_publish_blocked` | instagram | Publicação bloqueada |
| `instagram_publish_container_created` | instagram | Container de mídia criado |
| `instagram_publish_success` | instagram | Publicação com sucesso |
| `instagram_publish_failed` | instagram | Falha na publicação |
| `social_post_marked_publishing` | instagram | Post marcado como publishing |
| `social_post_published` | instagram | Post publicado |
| `social_post_failed` | instagram | Post falhou |

Visualize em: `/admin/activity-logs`

---

## Segurança

- Somente `admin_geral` e `agencia_admin` podem conectar/desconectar Instagram
- Somente `admin_geral` e `agencia_admin` podem publicar manualmente
- O scheduler publica apenas posts `approved + scheduled`
- Tokens são criptografados no banco e nunca aparecem em views ou logs
- Erros contendo tokens são sanitizados antes de salvar
- Posts de clientes externos não são publicados automaticamente nesta fase

---

## Painel de monitoramento

- Instagram: https://ia.lymity.com.br/admin/social/instagram
- Posts: https://ia.lymity.com.br/admin/social/posts
- Command Center: https://ia.lymity.com.br/admin/content-command-center
- Operação: https://ia.lymity.com.br/admin/operation
- Logs: https://ia.lymity.com.br/admin/activity-logs
