# Contextual Image Generation

## Service

`App\Services\Content\ContextualImageGenerationService`

## Métodos

| Método | Descrição |
|---|---|
| `generateForBlogPost(BlogPost, User)` | Gera imagem usando contexto do artigo (title, excerpt, focus_keyword, seo_description) |
| `generateForSocialPost(SocialPost, User)` | Gera imagem usando contexto do post (title, main_caption, objective, creative_brief) |
| `attachToBlogPost(BlogPost, AiGeneratedImage, User)` | Vincula imagem ao blog post |
| `attachToSocialPost(SocialPost, AiGeneratedImage, User)` | Vincula imagem ao post social |
| `validateBlogPostImage(BlogPost)` | Valida imagem do blog |
| `validateSocialPostImage(SocialPost)` | Valida imagem do social |
| `calculateSocialPostContextHash(SocialPost)` | Hash do contexto para detectar texto alterado |
| `isSocialPostImageOutdated(SocialPost)` | Detecta se texto foi alterado após geração da imagem |

## Campos BlogPost

| Campo | Valores |
|---|---|
| `featured_image` | URL da imagem |
| `featured_image_generation_id` | FK para `ai_image_generations` |
| `featured_image_source` | `upload`, `external_url`, `ai_generated` |
| `featured_image_status` | `missing`, `valid`, `invalid`, `generated`, `uploaded`, `external`, `failed` |
| `featured_image_error` | Mensagem de erro |

## Campos SocialPost (existentes)

| Campo | Valores |
|---|---|
| `public_image_url` | URL pública da imagem |
| `image_validation_status` | `valid`, `invalid`, `pending` |
| `image_status` | Status geral da imagem |
| `ai_image_generation_id` | FK para `ai_image_generations` |
| `image_generated_from_caption_hash` | Hash do contexto no momento da geração |
| `image_generation_mode` | `ai_single`, `upload`, `external_url` |

## Regra de imagem desatualizada (SocialPost)

Quando o texto (title, main_caption, objective, content_type) é alterado após a geração da imagem:
- `image_generated_from_caption_hash` diverge do hash atual
- O pipeline social mostra overlay amarelo no preview
- Botão "Regen. imagem" aparece no card

## Brand Context padrão

```
Lymity IA — agência digital inteligente.
Visual premium, tecnológico, limpo e editorial.
Sem texto pequeno ou ilegível.
Cores: tons escuros com destaques em indigo/violeta.
Sem rostos genéricos.
```

## Logs gerados

- `blog_image_generated` / `blog_image_attached`
- `social_image_generated` / `social_image_attached`
- `social_image_invalid` / `social_image_outdated`
