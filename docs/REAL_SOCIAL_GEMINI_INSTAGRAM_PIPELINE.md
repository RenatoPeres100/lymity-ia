# Pipeline Real: Gemini Image Generation + Instagram Publishing

## Visão Geral

Este documento descreve o fluxo completo de criação, geração de conteúdo com IA, aprovação, validação e publicação real de posts no Instagram oficial @lymity.ia.

**Canal oficial**: `@lymity.ia` — Instagram User ID `17841434234661171`
**Domínio**: `https://ia.lymity.com.br`
**Fase**: feed de imagem única. Carrossel e Reels fora desta fase.

---

## Variáveis .env necessárias

```env
# IA - Gemini
AI_PROVIDER=google
GEMINI_API_KEY=AIza...
GEMINI_TEXT_MODEL=gemini-2.5-flash
GEMINI_IMAGE_MODEL=imagen-3.0-generate-002
AI_TEXT_ONLY_MODE=false
AI_IMAGE_GENERATION_ENABLED=true

# Meta / Instagram
META_AUTH_MODE=facebook_login
META_APP_ID=...
META_APP_SECRET=...
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
META_FACEBOOK_SCOPES=pages_show_list,pages_read_engagement,business_management,instagram_basic,instagram_content_publish
INSTAGRAM_PUBLISHING_ENABLED=true

# Canal oficial
INSTAGRAM_OFFICIAL_USERNAME=lymity.ia
INSTAGRAM_OFFICIAL_PAGE_ID=1069242536283477
INSTAGRAM_OFFICIAL_IG_USER_ID=17841434234661171

# Storage de imagens
SOCIAL_IMAGE_DISK=public
SOCIAL_IMAGE_PATH=social/generated
SOCIAL_IMAGE_PUBLIC_BASE_URL=https://ia.lymity.com.br/storage

# Token temporário de teste (para importação via comando — substitua pelo OAuth)
META_TEST_USER_TOKEN=EAA...
```

---

## Fluxo completo

```
1. Admin cria post (manual ou via /ai-create)
   └── status = draft

2. [Opcional] Gemini gera legenda
   └── POST /admin/social/posts/{id}/generate-caption

3. [Opcional] Gemini gera imagem
   └── POST /admin/social/posts/{id}/generate-image
   └── Imagem salva em: storage/app/public/social/generated/{id}/image.jpg
   └── URL pública: https://ia.lymity.com.br/storage/social/generated/{id}/image.jpg
   └── Validação automática após geração

4. Admin revisa e edita
   └── GET /admin/social/posts/{id}/edit
   └── Pode substituir imagem por URL ou upload
   └── Pode editar legenda, hashtags, CTA, prompt de imagem

5. Admin envia para aprovação
   └── POST /admin/social/posts/{id}/send-approval
   └── Condição: legenda preenchida + imagem válida
   └── status = pending_approval
   └── ApprovalRequest criado automaticamente

6. Admin aprova
   └── POST /admin/social/posts/{id}/approve
   └── Condição: imagem_validation_status = valid
   └── status = approved, approved_by, approved_at

7. [Opcional] Admin agenda
   └── PATCH /admin/social/posts/{id}/schedule
   └── status = scheduled, scheduled_at

8. Publicação
   a. Manual: POST /admin/social/posts/{id}/publish-instagram-now
   b. Automática: php artisan social:publish-due (cron)

9. Pipeline de publicação (InstagramPublishingService)
   └── POST /{ig-user-id}/media (criar container)
   └── Salva instagram_container_id
   └── GET /{container-id}?fields=status_code (aguarda FINISHED)
   └── POST /{ig-user-id}/media_publish
   └── GET /{media-id}?fields=id,permalink
   └── status = published
   └── Salva external_post_id + permalink

10. Logs criados em cada etapa via ActivityLog
```

---

## Validação de Imagem

Critérios obrigatórios:
- URL começa com `https://`
- URL não é localhost/IP privado
- HTTP 200
- Content-Type: `image/jpeg` ou `image/png`
- Dimensões mínimas: 320x320px
- Tamanho máximo: 8MB

Status possíveis (`image_validation_status`): `valid` | `invalid`

---

## Geração de Imagem com Gemini

Modelos tentados (em ordem):
1. `GEMINI_IMAGE_MODEL` (padrão: `imagen-3.0-generate-002`) via `/generateImages`
2. `gemini-2.0-flash-exp-image-generation` via `/generateContent`
3. `gemini-2.0-flash-exp` via `/generateContent`
4. `gemini-2.0-flash` via `/generateContent`

Imagem salva em: `storage/app/public/social/generated/{post_id}/image.{ext}`

URL pública: `https://ia.lymity.com.br/storage/social/generated/{post_id}/image.{ext}`

---

## Como importar token validado (ponte operacional)

```bash
# 1. Adicione ao .env:
META_TEST_USER_TOKEN=EAA...

# 2. Execute:
php artisan instagram:import-tested-token \
  --from-env=META_TEST_USER_TOKEN \
  --page-id=1069242536283477 \
  --ig-user-id=17841434234661171 \
  --username=lymity.ia

# O token é validado via /me, /me/permissions e /{page_id}
# Armazenado criptografado no canal social_channels
# NUNCA impresso na tela ou em logs
```

> **Atenção**: Este comando é uma ponte para validação. O OAuth definitivo é feito via painel em `/admin/social/instagram`.

---

## Como renovar o token futuramente

1. Acesse `/admin/social/instagram` no painel
2. Clique em "Conectar Instagram"
3. Autorize via Facebook Login
4. O token é salvo automaticamente no canal
5. Tokens Facebook User geralmente expiram em 60 dias — tokens de longa duração (Long-Lived) duram 60 dias
6. Configure refresh automático via webhook ou cron quando disponível

---

## Comandos disponíveis

```bash
# Diagnóstico completo do pipeline
php artisan instagram:diagnose-publishing

# Importar token testado manualmente
php artisan instagram:import-tested-token --from-env=META_TEST_USER_TOKEN ...

# Criar post de teste com IA (draft — sem publicar)
php artisan social:generate-test-post

# Gerar imagem para um post existente
php artisan social:generate-image {post_id}

# Validar imagem de um post
php artisan social:validate-image {post_id}

# Publicar posts devidos (cron)
php artisan social:publish-due

# Testar publicação real (pede confirmação)
php artisan social:test-instagram-publish
php artisan social:test-instagram-publish {post_id}
php artisan social:test-instagram-publish --use-placeholder  # placeholder sem Gemini
```

---

## Telas disponíveis

- `/admin/social/posts` — Lista de posts
- `/admin/social/posts/create` — Criar post manual
- `/admin/social/posts/ai-create` — Criar post com IA (Gemini)
- `/admin/social/posts/{id}` — Visualizar post com ações completas
- `/admin/social/posts/{id}/edit` — Editar post
- `/admin/social/instagram` — Conexão do canal Instagram

---

## Regras de segurança

- Token Meta **nunca** aparece em tela, log ou resposta HTTP
- Gemini API Key **nunca** aparece em tela ou log
- Posts gerados por IA começam como `draft` — nunca publicam automaticamente
- Aprovação é obrigatória antes de qualquer publicação
- Imagem válida é pré-requisito para aprovação e publicação
- `social:publish-due` verifica: status, approved_by, image_validation_status=valid, public_image_url HTTPS

---

## Erros comuns

| Erro | Causa | Solução |
|------|-------|---------|
| `INSTAGRAM_PUBLISHING_ENABLED=false` | Variável desativada | Configure `true` no .env |
| Canal não conectado | Token ausente ou expirado | Reconecte em `/admin/social/instagram` ou importe com o comando |
| Imagem inválida | URL privada, 404, tamanho | Use URL HTTPS pública, mínimo 320x320px |
| Container ERROR | Meta rejeitou a imagem | Verifique dimensões, formato e URL pública |
| Gemini falhou | Modelo sem acesso a geração de imagem | Verifique se GEMINI_API_KEY tem acesso ao Imagen. Substitua imagem manualmente |

---

## Limite desta fase

- Somente **feed de imagem única** (`content_type=feed`)
- Carrossel: tabela `social_post_assets` preparada, não utilizada nesta fase
- Reels/vídeo: fora desta fase
- Clientes não publicam diretamente — somente Admin Geral controla publicação da agência
