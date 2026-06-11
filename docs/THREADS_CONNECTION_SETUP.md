# Threads Connection Setup

## Visão Geral

O módulo Threads usa um canal `SocialChannel` separado do Instagram (`platform='threads'`).
Tokens, perfil e permissões são completamente independentes do Instagram.

## Duas Opções de Configuração

### Opção A — Reaproveitar app Meta/Instagram (recomendado)

Se o Instagram já está conectado, use o mesmo app Meta:

```env
THREADS_USE_META_APP=true
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_SCOPES=threads_basic,threads_content_publish
THREADS_PUBLISHING_ENABLED=false
# META_APP_ID e META_APP_SECRET já devem estar configurados
```

**Requisito:** O app Meta precisa ter o produto/caso de uso **Threads API** habilitado em
[developers.facebook.com](https://developers.facebook.com/apps).

### Opção B — App dedicado Threads

```env
THREADS_USE_META_APP=false
THREADS_APP_ID=seu_threads_app_id
THREADS_APP_SECRET=seu_threads_app_secret
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_SCOPES=threads_basic,threads_content_publish
THREADS_PUBLISHING_ENABLED=false
```

## Após alterar .env

```bash
php artisan config:clear
php artisan optimize:clear
php artisan threads:diagnose
```

## Fluxo OAuth

1. Acesse `/admin/social/threads`
2. Clique em **Conectar Threads**
3. Redireciona para `https://threads.net/oauth/authorize`
4. Autorize no Threads
5. Callback em `/admin/social/threads/callback`
6. Token salvo em `SocialChannel platform='threads'`

## Redirect URI no Meta

No painel [developers.facebook.com](https://developers.facebook.com/apps), em seu app:
- Produtos → Threads API → Configurações
- Valid OAuth Redirect URIs: `https://ia.lymity.com.br/admin/social/threads/callback`

## Diagnóstico

```bash
php artisan threads:diagnose
```

Mostra:
- `THREADS_USE_META_APP` e modo ativo
- Credenciais configuradas (sem expor secrets)
- Redirect URI e scopes
- Status do canal
- Token e expiração
- Posts por status
- Últimos erros

## Separação do Instagram

- `SocialChannel platform='instagram'` → não alterado
- `SocialChannel platform='threads'` → canal separado
- Token Instagram não é usado para Threads
- `instagram_user_id` e `facebook_page_id` ficam null no canal Threads
- `threads_user_id` só existe no canal Threads
