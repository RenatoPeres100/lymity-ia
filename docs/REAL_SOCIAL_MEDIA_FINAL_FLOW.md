# Social Media — Fluxo Final Operacional

## Token persistente — Instagram @lymity.ia

### Como o token é gerenciado

O token de acesso Instagram é salvo **criptografado** em `social_channels.access_token` (cast `encrypted` do Laravel).

- O token nunca é pedido no momento de publicar
- O sistema usa sempre o canal `@lymity.ia` salvo no banco
- O token é removido apenas quando o admin clicar "Desconectar"
- Se o token expirar, `status` vira `expired` e o painel mostra botão "Reconectar"

### Canal oficial

```
ID: 3 (social_channels)
platform: instagram
account_name: @lymity.ia
instagram_user_id: 17841434234661171
facebook_page_id: 1069242536283477
account_url: https://instagram.com/lymity.ia
```

### Como conectar Instagram

**Via OAuth (recomendado):**
1. Acesse `/admin/social/instagram`
2. Clique "Conectar Instagram"
3. Autorize no Meta
4. Sistema salva token automaticamente

**Via importação de token validado (ponte técnica):**
```bash
# Adicione ao .env:
META_TEST_USER_TOKEN=EAA...seu_token...

# Execute:
php artisan instagram:import-tested-token \
  --from-env=META_TEST_USER_TOKEN \
  --page-id=1069242536283477 \
  --ig-user-id=17841434234661171 \
  --username=lymity.ia \
  --expires-days=55 \
  --force

# Se token ainda não expirou na API Meta (validação normal)
# Se token expirado na API mas estrutura válida (emergência):
php artisan instagram:import-tested-token ... --skip-validation --force
```

### Como desconectar

1. Acesse `/admin/social/instagram`
2. Clique "Desconectar"
3. Token é removido do banco
4. Status vira `disconnected`

---

## Fluxo completo de publicação

```
1. Criar post (texto obrigatório primeiro)
   ↓
2. Adicionar imagem (upload / URL / Gemini após texto)
   ↓
3. Validar imagem (HTTPS, 200, JPEG/PNG, ≥320x320, ≤8MB)
   ↓
4. Enviar para aprovação (legenda + imagem válida obrigatórios)
   ↓
5. Aprovar (admin)
   ↓
6. Agendar (data/hora futura)
   ↓
7. Publicar: social:publish-due ou botão manual
   ↓
8. Permalink + logs salvos
```

---

## Imagem — Regras

### Texto antes da imagem (obrigatório)
A imagem com IA só pode ser gerada quando `main_caption` estiver preenchida.

### Imagem desatualizada
Quando o admin edita o texto após gerar a imagem:
- `isImageOutdated()` retorna `true` (compara MD5 da legenda atual com `image_generated_from_caption_hash`)
- Alerta laranja aparece na tela do post e na tela de edição
- Aprovação e publicação ficam bloqueadas
- Admin precisa regenerar ou substituir a imagem

### Campos de imagem em social_posts

| Campo | Descrição |
|-------|-----------|
| `image_generation_mode` | `none`, `ai_single`, `ai_carousel`, `upload`, `external_url` |
| `image_generated_from_caption_hash` | MD5 da legenda no momento da geração |
| `image_last_generated_at` | Timestamp da última geração |
| `image_status` | `missing`, `generating`, `generated`, `validating`, `valid`, `invalid`, `failed`, `replaced` |
| `image_validation_status` | `valid`, `invalid` |
| `public_image_url` | URL HTTPS pública — usada para publicar no Instagram |

---

## Opções de imagem

### 1. Upload manual
- JPEG ou PNG, máximo 8MB, mínimo 320x320px
- Salvo em `storage/app/public/social/generated/{post_id}/image.jpg`
- URL: `https://ia.lymity.com.br/storage/social/generated/{post_id}/image.jpg`
- Validado automaticamente após upload

### 2. URL pública HTTPS
- Link direto para arquivo JPEG/PNG
- Validado: HTTP 200, Content-Type correto, dimensões, tamanho
- Usado diretamente como `public_image_url`

### 3. Gerar com Gemini
- Exige `main_caption` preenchida
- Prompt construído por `SocialImagePromptService` usando: legenda, CTA, objetivo, público, tom, brief, brand context
- Imagem salva em storage público
- Validada automaticamente após geração
- Model: configurado em `GEMINI_IMAGE_MODEL` no `.env`

---

## Aprovação obrigatória

Post só pode ser enviado para aprovação se:
1. `main_caption` preenchida
2. `public_image_url` preenchida
3. `image_validation_status = valid`
4. `isImageOutdated() = false`

Ao editar texto ou imagem depois de aprovado:
- Status volta para `draft`
- `approved_by = null`
- `approved_at = null`

---

## Agendamento e publicação

**Agendar:**
- Post precisa estar com `status = approved`
- `scheduled_at` obrigatório

**Publicar automaticamente:**
```bash
php artisan social:publish-due
```
Publica posts com `status approved/scheduled`, `scheduled_at <= now()`, imagem válida, canal conectado.

**Publicar manualmente:**
- Botão "Publicar agora" na tela do post (aparece quando tudo válido)

---

## Carrossel

```bash
# Analisar se o post é candidato a carrossel
php artisan social:suggest-carousel {post_id}

# Gerar slides com IA
php artisan social:generate-carousel {post_id}

# Validar assets
php artisan social:validate-assets {post_id}
```

Critérios para sugerir carrossel: conteúdo educativo, listas, passo a passo, frameworks, comparações.

---

## Commands

```bash
# Diagnóstico Instagram
php artisan instagram:diagnose-publishing

# Importar token validado
php artisan instagram:import-tested-token --force [--skip-validation]

# Gerar imagem (exige main_caption)
php artisan social:generate-image {post_id}

# Sugerir carrossel
php artisan social:suggest-carousel {post_id}

# Gerar carrossel
php artisan social:generate-carousel {post_id}

# Validar imagem
php artisan social:validate-image {post_id}

# Validar assets carrossel
php artisan social:validate-assets {post_id}

# Testar geração de imagem (não publica)
php artisan social:test-gemini-image

# Publicar posts devidos
php artisan social:publish-due

# Testar publicação Instagram
php artisan social:test-instagram-publish {post_id?}
```

---

## Erros comuns

| Erro | Causa | Solução |
|------|-------|---------|
| "Crie o texto antes de gerar a imagem" | `main_caption` vazia | Preencher legenda |
| "Token expirado" | Access token Meta expirado | Reconectar via OAuth ou reimportar via comando |
| "Canal não conectado" | Canal sem token | Conectar em `/admin/social/instagram` |
| "Imagem desatualizada" | Texto editado após imagem | Regenerar imagem |
| "Post não aprovado" | Falta aprovação | Enviar para aprovação e aprovar |
| "URL retornou HTTP 404" | Arquivo não existe | Verificar storage e symlink |
| "Content-Type inválido" | URL não é imagem direta | Usar link direto para arquivo |

---

## Configuração .env

```env
# Fluxo validado: Facebook Login + Instagram Graph API
META_AUTH_MODE=facebook_login
META_APP_ID=
META_APP_SECRET=
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
INSTAGRAM_PUBLISHING_ENABLED=true

# Instagram IDs validados
INSTAGRAM_OFFICIAL_USERNAME=lymity.ia
INSTAGRAM_OFFICIAL_PAGE_ID=1069242536283477
INSTAGRAM_OFFICIAL_IG_USER_ID=17841434234661171

# Ponte técnica (token manual — atualizar quando expirar)
META_TEST_USER_TOKEN=EAA...

# Gemini
AI_PROVIDER=google
GEMINI_API_KEY=
GEMINI_IMAGE_MODEL=imagen-3.0-generate-002

# Social image storage
SOCIAL_IMAGE_DISK=public
SOCIAL_IMAGE_PATH=social/generated
SOCIAL_IMAGE_PUBLIC_BASE_URL=https://ia.lymity.com.br/storage
```

---

*Última atualização: 2026-06-01*
