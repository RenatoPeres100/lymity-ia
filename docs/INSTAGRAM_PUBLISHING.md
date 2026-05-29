# Instagram Publishing — Lymity IA

## Objetivo

Integração real com o Instagram oficial **@lymity.ia** via Meta Graph API.
Permite publicação automática de posts aprovados e agendados diretamente no feed do Instagram da agência.

---

## Perfil oficial

- Instagram: `@lymity.ia`
- URL: https://instagram.com/lymity.ia
- Tipo de conta requerido: **Business** ou **Creator** (obrigatório para Graph API)

---

## Variáveis de ambiente obrigatórias

```env
META_APP_ID=seu_app_id_aqui
META_APP_SECRET=seu_app_secret_aqui
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
INSTAGRAM_PUBLISHING_ENABLED=false
```

> Nunca commitar credenciais reais. As variáveis devem existir apenas no `.env` do servidor.
> `INSTAGRAM_PUBLISHING_ENABLED=false` por padrão — ative somente após validar a conexão.

---

## Como configurar credenciais

1. Acesse https://developers.facebook.com
2. Crie um App do tipo "Business" ou "Consumer"
3. Adicione o produto **Instagram Graph API**
4. Em "Configurações do App → Básico", copie o **App ID** e o **App Secret**
5. Em "Instagram → Configurações de OAuth", adicione o Redirect URI:
   ```
   https://ia.lymity.com.br/admin/social/instagram/callback
   ```
6. Adicione as permissões (obrigatórias para apps publicados):
   - `instagram_business_basic`
   - `instagram_business_content_publish`
   - (Escopos antigos `instagram_basic`, `instagram_content_publish` geram Invalid Scopes em apps publicados)
7. Configure o `.env` do servidor com as credenciais

---

## Como conectar via OAuth

1. Acesse `/admin/social/instagram` no painel
2. Verifique o checklist — META_APP_ID e META_APP_SECRET devem estar "Configurado"
3. Clique em **Conectar Instagram**
4. Faça login com a conta Facebook vinculada ao Instagram @lymity.ia
5. Autorize as permissões solicitadas
6. Você será redirecionado de volta ao painel com o canal conectado

O sistema salva automaticamente:
- `instagram_user_id`
- `facebook_page_id`
- `access_token` (criptografado)
- `token_expires_at`
- `status = connected`

---

## Como validar a conexão

Na tela `/admin/social/instagram`:
- Status "Conectado" (verde)
- Instagram User ID preenchido
- Token não expirado

Ou pelo command:
```bash
php artisan content:diagnose
```

Para revalidar o token:
```bash
# Via painel: botão "Verificar Conexão"
# Via artisan:
php artisan tinker --execute="
\$ch = App\Models\SocialChannel::where('platform','instagram')->first();
app(App\Services\Instagram\MetaInstagramAuthService::class)->refreshConnectionStatus(\$ch);
echo \$ch->fresh()->status;
"
```

---

## Como criar um post com imagem

```php
SocialPost::create([
    'company_id'     => $company->id,
    'client_id'      => null,
    'created_by'     => $user->id,
    'title'          => 'Título do Post',
    'objective'      => 'authority',
    'content_type'   => 'feed',
    'creative_format'=> 'feed_image',
    'main_caption'   => 'Texto da publicação no Instagram.',
    'hashtags'       => '#LymityIA #IA',
    'cta'            => 'Conheça a Lymity IA',
    'status'         => 'draft',
    'requires_approval' => true,
    'public_image_url'  => 'https://ia.lymity.com.br/storage/imagem.jpg',
]);
```

A `public_image_url` deve ser uma URL HTTPS publicamente acessível.
Para imagens locais: use `Storage::disk('public')->url('nome_arquivo.jpg')` e garanta que `php artisan storage:link` foi executado.

---

## Como aprovar um post

1. No painel: `/admin/social/posts/{id}` → botão "Aprovar"
2. Ou via command:
```bash
php artisan tinker --execute="
\$post = App\Models\SocialPost::find(1);
\$admin = App\Models\User::where('email','admin@lymity.local')->first();
\$post->update(['status'=>'approved','approved_by'=>\$admin->id,'approved_at'=>now()]);
"
```

---

## Como agendar um post

1. No painel: `/admin/social/posts/{id}` → seção "Agendar"
2. Selecione data/hora e clique em "Agendar"
3. O post deve estar com `status = approved`
4. O `scheduled_at` deve ser uma data futura

---

## Como publicar

### Automático (agendado)

O scheduler executa `content:run-publishing-cycle` que chama `social:publish-due`.

```bash
# Verificar posts devidos
php artisan social:publish-due

# Ciclo completo
php artisan content:run-publishing-cycle
```

### Manual (pelo painel)

Em `/admin/social/posts/{id}`, clique em **"Publicar agora no Instagram"**.
O botão só aparece se:
- Post aprovado ou agendado
- Canal @lymity.ia conectado
- `INSTAGRAM_PUBLISHING_ENABLED=true`
- `public_image_url` válida (HTTPS)

---

## Comando social:publish-due

```bash
php artisan social:publish-due
```

**Saída esperada:**
```
SOCIAL_PUBLISH_STARTED
DUE_POSTS=2
  PUBLISHING #5 — Post de teste
  PUBLISHED #5 external_id=18012345678
  PUBLISHING #6 — Post agendado
  PUBLISHED #6 external_id=18012345679
PUBLISHED=2
FAILED=0
SOCIAL_PUBLISH_FINISHED
```

**Critérios para publicação automática:**
- `company_id` preenchido, `client_id` null (agência)
- `status = scheduled`
- `scheduled_at <= now()`
- `content_type = feed` ou `creative_format = feed_image`
- Aprovação respeitada
- Canal @lymity.ia conectado
- `INSTAGRAM_PUBLISHING_ENABLED=true`
- `public_image_url` HTTPS válida

---

## Comando content:run-publishing-cycle

Executa em sequência:
1. `agents:run-due-routines` — gera conteúdo via IA
2. `blog:publish-due` — publica posts de blog
3. `social:publish-due` — publica posts sociais

```bash
php artisan content:run-publishing-cycle
```

---

## Erros comuns

### Token expirado

**Sintoma:** `status = expired` no canal, publicações falhando.

**Solução:** Reconectar via OAuth em `/admin/social/instagram` → "Conectar Instagram".

O token Meta de longa duração dura ~60 dias. Reconecte periodicamente ou implemente refresh automático.

---

### Imagem não pública

**Sintoma:** `public_image_url ausente ou inválida`

**Solução:**
1. Garanta que o arquivo existe no storage: `storage/app/public/`
2. Execute `php artisan storage:link` para criar o symlink em `public/storage`
3. A URL deve ser `https://ia.lymity.com.br/storage/arquivo.jpg`
4. Teste acessando a URL em um browser sem login

---

### INSTAGRAM_PUBLISHING_ENABLED=false

**Sintoma:** Posts nunca publicam, comando retorna PUBLISHED=0.

**Solução:**
```bash
# No .env do servidor:
INSTAGRAM_PUBLISHING_ENABLED=true

# Limpar cache:
php artisan config:cache
```

> Ative apenas após validar a conexão OAuth e testar com imagem real.

---

### Instagram não conectado

**Sintoma:** `Canal Instagram não conectado`

**Solução:** Acesse `/admin/social/instagram` e clique em "Conectar Instagram".

---

### "URL bloqueada pela Meta" ao conectar

**Sintoma:** Durante o fluxo OAuth, a Meta retorna erro de URL bloqueada e redireciona de volta com `error=access_denied` ou similar.

**Causas e soluções:**

1. Acesse [developers.facebook.com](https://developers.facebook.com/apps) → seu App → **Facebook Login → Configurações**.
2. Em **Valid OAuth Redirect URIs**, adicione exatamente:
   ```
   https://ia.lymity.com.br/admin/social/instagram/callback
   ```
   - Sem barra final.
   - Sem query string.
   - Deve ser HTTPS.
3. Em **App Domains**, adicione:
   ```
   ia.lymity.com.br
   ```
4. Confirme que estão **ON**: Client OAuth Login, Web OAuth Login, Use Strict Mode for Redirect URIs, Enforce HTTPS.
5. Salve e tente conectar novamente.

---

### "Estado OAuth inválido ou expirado" ao voltar do Meta

**Sintoma:** Após autorizar no Meta, o sistema retorna com `error=Estado OAuth inválido ou expirado`.

**Causas:** O state OAuth é um token único salvo na tabela `instagram_oauth_states` com validade de 15 minutos. Expira ou fica inválido se:
- O fluxo demorou mais de 15 minutos.
- O navegador ou cookie mudou (aba incógnita, limpeza de cookies).
- A URL de callback foi aberta manualmente.

**Solução:**
```bash
php artisan config:clear
php artisan optimize:clear
```
1. Acesse `/admin/social/instagram`.
2. Clique em **Conectar Instagram** — um novo state será gerado e salvo no banco.
3. Conclua a autorização dentro de 15 minutos sem trocar de aba/navegador.

**Diagnóstico:**
```bash
php artisan instagram:diagnose-oauth
```

---

## Ativando INSTAGRAM_PUBLISHING_ENABLED=true

1. Valide a conexão OAuth completamente
2. Crie um post de teste com `public_image_url` real
3. Execute `php artisan social:publish-due` e verifique logs
4. Se tudo OK: edite o `.env` → `INSTAGRAM_PUBLISHING_ENABLED=true`
5. Execute `php artisan config:cache`

---

## Segurança de tokens

- O `access_token` é armazenado **criptografado** no banco (coluna com cast `encrypted`)
- O token **nunca** aparece em views, logs, exceptions ou metadata
- Erros da API Meta que contenham tokens são sanitizados: `EAA[...]` → `[TOKEN_REDACTED]`
- Somente `admin_geral` e `agencia_admin` podem conectar/desconectar e publicar manualmente
- O scheduler publica somente posts `approved + scheduled`, nunca `draft` ou `pending_approval`

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
