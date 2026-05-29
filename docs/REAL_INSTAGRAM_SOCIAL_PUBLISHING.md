# Social Media & Instagram Publishing — Lymity IA

Canal oficial: **@lymity.ia**
Domínio: **https://ia.lymity.com.br**
App Meta: **aceito/publicado**

---

## Status do App Meta

O app Meta foi aceito e publicado. O fluxo de OAuth via Facebook Login está ativo.
Permissões configuradas:
- `pages_show_list`, `pages_read_engagement`, `instagram_basic`, `instagram_content_publish`
- `instagram_business_basic`, `instagram_business_content_publish`

---

## Variáveis de ambiente necessárias

```env
META_AUTH_MODE=facebook_login
META_APP_ID=<valor_real>
META_APP_SECRET=<valor_real>
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
META_FACEBOOK_SCOPES=pages_show_list,pages_read_engagement,instagram_basic,instagram_content_publish
META_INSTAGRAM_SCOPES=instagram_business_basic,instagram_business_content_publish
INSTAGRAM_PUBLISHING_ENABLED=true
```

**Nunca versionar META_APP_SECRET, access_token ou refresh_token.**

---

## Como conectar o Instagram

1. Acesse `/admin/social/instagram`
2. Confira que META_APP_ID e META_APP_SECRET estão configurados
3. Clique em **Conectar**
4. Faça login com a conta Facebook vinculada ao @lymity.ia
5. Autorize as permissões solicitadas
6. Após o callback, clique em **Verificar** para confirmar `instagram_user_id` e `facebook_page_id`
7. Confirme que o status do canal é `connected`

---

## Como ativar publicação real

Após conectar e validar o canal:

```bash
# No .env da VPS:
INSTAGRAM_PUBLISHING_ENABLED=true

# Limpar cache:
php artisan config:clear
php artisan optimize:clear
```

---

## Como criar um post

1. Acesse `/admin/social/posts/create`
2. Preencha: título, objetivo, tipo de conteúdo (feed), legenda, hashtags, CTA
3. Insira `public_image_url` com uma URL **HTTPS pública** (ex: `https://ia.lymity.com.br/storage/imagem.jpg`)
4. Salve como rascunho

---

## Como aprovar um post

1. Abra o post em `/admin/social/posts/{id}`
2. Clique em **Enviar para aprovação** (se `requires_approval=true`)
3. Clique em **Aprovar**
4. O post receberá `approved_by` e `approved_at`

---

## Como agendar um post

1. Post precisa estar com status `approved`
2. Clique em **Agendar**
3. Informe data/hora futura
4. Status muda para `scheduled`

---

## Como publicar agora

1. Post precisa estar `approved` ou `scheduled`
2. Acesse o post e clique em **Publicar no Instagram agora**
3. Rota: `POST /admin/social/posts/{socialPost}/publish-instagram-now`
4. Requer: canal connected, INSTAGRAM_PUBLISHING_ENABLED=true, public_image_url válida

---

## Validação de imagem pública

A imagem deve estar em uma URL HTTPS pública acessível pela Meta:

```
✓ https://ia.lymity.com.br/storage/imagens/post.jpg
✗ http://...  (não HTTPS)
✗ localhost
✗ 192.168.x.x
✗ 127.0.0.1
```

Para verificar:
```bash
php artisan tinker --execute="echo app(\App\Services\Social\PublicImageValidatorService::class)->explain('https://...').PHP_EOL;"
```

---

## Command: social:publish-due

Publica posts da agência com status `scheduled` e `scheduled_at <= now()`:

```bash
php artisan social:publish-due
```

**Saída esperada:**
```
SOCIAL_PUBLISH_STARTED
DUE_POSTS=X
PUBLISHED=X
FAILED=X
SOCIAL_PUBLISH_FINISHED
```

**Bloqueios automáticos:**
- `INSTAGRAM_PUBLISHING_ENABLED=false`
- Canal não conectado ou token inválido
- Post não aprovado (`requires_approval=true` sem `approved_by`/`approved_at`)
- `public_image_url` ausente ou não HTTPS

---

## Command: content:run-publishing-cycle

Executa o ciclo completo (agentes + blog + social):

```bash
php artisan content:run-publishing-cycle
```

---

## Scheduler / Cron

O scheduler Laravel roda a cada minuto automaticamente.

Verificar:
```bash
php artisan schedule:list
crontab -l
```

O crontab deve conter:
```
* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1
```

---

## Diagnóstico

```bash
php artisan instagram:diagnose-publishing
```

Verifica:
- META config
- Feature flags
- Canal @lymity.ia (status, instagram_user_id, facebook_page_id, token)
- Commands registrados
- Cron configurado
- Posts agendados e pendentes
- Últimos erros

---

## Logs

Eventos registrados na tabela `activity_logs`:

| Ação | Descrição |
|------|-----------|
| `social_post_created` | Post criado |
| `social_post_submitted_approval` | Enviado para aprovação |
| `social_post_approved` | Aprovado |
| `social_post_rejected` | Rejeitado |
| `social_post_scheduled` | Agendado |
| `social_post_publish_now_clicked` | Publicar agora clicado |
| `social_post_publishing` | Publicando |
| `social_post_published` | Publicado com sucesso |
| `social_post_failed` | Falha na publicação |
| `instagram_publish_started` | Início da publicação |
| `instagram_publish_blocked` | Publicação bloqueada |
| `instagram_publish_container_created` | Container criado na Meta |
| `instagram_publish_success` | Publicado na Meta |
| `instagram_publish_failed` | Erro na Meta |

---

## Erros comuns

| Erro | Causa | Solução |
|------|-------|---------|
| Canal não conectado | Primeira vez ou token expirado | Reconectar em /admin/social/instagram |
| INSTAGRAM_PUBLISHING_ENABLED=false | Flag desabilitada | Setar true no .env e limpar cache |
| public_image_url inválida | URL não HTTPS ou inacessível | Usar URL HTTPS pública acessível pela Meta |
| Token expirado | Token vencido | Reconectar o Instagram |
| Post não aprovado | requires_approval=true sem aprovação | Aprovar o post antes de publicar |

---

## Cuidados com tokens

- **Nunca** salvar ou exibir access_token em tela ou log
- Tokens são sanitizados automaticamente (regex `EAA[A-Za-z0-9]+` → `[TOKEN_REDACTED]`)
- Tokens de longa duração expiram em ~60 dias — monitorar `token_expires_at`
- Após reconectar, verificar com o botão **Verificar** na tela de Instagram

---

## Fluxo completo

```
Criar rascunho → Enviar para aprovação → Aprovar → Agendar → social:publish-due → Publicado
                                               ↓
                                     Ou: Publicar Agora → Publicado
```
