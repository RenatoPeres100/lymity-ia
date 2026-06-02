# AI Image Studio — Gemini Image Generation

## Objetivo

Módulo para geração de imagens com IA usando Gemini, atendendo posts de Instagram e artigos do blog da Lymity IA.

## Rotas

| Método | URL | Nome | Descrição |
|--------|-----|------|-----------|
| GET | /admin/ai-images | admin.ai-images.index | Listagem com filtros |
| GET | /admin/ai-images/create | admin.ai-images.create | Formulário de criação |
| POST | /admin/ai-images | admin.ai-images.store | Salvar nova geração |
| GET | /admin/ai-images/{id} | admin.ai-images.show | Detalhes + imagens |
| GET | /admin/ai-images/{id}/edit | admin.ai-images.edit | Editar geração |
| PUT | /admin/ai-images/{id} | admin.ai-images.update | Atualizar geração |
| POST | /admin/ai-images/{id}/generate | admin.ai-images.generate | Disparar geração |
| POST | /admin/ai-images/{id}/regenerate | admin.ai-images.regenerate | Regenerar |
| POST | /admin/ai-images/{id}/archive | admin.ai-images.archive | Arquivar |
| POST | /admin/ai-images/{id}/attach/social/{spId} | admin.ai-images.attach.social | Vincular a post social |
| POST | /admin/ai-images/{id}/attach/blog/{bpId} | admin.ai-images.attach.blog | Vincular a blog post |
| GET | /admin/ai-images/settings | admin.ai-images.settings | Config Gemini |
| POST | /admin/ai-images/settings/test | admin.ai-images.settings.test | Testar conexão |
| GET | /admin/ai-images/logs | admin.ai-images.logs | Logs de gerações |

## Tabelas

### ai_image_generations
- Registro central de cada geração (única ou carrossel)
- Campos: channel, purpose, generation_type, title, subject, prompt_context, tone, visual_style, brand_context, overlay_mode, slides_count, aspect_ratio, status, provider, model, error_message, generated_at

### ai_generated_images
- Imagens físicas resultantes de cada geração
- Campos: ai_image_generation_id, slide_number, storage_path, public_url, mime_type, prompt_used, status

### blog_posts (campo adicionado)
- featured_image_generation_id (FK opcional para ai_image_generations)

### social_posts (campo adicionado)
- ai_image_generation_id (FK opcional para ai_image_generations)

## Services

### App\Services\AiImages\AiImagePromptBuilderService
- Constrói prompts especializados por canal (blog/social/general)
- Suporta carrossel com estrutura de slides (gancho → problema → solução → CTA)
- Respeita overlay_mode para safe areas

### App\Services\AiImages\GeminiAiImageService
- Wrapper sobre GeminiImageGenerationService existente
- generateSingle() / generateCarousel()
- Persiste imagens em ai_generated_images
- Retorna URLs públicas em https://ia.lymity.com.br/storage/ai-images/...

### App\Services\AiImages\AiGeneratedImageValidatorService
- Valida arquivo em storage, URL pública, MIME type, tamanho

## Jobs

- GenerateAiImageJob — geração única em background (2 tentativas, 180s timeout)
- GenerateAiCarouselJob — carrossel em background (1 tentativa, 600s timeout)

## Configuração Gemini

### .env necessário
```
AI_PROVIDER=google
GEMINI_API_KEY=sua_chave_aqui
GEMINI_TEXT_MODEL=gemini-2.5-flash
GEMINI_IMAGE_MODEL=imagen-3.0-generate-002
GEMINI_IMAGE_ENABLED=true
AI_IMAGE_STORAGE_DISK=public
AI_IMAGE_STORAGE_PATH=ai-images
AI_IMAGE_PUBLIC_BASE_URL=https://ia.lymity.com.br/storage
```

### Como testar conexão
```bash
php artisan ai-images:diagnose
php artisan ai-images:test-gemini
```

## Como gerar imagem única (CLI)
```bash
php artisan ai-images:generate-single \
  --channel=social \
  --title="Automação de marketing com IA" \
  --context="Imagem para post da Lymity IA sobre automação" \
  --tone="estratégico e sofisticado" \
  --style="tecnológico premium" \
  --overlay=safe_bottom
```

## Como gerar carrossel (CLI)
```bash
php artisan ai-images:generate-carousel \
  --channel=social \
  --title="5 sinais de que sua empresa precisa de automação" \
  --context="Carrossel para Instagram da Lymity IA" \
  --slides=5
```

## Validar geração
```bash
php artisan ai-images:validate {generation_id}
```

## Como usar no Instagram (Social Post)
1. Vá em Posts Sociais → editar um post
2. Na seção "Imagem do Post", escolha:
   - ✨ Gerar com Gemini (existente, direto do post)
   - 🖼 Biblioteca IA: selecione uma geração já completa
   - 📁 Upload manual (preservado)
   - 🔗 URL externa (preservado)
3. Geração não publica o post — aprovação obrigatória continua

## Como usar no Blog
1. Vá em Blog → editar um artigo
2. No painel lateral, seção "Imagem de Capa":
   - Cole uma URL manualmente
   - Selecione da biblioteca IA (gerações de blog/general completas)
   - Clique "+ Gerar nova" para ir ao AI Image Studio
3. Vincular pela biblioteca aplica via POST /attach/blog/{id}

## Upload manual preservado
- POST /admin/social/posts/{id}/replace-image-upload (rota existente, não alterada)

## URL externa preservada
- POST /admin/social/posts/{id}/replace-image-url (rota existente, não alterada)

## Visualizar consumo/logs
- /admin/ai-images/logs — histórico de gerações com filtros

## Limitações do plano free
- Modelos Imagen (imagen-3.0, imagen-4.0) requerem plano pago
- Gemini image models (gemini-2.5-flash-image) podem não estar disponíveis no servidor por restrições geográficas
- Se falhar, use upload manual ou URL externa enquanto aguarda disponibilidade

## Erros comuns

| Erro | Solução |
|------|---------|
| GEMINI_API_KEY não configurado | Configurar no .env + optimize:clear |
| GEMINI_IMAGE_ENABLED=false | Setar true no .env |
| Geração falhou: paid plan | Fazer upgrade no Google AI Studio |
| URL pública inacessível | Checar storage:link + permissões de pasta |
| Rate limit / quota | Aguardar alguns minutos |

## Regressão
Funcionalidades existentes NÃO foram quebradas:
- Social posts: upload manual, URL externa, geração Gemini existente
- Blog: publicação, pipeline, aprovações
- Instagram: OAuth, publicação via Graph API
- Rotinas dos agentes
- Aprovações
