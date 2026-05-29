# Instagram Connection — Real Phase 3

## Overview

This document describes how to connect the @lymity.ia Instagram Business account
to the Lymity IA platform using the Meta Graph API.

The integration is **read-ready** — all infrastructure is in place. Publishing
remains disabled until the connection is validated (`INSTAGRAM_PUBLISHING_ENABLED=false`).

---

## Prerequisites

1. Instagram account is **Business or Creator** type.
2. Instagram is **linked to a Facebook Page**.
3. A **Meta App** exists at developers.facebook.com with:
   - Type: Business
   - Redirect URI configured: `https://ia.lymity.com.br/admin/social/instagram/callback`
   - Permissions added: `instagram_business_basic`, `instagram_business_content_publish`
   - (Escopos legados `instagram_basic`, `instagram_content_publish` não são mais aceitos em apps publicados)

---

## Environment Variables

Add to `/var/www/lymity-ia/.env`:

```env
META_APP_ID=your_app_id
META_APP_SECRET=your_app_secret
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
INSTAGRAM_PUBLISHING_ENABLED=false
```

After editing:

```bash
php artisan config:cache
```

---

## Connection Flow

1. Navigate to **Admin → Conexão Instagram** (`/admin/social/instagram`).
2. Click **Conectar Instagram**.
3. Authorize the app on Facebook/Instagram (grant all required permissions).
4. Callback returns to `/admin/social/instagram/callback`.
5. The system:
   - Validates the OAuth `state` via **database** (`instagram_oauth_states` table, expires in 15 min) with session fallback.
   - Exchanges the short-lived code for a long-lived token (~60 days).
   - Fetches the Facebook Pages linked to the account.
   - Finds the Instagram Business Account linked to a Page.
   - Saves the channel with encrypted token, Instagram User ID, Facebook Page ID.

---

## Enabling Publishing

Only after the connection is validated and tested:

```env
INSTAGRAM_PUBLISHING_ENABLED=true
```

```bash
php artisan config:cache
```

Then posts with `approved` or `scheduled` status can be published via
`InstagramPublishingService`.

---

## Security Design

| Layer | Mechanism |
|---|---|
| Token storage | `encrypted` cast on `access_token` / `refresh_token` in `SocialChannel` |
| Token serialization | `hidden` array on model prevents tokens from appearing in JSON/arrays |
| Log redaction | `preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', ...)` in all services |
| OAuth CSRF | `state` (64 chars) stored in `instagram_oauth_states` DB table (SHA-256 hash), expires 15 min; session used as fallback only |
| Publish guard | `INSTAGRAM_PUBLISHING_ENABLED` checked first before any API call |

Tokens are **never shown** in any view, log, or API response.

---

## Troubleshooting: "URL bloqueada pela Meta"

The Meta callback URL is blocked when the redirect URI does not match exactly.

Steps to fix:

1. Open your app at [developers.facebook.com](https://developers.facebook.com/apps).
2. Go to **Facebook Login → Settings**.
3. Under **Valid OAuth Redirect URIs** add exactly:
   ```
   https://ia.lymity.com.br/admin/social/instagram/callback
   ```
   - No trailing slash.
   - No query string.
   - Must be HTTPS.
4. Under **App Domains** add:
   ```
   ia.lymity.com.br
   ```
5. Ensure **Client OAuth Login: ON**, **Web OAuth Login: ON**, **Use Strict Mode for Redirect URIs: ON**, **Enforce HTTPS: ON**.
6. Save and try connecting again.

---

## Troubleshooting: "Estado OAuth inválido ou expirado"

The OAuth state is a one-time security token that expires in 15 minutes and is stored in the `instagram_oauth_states` database table.

Common causes:

- The OAuth flow took more than 15 minutes.
- The browser session changed (different tab, incognito, cookie cleared).
- The callback URL was opened manually instead of via Meta redirect.

Fix:

1. Run `php artisan config:clear && php artisan optimize:clear`.
2. Return to `/admin/social/instagram`.
3. Click **Conectar Instagram** again — a new state is generated.
4. Complete the authorization within 15 minutes without changing browser/tab.

Diagnostic:

```bash
php artisan instagram:diagnose-oauth
```

---

## Key Files

| File | Purpose |
|---|---|
| `config/meta.php` | All Meta/Instagram config |
| `config/features.php` | Feature flags (`instagram_connection`, `instagram_publishing`) |
| `app/Services/Instagram/MetaInstagramAuthService.php` | OAuth flow, token exchange, channel persistence |
| `app/Services/Instagram/InstagramPublishingService.php` | Media containers, publishing, error handling |
| `app/Http/Controllers/Admin/InstagramConnectionController.php` | Admin routes handler |
| `app/Models/SocialChannel.php` | Channel model with encrypted tokens and status helpers |
| `app/Models/InstagramOAuthState.php` | OAuth state persistence model |
| `database/migrations/2026_05_29_200000_create_instagram_oauth_states_table.php` | `instagram_oauth_states` table |
| `database/migrations/2026_05_26_130153_add_instagram_fields_to_social_channels_table.php` | Schema for instagram_user_id, facebook_page_id, permissions, etc. |
| `resources/views/admin/social/instagram/index.blade.php` | Connection management UI with diagnostics |

---

## Channel Status Values

| Status | Meaning |
|---|---|
| `not_configured` | Meta App not configured in .env |
| `disconnected` | Channel exists but no active token |
| `connected` | Token valid, Instagram account linked |
| `expired` | Token expired, reconnect required |
| `error` | Last operation failed (see `last_error` column) |

---

## Testing Without Credentials

```bash
php artisan tinker
>>> $auth = app(App\Services\Instagram\MetaInstagramAuthService::class);
>>> $auth->isConfigured(); // false — safe, no API calls made

>>> $pub = app(App\Services\Instagram\InstagramPublishingService::class);
>>> $channel = App\Models\SocialChannel::where('platform', 'instagram')->first();
>>> $pub->canPublish($channel); // false — publishing disabled
```

---

## Checklist

- [ ] META_APP_ID configured
- [ ] META_APP_SECRET configured
- [ ] META_AUTH_MODE=facebook_business_login
- [ ] META_FACEBOOK_SCOPES=instagram_business_basic,instagram_business_content_publish
- [ ] META_REDIRECT_URI configured and matches Meta App settings
- [ ] Instagram account is Business/Creator
- [ ] Instagram linked to a Facebook Page
- [ ] OAuth flow completed successfully
- [ ] Channel shows `connected` status
- [ ] instagram_user_id populated in DB
- [ ] `INSTAGRAM_PUBLISHING_ENABLED=true` (only after above steps validated)

---

## Correção de Invalid Scopes

**Erro:** `Invalid Scopes: instagram_basic, instagram_content_publish`

**Causa:** Sistema solicitando escopos antigos ou incompatíveis com o app Meta publicado.

**Correção no `.env`:**
```env
META_AUTH_MODE=facebook_business_login
META_FACEBOOK_SCOPES=instagram_business_basic,instagram_business_content_publish
META_INSTAGRAM_SCOPES=instagram_business_basic,instagram_business_content_publish
```

Depois rodar:
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```
