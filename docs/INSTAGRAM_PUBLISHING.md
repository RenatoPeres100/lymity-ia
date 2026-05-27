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
6. Adicione as permissões:
   - `pages_show_list`
   - `pages_read_engagement`
   - `instagram_basic`
   - `instagram_content_publish`
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
