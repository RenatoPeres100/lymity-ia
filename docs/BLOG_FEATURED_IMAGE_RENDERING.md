# Blog Featured Image — Rendering e Resolução

## Como a imagem é salva

O campo `blog_posts.featured_image` recebe a URL pública da imagem gerada por IA no momento da geração, via `ContentPackageImageGenerationService::generateFeaturedImage()`. A URL tem o formato:

```
https://ia.lymity.com.br/storage/ai-images/packages/{pkg_id}/{filename}.jpg
```

O `GeneratedContentPackage.visual_payload` também armazena:
- `featured_image_url` — URL pública
- `featured_image_local` — path relativo no disco `public`
- `featured_image_asset_id` — ID do `GeneratedContentAsset`
- `image_alt` — texto alternativo

## BlogPostImageResolver

`App\Services\Blog\BlogPostImageResolver`

### Ordem de resolução de URL

1. `blog_posts.featured_image` (coluna direta — URL ou path)
2. `blog_posts.ai_metadata.featured_image_url`
3. `blog_posts.ai_metadata.image_url`
4. `GeneratedContentPackage.visual_payload.featured_image_url` (lookup por FK ou polimorfismo)
5. `GeneratedContentAsset` com `asset_type=featured_image` e `status=generated`

### Normalização de path

- URL completa (`http://` / `https://`) → retorna como está
- `/storage/...` → retorna como está
- `storage/...` → prefixado com `/`
- Contém `app/public/` → converte para `Storage::disk('public')->url()`
- Path relativo no disco public → `Storage::disk('public')->url($path)`

### Uso nas views

```blade
@php
    $imgResolver = app(\App\Services\Blog\BlogPostImageResolver::class);
    $featuredImageUrl = $imgResolver->resolveUrl($post);
    $featuredImageAlt = $imgResolver->resolveAlt($post);
    $featuredImageCap = $imgResolver->resolveCaption($post);
@endphp

@if($featuredImageUrl)
<img src="{{ $featuredImageUrl }}" alt="{{ $featuredImageAlt }}">
@endif
```

## Onde a imagem aparece

| Local | View |
|---|---|
| Página pública do post | `resources/views/site/blog-post.blade.php` |
| Listagem/cards do blog | `resources/views/site/blog.blade.php` |
| Aprovação admin | `resources/views/admin/blog/approvals/show.blade.php` |
| Post admin show | `resources/views/admin/blog/posts/show.blade.php` |

## Propagação na aprovação

`ApprovalService::syncGeneratedPackageStatus()` — ao aprovar um `GeneratedContentPackage` de blog, verifica se o `BlogPost` não tem `featured_image` e popula a partir do `visual_payload` ou do `GeneratedContentAsset`.

## Comandos

```bash
# Diagnóstico dos últimos posts gerados
php artisan blog:diagnose-generated-posts

# Verificar quais posts podem ter imagem reparada (sem alterar nada)
php artisan blog:repair-generated-images --dry-run

# Reparar posts sem imagem usando assets existentes
php artisan blog:repair-generated-images --fix

# Limitar quantidade
php artisan blog:repair-generated-images --fix --limit=100
```

## Posts sem imagem

Posts gerados antes do sistema de geração de imagens (sem `GeneratedContentPackage` associado) não têm imagem disponível. O comando `repair-generated-images` reporta esses posts como `no_package` — seria necessário regenerar a imagem manualmente via AI Image Studio.
