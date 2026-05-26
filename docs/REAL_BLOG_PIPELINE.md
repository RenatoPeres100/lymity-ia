# Real Phase 2 — Pipeline Operacional do Blog da Agência

## Objetivo

Tornar o blog da agência Lymity completamente operacional com:
- Criação de posts manuais e via Blog Writer IA
- Fluxo de aprovação obrigatório antes da publicação
- Agendamento de publicação com scheduler automático
- Publicação imediata pelo admin
- Logs de todas as ações
- Bloqueio de geração IA quando provider não está configurado

---

## Fluxo Operacional

```
Blog Writer IA ou Admin cria artigo
        ↓
   status = draft
        ↓
Admin envia para aprovação
        ↓
   status = pending_approval
   ApprovalRequest criada (approval_type = blog)
        ↓
Admin aprova (approved_by, approved_at preenchidos)
        ↓
   status = approved
        ↓
Admin agenda (scheduled_at obrigatório e futuro)  ──OR──  Admin publica agora
        ↓                                                        ↓
   status = scheduled                                    status = publishing
        ↓                                                        ↓
Scheduler: blog:publish-due (a cada minuto)             status = published
        ↓                                               published_at = now()
   status = publishing → published
   published_at preenchido
        ↓
Post aparece em /blog/{slug} publicamente
        ↓
Log registrado em cada etapa
```

---

## Statuses do BlogPost

| Status           | Descrição                                               |
|------------------|---------------------------------------------------------|
| `draft`          | Rascunho — criado pelo admin ou IA                      |
| `pending_approval` | Aguardando aprovação humana                           |
| `approved`       | Aprovado — pode ser agendado ou publicado agora         |
| `scheduled`      | Agendado — aguarda scheduler para publicar              |
| `publishing`     | Sendo publicado (estado transitório)                    |
| `published`      | Publicado — visível em /blog/{slug}                     |
| `failed`         | Falha na publicação — publication_error preenchido      |
| `rejected`       | Reprovado pelo admin                                    |
| `archived`       | Arquivado — não visível publicamente                    |

---

## BlogPipelineService

Arquivo: `app/Services/Blog/BlogPipelineService.php`

| Método               | Descrição                                                      |
|----------------------|----------------------------------------------------------------|
| `createManualDraft`  | Cria rascunho manual com type=agency, author=user logado       |
| `createDraftFromAi`  | Gera via Blog Writer IA (bloqueia se provider=mock)            |
| `submitForApproval`  | draft/rejected → pending_approval + cria ApprovalRequest       |
| `approve`            | pending_approval/draft → approved + approved_by/approved_at    |
| `reject`             | → rejected + log com motivo                                    |
| `schedule`           | approved → scheduled + scheduled_at (obrigatório futuro)       |
| `publish`            | approved/scheduled → published + validações                    |
| `publishNow`         | approved → published imediatamente                             |
| `failPublication`    | → failed + publication_error preenchido                        |
| `registerLog`        | Registra ActivityLog com subject_type=BlogPost                 |

---

## PublishScheduledBlogPostJob

Arquivo: `app/Jobs/PublishScheduledBlogPostJob.php`

- Recebe `blog_post_id`
- Verifica se post existe e pode ser publicado
- Chama `BlogPipelineService->publish($post)`
- Em caso de falha: chama `failPublication()`
- tries=3, timeout=60s

---

## Command: blog:publish-due

Arquivo: `app/Console/Commands/PublishDueBlogPostsCommand.php`

```bash
php artisan blog:publish-due
```

- Busca `BlogPost::agency()->dueForPublishing()` (status=scheduled + scheduled_at <= now())
- Para cada post: verifica `canBePublished()` — nunca publica sem aprovação
- Se queue=sync: publica direto via service
- Se queue=redis: despacha `PublishScheduledBlogPostJob`
- Registra quantidade publicada/falhou

---

## Scheduler

Arquivo: `routes/console.php`

```php
Schedule::command('blog:publish-due')->everyMinute()->withoutOverlapping();
```

Rodando via cron (www-data):
```
* * * * * /usr/bin/php8.3 /var/www/lymity-ia/artisan schedule:run >> /dev/null 2>&1
```

---

## Approval Flow

1. Admin chama `submitForApproval()` — cria `ApprovalRequest` (approval_type=`blog`, approvable_type=`BlogPost`)
2. Admin vê a aprovação em `/admin/approvals`
3. Admin chama `approve()` — preenche `approved_by`, `approved_at` e atualiza ApprovalRequest
4. Post pode ser agendado ou publicado imediatamente

---

## Bloqueio sem Provider Real

Se `AI_PROVIDER=mock` ou `AI_REAL_ENABLED=false`:
- `/admin/blog/pipeline/generate-ai` mostra alerta claro
- Form de geração fica desabilitado
- Nenhum conteúdo mock é gerado
- Mensagem: "Provider de IA não configurado. Configure um provedor real..."
- Redirecionamento para criação manual

Para habilitar:
```env
AI_PROVIDER=openai
AI_API_KEY=sk-...
AI_REAL_ENABLED=true
```

---

## Publicação Pública

- `/blog` lista apenas `type=agency` + `status=published`
- `/blog/{slug}` retorna 404 para draft/scheduled/approved/pending
- SEO: usa `seo_title` e `seo_description` quando preenchidos

---

## Como testar publicação agendada

```bash
# 1. Criar rascunho
php artisan tinker
$admin = App\Models\User::where('email','admin@lymity.local')->first();
$svc = app(App\Services\Blog\BlogPipelineService::class);
$post = $svc->createManualDraft([...], $admin);

# 2. Aprovar
$svc->approve($post, $admin);

# 3. Agendar (futuro válido)
$svc->schedule($post, now()->addMinutes(2), $admin);

# 4. Simular tempo passado (só para teste)
$post->update(['scheduled_at' => now()->subMinute()]);

# 5. Publicar manualmente ou aguardar scheduler
php artisan blog:publish-due

# 6. Verificar
$post->refresh(); echo $post->status; // published
```

---

## Como publicar agora

```bash
php artisan tinker
$admin = App\Models\User::where('email','admin@lymity.local')->first();
$post = App\Models\BlogPost::find($id);
$svc = app(App\Services\Blog\BlogPipelineService::class);
$svc->approve($post, $admin);   // se ainda não aprovado
$svc->publishNow($post, $admin);
```

---

## Logs

Todos os eventos são registrados em `activity_logs`:
- `subject_type = App\Models\BlogPost`
- `subject_id = post.id`
- `module = blog`
- Ações: `draft_created`, `submitted_for_approval`, `approved`, `rejected`, `scheduled`, `published`, `publication_failed`, `archived`, `edited`, `ai_generation_requested`

Ver logs de um post: `/admin/blog/posts/{id}/logs`

---

## Rotas

| Rota                                          | Nome                              |
|-----------------------------------------------|-----------------------------------|
| GET /admin/blog/pipeline                      | admin.blog.pipeline.index         |
| GET /admin/blog/pipeline/generate-ai          | admin.blog.pipeline.generate-ai   |
| POST /admin/blog/pipeline/generate-ai         | admin.blog.pipeline.generate-ai.store |
| GET /admin/blog/posts/create-pipeline         | admin.blog.posts.create           |
| POST /admin/blog/posts                        | admin.blog.posts.store            |
| GET /admin/blog/posts/{id}                    | admin.blog.posts.show             |
| GET /admin/blog/posts/{id}/edit               | admin.blog.posts.edit             |
| PUT /admin/blog/posts/{id}                    | admin.blog.posts.update           |
| POST /admin/blog/posts/{id}/submit-approval   | admin.blog.posts.submit-approval  |
| POST /admin/blog/posts/{id}/approve           | admin.blog.posts.approve          |
| POST /admin/blog/posts/{id}/reject            | admin.blog.posts.reject           |
| POST /admin/blog/posts/{id}/schedule          | admin.blog.posts.schedule         |
| POST /admin/blog/posts/{id}/publish-now       | admin.blog.posts.publish-now      |
| POST /admin/blog/posts/{id}/archive           | admin.blog.posts.archive          |
| GET /admin/blog/posts/{id}/logs               | admin.blog.posts.logs             |

---

## Próximos Passos (Phase Real 3)

1. Configurar AI_PROVIDER real para geração via Blog Writer IA
2. Upload de imagem destacada (featured_image) via storage
3. Sitemap automático para posts publicados
4. Preview público antes de publicar
5. Tags e categorias no pipeline
6. Notificação por e-mail ao aprovar/reprovar
7. Pipeline de Instagram com o mesmo fluxo
