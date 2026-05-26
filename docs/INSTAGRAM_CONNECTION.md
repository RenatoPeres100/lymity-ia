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
   - Permissions added: `pages_show_list`, `pages_read_engagement`, `instagram_basic`, `instagram_content_publish`

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
   - Validates the OAuth `state` against the session (CSRF protection).
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
| OAuth CSRF | `state` parameter stored in session, validated on callback |
| Publish guard | `INSTAGRAM_PUBLISHING_ENABLED` checked first before any API call |

Tokens are **never shown** in any view, log, or API response.

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
| `database/migrations/2026_05_26_130153_add_instagram_fields_to_social_channels_table.php` | Schema for instagram_user_id, facebook_page_id, permissions, etc. |
| `resources/views/admin/social/instagram/index.blade.php` | Connection management UI |

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
- [ ] META_REDIRECT_URI configured and matches Meta App settings
- [ ] Instagram account is Business/Creator
- [ ] Instagram linked to a Facebook Page
- [ ] OAuth flow completed successfully
- [ ] Channel shows `connected` status
- [ ] instagram_user_id populated in DB
- [ ] `INSTAGRAM_PUBLISHING_ENABLED=true` (only after above steps validated)
