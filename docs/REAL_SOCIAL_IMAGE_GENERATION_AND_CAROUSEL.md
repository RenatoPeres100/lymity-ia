# Social Media — Image Generation & Carousel Pipeline

## Fluxo oficial (texto antes da imagem)

```
Briefing do post
  ↓
Gemini gera legenda/texto
  ↓
Admin revisa/edita texto
  ↓
Sistema gera prompt visual baseado no texto final
  ↓
Gemini gera imagem ou carrossel
  ↓
Sistema valida imagem pública (HTTPS, JPEG/PNG, dimensões)
  ↓
Admin revisa imagem
  ↓
Admin pode substituir por upload ou regenerar com IA
  ↓
Admin aprova
  ↓
Admin agenda ou publica
  ↓
Instagram publica (@lymity.ia)
  ↓
Permalink e logs salvos
```

**Regra fundamental:** a imagem é sempre gerada *depois* do texto final. Nunca antes.

---

## Imagem desatualizada

Quando o admin edita o texto (`main_caption`) após a imagem ter sido gerada, o sistema:

1. Calcula o MD5 do texto atual e compara com `image_generated_from_caption_hash`
2. Se diferente → `isImageOutdated()` retorna `true`
3. Na tela do post aparece o alerta laranja: **"Imagem desatualizada"**
4. A aprovação e publicação ficam bloqueadas até regenerar

O campo `image_metadata['image_outdated'] = true` também é marcado.

---

## Estrutura de dados

### social_posts (campos de imagem)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `image_generation_mode` | string | `none`, `ai_single`, `ai_carousel`, `upload`, `external_url` |
| `image_prompt` | text | Prompt usado para gerar a imagem |
| `image_prompt_source_hash` | string | MD5 de todos os campos visuais no momento da geração |
| `image_generated_from_caption_hash` | string | MD5 da legenda no momento da geração |
| `image_last_generated_at` | timestamp | Data/hora da última geração |
| `image_path` | text | Caminho local no storage |
| `image_url` | text | URL pública |
| `public_image_url` | text | URL pública validada (usada para publicar) |
| `image_status` | string | `missing`, `generating`, `generated`, `validating`, `valid`, `invalid`, `failed`, `replaced` |
| `image_provider` | string | `gemini`, `upload`, `url` |
| `image_metadata` | json | Metadados (mime, tamanho, modelo, etc.) |
| `image_validation_status` | string | `valid`, `invalid` |
| `image_validation_error` | text | Mensagem de erro da validação |
| `carousel_enabled` | boolean | Se é publicação de carrossel |
| `carousel_slide_count` | int | Quantidade de slides |
| `carousel_status` | string | `none`, `planning`, `generating`, `generated`, `validating`, `valid`, `invalid`, `failed` |

### social_post_assets (carrossel)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `social_post_id` | FK | Post ao qual pertence |
| `type` | string | `image`, `video` |
| `source` | string | `generated`, `upload`, `external_url` |
| `provider` | string | `gemini`, `upload` |
| `path` | text | Caminho local |
| `public_url` | text | URL pública |
| `position` | int | Posição no carrossel (1-5) |
| `prompt` | text | Prompt usado para gerar este slide |
| `status` | string | `draft`, `generating`, `generated`, `validating`, `valid`, `invalid`, `failed`, `published` |
| `validation_error` | text | Erro de validação |
| `instagram_container_id` | string | ID do container IG deste slide |
| `metadata` | json | Metadados |

---

## Services

### SocialImagePromptService
`app/Services/Social/SocialImagePromptService.php`

- `buildSingleImagePrompt(SocialPost)` — prompt completo para imagem única, baseado em: legenda, objetivo, CTA, público, tom, brief, brand context
- `buildCarouselPlanPrompt(SocialPost)` — prompt para Gemini gerar o plano JSON do carrossel
- `buildCarouselSlidePrompt(SocialPost, slidePlan, position)` — prompt para cada slide
- `shouldSuggestCarousel(SocialPost)` — detecta se o conteúdo tem estrutura de carrossel (lista, passo a passo, framework)
- `hashPostVisualSource(SocialPost)` — MD5 de todos os campos visuais

### SocialImageService
`app/Services/Social/SocialImageService.php`

- `generateSingleImageFromPost(post, user)` — gera imagem (exige legenda)
- `generateWithGemini(post, user)` — alias interno com guards
- `generateCarouselFromPost(post, user)` — gera plano → slides → valida
- `replaceImageFromUpload(post, file, user)` — upload manual
- `replaceImageFromUrl(post, url, user)` — substituição por URL
- `validateImage(post)` — valida URL pública da imagem principal
- `validateCarouselAssets(post)` — valida todos os assets do carrossel
- `markImageOutdatedIfTextChanged(post)` — marca outdated se caption mudou

### GeminiImageGenerationService
`app/Services/AI/GeminiImageGenerationService.php`

- `generateAndStore(prompt, storagePath)` — gera imagem e salva no storage público
- `generateCarouselPlan(post, promptService)` — usa modelo de texto para gerar plano JSON

### PublicImageValidatorService
`app/Services/Social/PublicImageValidatorService.php`

- Valida: HTTPS, HTTP 200, Content-Type image/jpeg ou image/png, dimensões >= 320x320, tamanho <= 8MB

### InstagramPublishingService
`app/Services/Instagram/InstagramPublishingService.php`

- `publishSingleImage(channel, post)` — cria container → aguarda FINISHED → publica → salva permalink
- `publishCarousel(channel, post)` — cria containers de cada item → container pai → publica
- `createCarouselItemContainer(channel, imageUrl)` — POST media com is_carousel_item=true
- `createCarouselContainer(channel, childrenIds, caption)` — POST media com media_type=CAROUSEL

---

## Commands Artisan

```bash
# Gerar imagem para um post (texto obrigatório)
php artisan social:generate-image {post_id}

# Sugerir se o post é melhor como carrossel
php artisan social:suggest-carousel {post_id}

# Gerar carrossel para um post (texto obrigatório)
php artisan social:generate-carousel {post_id}

# Validar imagem de um post
php artisan social:validate-image {post_id}

# Validar todos os assets de carrossel de um post
php artisan social:validate-assets {post_id}

# Testar geração de imagem com Gemini (não publica)
php artisan social:test-gemini-image

# Publicar posts agendados/aprovados
php artisan social:publish-due

# Diagnóstico Instagram
php artisan instagram:diagnose-publishing
```

---

## Regras de aprovação

Post só pode ser aprovado se:
1. `main_caption` preenchida
2. `image_validation_status = valid`
3. `isImageOutdated() = false`

Se o admin editar o texto após aprovação → status volta para `draft`, `approved_by = null`, `approved_at = null`.

---

## Carrossel — regras

- Sugestão automática via `shouldSuggestCarousel()`: conteúdo educativo, listas, passo a passo
- Mínimo: 3 slides válidos (`SOCIAL_CAROUSEL_MIN_SLIDES`)
- Máximo: 5 slides (`SOCIAL_CAROUSEL_MAX_SLIDES`)
- Se carrossel incompleto: bloqueia com mensagem clara
- Se carrossel falhar: pode publicar como imagem única com confirmação do admin

---

## Upload manual

1. Admin acessa o post em `/admin/social/posts/{id}`
2. Na seção "Imagem do Post" → "Substituir por Upload"
3. Upload JPEG/PNG, máximo 8MB, mínimo 320x320px
4. Sistema salva em `storage/app/public/social/generated/{post_id}/image.jpg`
5. Valida automaticamente após upload

---

## URL Externa

1. Admin acessa o post
2. Na seção "Imagem do Post" → "Substituir por URL HTTPS"
3. Informa URL pública HTTPS
4. Sistema valida (HTTP 200, Content-Type, dimensões, tamanho)

---

## Configuração .env

```env
AI_PROVIDER=google
GEMINI_API_KEY=sua_chave
GEMINI_TEXT_MODEL=gemini-1.5-flash
GEMINI_IMAGE_MODEL=imagen-3.0-generate-002
AI_IMAGE_GENERATION_ENABLED=true
AI_SOCIAL_IMAGE_GENERATION_ENABLED=true
AI_SOCIAL_CAROUSEL_GENERATION_ENABLED=true

SOCIAL_IMAGE_DISK=public
SOCIAL_IMAGE_PATH=social/generated
SOCIAL_IMAGE_PUBLIC_BASE_URL=https://ia.lymity.com.br/storage
SOCIAL_IMAGE_DEFAULT_WIDTH=1080
SOCIAL_IMAGE_DEFAULT_HEIGHT=1080
SOCIAL_CAROUSEL_MAX_SLIDES=5
SOCIAL_CAROUSEL_MIN_SLIDES=3

INSTAGRAM_OFFICIAL_USERNAME=lymity.ia
INSTAGRAM_OFFICIAL_PAGE_ID=1069242536283477
INSTAGRAM_OFFICIAL_IG_USER_ID=17841434234661171
INSTAGRAM_PUBLISHING_ENABLED=true
```

---

## Erros comuns

| Erro | Causa | Solução |
|------|-------|---------|
| "Crie o texto antes de gerar a imagem" | `main_caption` vazia | Adicione ou gere a legenda |
| "Gemini Image Model não configurado" | `GEMINI_IMAGE_MODEL` ausente | Configure no `.env` |
| "Imagem desatualizada" | Texto editado após geração | Clique "Regenerar Imagem" |
| "URL retornou HTTP 404" | Arquivo não existe publicamente | Verifique storage/symlink |
| "Error validating access token" | Token Instagram expirado | Reconecte em `/admin/social/instagram` |
| "Apenas N slides válidos" | Carrossel incompleto | Regenere o carrossel |
| "is_carousel_item" error | Assets sem URL pública válida | Valide os assets antes |

---

## Tela de criação com IA (`/admin/social/posts/ai-create`)

Fluxo:
1. Admin informa briefing (tema, objetivo, público, tom, CTA, brief)
2. Admin escolhe "Não gerar imagem agora" (recomendado) ou formato
3. Gemini gera legenda automaticamente
4. Sistema redireciona para show do post
5. Admin revisa e edita o texto
6. Admin clica "2. Gerar Imagem com IA" (só aparece habilitado quando há legenda)
7. Sistema gera imagem baseada no texto final, valida, atualiza status
8. Admin aprova → agenda → publica

---

*Última atualização: 2026-06-01*
