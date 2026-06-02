# Instagram Connection — Lymity IA

Canal oficial: **@lymity.ia** | Domínio: **https://ia.lymity.com.br**

---

## Fluxo validado

**Facebook Login + Instagram Graph API**

Este é o fluxo validado e em produção. Não usar `instagram_business_login` como fluxo principal.

| Campo              | Valor               |
|--------------------|---------------------|
| Facebook Page ID   | `1069242536283477`  |
| Page Name          | Lymity IA           |
| Instagram User ID  | `17841434234661171` |
| Instagram Username | `@lymity.ia`        |
| IG Name            | Lymity - Agency IA  |

---

## Configuração .env

```env
META_AUTH_MODE=facebook_login
META_APP_ID=<seu_app_id>
META_APP_SECRET=<seu_app_secret>
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

## Fluxo OAuth

1. Admin clica em "Conectar Instagram"
2. Sistema gera URL: `https://www.facebook.com/v25.0/dialog/oauth?scope=pages_show_list,...`
3. Usuário autoriza no Facebook
4. Meta redireciona para callback com `code`
5. Backend: `code` → short-lived token → long-lived token (60 dias)
6. Backend: `/me/accounts` → encontra página `1069242536283477`
7. Backend: `/{page_id}?fields=instagram_business_account` → `id=17841434234661171`, `username=lymity.ia`
8. Token salvo **criptografado** em `social_channels.access_token`
9. Canal marcado como `connected`

---

## Publicação

O sistema **nunca pede token manual**. Usa sempre o token salvo em `social_channels`:

```
POST https://graph.facebook.com/v25.0/{ig_user_id}/media     → cria container
GET  /{creation_id}?fields=status_code,status                 → aguarda FINISHED
POST https://graph.facebook.com/v25.0/{ig_user_id}/media_publish → publica
GET  /{media_id}?fields=id,permalink,caption,media_type,timestamp → permalink
```

Permalink real validado: `https://www.instagram.com/p/DY7w8jyjVQQ/`

---

## Token persistente

- Token salvo criptografado via Laravel `encrypted` cast
- Nunca aparece em logs, telas ou JSON público
- Válido por ~60 dias
- Renovação automática via `instagram:refresh-tokens` (diário 03:00)
- Se expirar: canal = `needs_reconnect`, admin reconecta via OAuth

---

## Reconexão

O histórico de posts **não é afetado** ao reconectar.

Se o token expirar:
1. Canal marcado como `needs_reconnect`
2. Admin acessa `/admin/social/instagram`
3. Clica em "Reconectar Instagram"
4. Fluxo OAuth completo novamente

---

## Diagnóstico

```bash
php artisan instagram:diagnose-oauth
php artisan instagram:diagnose-publishing
php artisan instagram:connection-check
```

---

## Importar token de emergência (fallback técnico)

```bash
php artisan instagram:import-tested-token \
  --from-env=META_TEST_USER_TOKEN \
  --page-id=1069242536283477 \
  --ig-user-id=17841434234661171 \
  --username=lymity.ia \
  --force
```

---

## NÃO usar

- `META_AUTH_MODE=instagram_business_login` — fluxo alternativo, não é o principal
- `instagram.com/oauth/authorize` — endpoint do fluxo instagram_business_login
- `instagram_business_basic` / `instagram_business_content_publish` — scopes do outro fluxo
