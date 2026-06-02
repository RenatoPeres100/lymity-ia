# Instagram Publishing — Lymity IA

Canal oficial: **@lymity.ia** | Domínio: **https://ia.lymity.com.br**

---

## Fluxo validado

**Facebook Login + Instagram Graph API** (`META_AUTH_MODE=facebook_login`)

Endpoint OAuth: `https://www.facebook.com/v25.0/dialog/oauth`

Publicação real validada:
- Permalink: `https://www.instagram.com/p/DY7w8jyjVQQ/`

---

## Variáveis de ambiente

```env
META_AUTH_MODE=facebook_login
META_APP_ID=<valor_real>
META_APP_SECRET=<valor_real>
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
META_FACEBOOK_SCOPES=pages_show_list,pages_read_engagement,business_management,instagram_basic,instagram_content_publish
META_INSTAGRAM_SCOPES=
INSTAGRAM_PUBLISHING_ENABLED=true
INSTAGRAM_OFFICIAL_USERNAME=lymity.ia
INSTAGRAM_OFFICIAL_PAGE_ID=1069242536283477
INSTAGRAM_OFFICIAL_IG_USER_ID=17841434234661171
```

---

## Dados validados

| Campo             | Valor               |
|-------------------|---------------------|
| PAGE_ID           | `1069242536283477`  |
| PAGE_NAME         | Lymity IA           |
| IG_USER_ID        | `17841434234661171` |
| IG_USERNAME       | `lymity.ia`         |
| IG_NAME           | Lymity - Agency IA  |

Permissões validadas no teste:
- `pages_show_list`
- `pages_read_engagement`
- `business_management`
- `instagram_basic`
- `instagram_content_publish`

---

## Fluxo de publicação

```
social:publish-due
  → busca SocialPost (status=scheduled, scheduled_at<=now, approved, image válida)
  → busca SocialChannel (platform=instagram, company_id, status=connected)
  → InstagramPublishingService::publishSingleImage($channel, $post)
    → POST graph.facebook.com/v25.0/{ig_user_id}/media (image_url, caption, access_token)
    → GET /{creation_id}?fields=status_code,status → aguarda FINISHED
    → POST graph.facebook.com/v25.0/{ig_user_id}/media_publish (creation_id, access_token)
    → GET /{media_id}?fields=id,permalink,caption,media_type,timestamp
    → post.status = published, post.permalink = permalink
```

**Frontend nunca envia token. Token fica no backend em social_channels.**

---

## Token expirado durante publicação

Se o token expirar:
- `InstagramPublishingService::handleError()` registra log seguro (sem token)
- `channel.status = error` ou `needs_reconnect`
- `post.status = failed`, `post.publication_error = "Token expirado..."`
- Admin vê o erro em `/admin/social/instagram` e reconecta

---

## Scheduler

```bash
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

Agendamentos:
- `social:publish-due` → a cada minuto
- `instagram:refresh-tokens` → diariamente 03:00
- `content:run-publishing-cycle` → periódico

---

## Comandos de diagnóstico

```bash
php artisan instagram:diagnose-publishing
php artisan instagram:diagnose-oauth
php artisan instagram:connection-check
php artisan social:publish-due
```
