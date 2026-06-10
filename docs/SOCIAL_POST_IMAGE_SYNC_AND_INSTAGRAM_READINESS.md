# Social Post Image Sync & Instagram Readiness

## Fluxo da imagem gerada para SocialPost

```
AgentTaskExecutionService (gera texto + cria SocialPost)
  → createApprovalPackage (cria GeneratedContentPackage e backfills generated_content_package_id)
  → executeImageGenerationIfNeeded
    → ContentPackageImageGenerationService.generateFeedImage()
      → generateAsset() → generateAndStore (Gemini)
      → attachToContentPackage()  ← atualiza visual_payload
      → propagateImageToEntity()  ← AGORA propaga feed_image E featured_image para SocialPost
          → SocialPost.public_image_url = asset.public_url
          → SocialPost.image_url        = asset.public_url
          → SocialPost.image_path       = asset.local_path
          → SocialPost.image_status     = 'generated'
          → SocialPost.image_metadata   = { asset_id, package_id, ... }
```

## SocialPostImageResolver

`App\Services\Social\SocialPostImageResolver`

### Ordem de resolução de URL

1. `social_posts.public_image_url`
2. `social_posts.image_url`
3. `social_posts.image_path` → Storage::url()
4. `social_posts.metadata.public_image_url`
5. `social_posts.image_metadata.public_url`
6. `GeneratedContentPackage.visual_payload.feed_image_url`
7. `GeneratedContentPackage.visual_payload.featured_image_url`
8. `GeneratedContentAsset` com tipo `feed_image` ou `featured_image` e `status=generated`

### isValidPublicHttps(url)
- Retorna `true` apenas para URLs https:// sem localhost/127.0.0.1

## SocialPostReadinessService

`App\Services\Social\SocialPostReadinessService`

- `canBeScheduled(post)` → `{ready: bool, reasons: []}`
- `canBePublished(post)` → `{ready: bool, reasons: []}`
- `hasRequiredImage(post)` → `bool`
- `getBlockingReasons(post, action)` → `string[]`

Requer imagem HTTPS para: `feed`, `reels`, `story`

## Propagação na aprovação

`ApprovalService::syncGeneratedPackageStatus()` — ao aprovar, se `SocialPost.public_image_url` estiver vazio, busca em `visual_payload.feed_image_url` e nos assets.

## Instagram Publishing

`InstagramPublishingService` usa `SocialPostImageResolver` para:
- `canPublish()` — verifica imagem resolvida e HTTPS
- `validatePost()` — valida imagem antes de publicar
- `publishSingleImage()` — resolve URL de todas as fontes; sincroniza no post se diferente da coluna

## Comandos

```bash
# Diagnóstico completo (últimos 10 posts)
php artisan social:diagnose-images

# Diagnóstico de post específico
php artisan social:diagnose-images --post=17

# Ver o que seria corrigido (sem alterar)
php artisan social:repair-generated-images --dry-run

# Corrigir post específico
php artisan social:repair-generated-images --fix --post=17

# Corrigir todos os posts sem imagem
php artisan social:repair-generated-images --fix

# Sincronizar via botão no admin
POST /admin/social/posts/{id}/sync-generated-image
```

## Botão de sincronização no admin

Na tela `/admin/social/posts/{id}/show`:
- Se resolver encontra imagem mas `public_image_url` está vazio, mostra banner verde com botão **"Sincronizar Imagem Gerada"**
- O botão chama `POST /admin/social/posts/{id}/sync-generated-image`
- Popula `public_image_url`, `image_url`, `image_path`, `image_status=generated`, `image_validation_status=valid`

## Por que Instagram exige imagem pública HTTPS

A Meta Graph API só aceita `image_url` com URL HTTPS acessível publicamente. URLs de localhost, IP interno ou HTTP simples causam erro `OAuthException: (#324) Missing or invalid image file`.
