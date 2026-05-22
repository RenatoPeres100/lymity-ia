# Fase 3 — Clientes, Websites e Branding

## Visão geral

A Fase 3 transforma cada registro de cliente em uma base operacional completa, com perfil de marca, website, páginas, assets, base de conhecimento e blog próprio. Inclui workflow de aprovação, logs de atividade e área self-service para o cliente.

---

## Modelos criados

### ActivityLog
- Tabela: `activity_logs`
- Registra toda ação sensível no sistema
- Método estático: `ActivityLog::record(action, module, description, clientId, metadata)`
- Captura automaticamente: `auth()->id()`, IP, user agent

### ClientBrandProfile
- Tabela: `client_brand_profiles`
- Um-para-um com Client (unique `client_id`)
- Campos: `tone_of_voice`, `target_audience`, `main_offer`, `objections`, `competitors`, `visual_style`, `forbidden_terms`, `preferred_terms`, `cta_examples`, `notes`

### ClientWebsite
- Tabela: `client_websites`
- Muitos para um com Client
- Campos: `domain`, `platform` (internal/wordpress/external), `status` (planning/active/paused/archived), `primary_color`, `secondary_color`, `logo_url`, `notes`

### ClientWebsitePage
- Tabela: `client_website_pages`
- Muitos para um com ClientWebsite (cascade delete)
- Campos: `title`, `slug` (unique por website), `page_type` (home/about/services/contact/blog/landing/other), `content`, `seo_title`, `seo_description`, `status` (draft/pending_approval/approved/published/archived), `created_by`, `approved_by`, `published_at`

### ClientAsset
- Tabela: `client_assets`
- Muitos para um com Client
- Campos: `name`, `type` (image/video/document/logo/brand_file/other), `source` (upload/google_drive/generated/external), `path`, `external_url`, `notes`
- Accessor `url`: prioriza `external_url`, fallback para `storage/{path}`

### ClientKnowledgeBase
- Tabela: `client_knowledge_bases`
- Muitos para um com Client
- Campos: `title`, `content` (longText), `source`, `status` (active/draft/archived)

---

## Rotas

### Admin — `/admin/clients/{client}/...`

| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| GET | `/admin/clients/{client}/brand` | `admin.clients.brand.show` | Ver/editar perfil de marca |
| POST | `/admin/clients/{client}/brand` | `admin.clients.brand.store` | Criar perfil de marca |
| PUT | `/admin/clients/{client}/brand` | `admin.clients.brand.update` | Atualizar perfil de marca |
| GET | `/admin/clients/{client}/website` | `admin.clients.website.show` | Ver/editar website |
| POST | `/admin/clients/{client}/website` | `admin.clients.website.store` | Criar website |
| PUT | `/admin/clients/{client}/website/{website}` | `admin.clients.website.update` | Atualizar website |
| GET | `/admin/clients/{client}/pages` | `admin.clients.pages.index` | Listar páginas |
| GET | `/admin/clients/{client}/pages/create` | `admin.clients.pages.create` | Formulário nova página |
| POST | `/admin/clients/{client}/pages` | `admin.clients.pages.store` | Criar página |
| GET | `/admin/clients/{client}/pages/{page}/edit` | `admin.clients.pages.edit` | Editar página |
| PUT | `/admin/clients/{client}/pages/{page}` | `admin.clients.pages.update` | Atualizar página |
| DELETE | `/admin/clients/{client}/pages/{page}` | `admin.clients.pages.destroy` | Excluir página |
| POST | `/admin/clients/{client}/pages/{page}/approve` | `admin.clients.pages.approve` | Aprovar página |
| POST | `/admin/clients/{client}/pages/{page}/reject` | `admin.clients.pages.reject` | Reprovar página |
| POST | `/admin/clients/{client}/pages/{page}/publish` | `admin.clients.pages.publish` | Publicar página |
| GET | `/admin/clients/{client}/assets` | `admin.clients.assets.index` | Listar assets |
| GET | `/admin/clients/{client}/assets/create` | `admin.clients.assets.create` | Formulário novo asset |
| POST | `/admin/clients/{client}/assets` | `admin.clients.assets.store` | Criar asset |
| DELETE | `/admin/clients/{client}/assets/{asset}` | `admin.clients.assets.destroy` | Excluir asset |
| GET | `/admin/clients/{client}/knowledge-base` | `admin.clients.knowledge-base.index` | Base de conhecimento |
| GET | `/admin/clients/{client}/knowledge-base/create` | `admin.clients.knowledge-base.create` | Novo item |
| POST | `/admin/clients/{client}/knowledge-base` | `admin.clients.knowledge-base.store` | Criar item |
| GET | `/admin/clients/{client}/knowledge-base/{entry}/edit` | `admin.clients.knowledge-base.edit` | Editar item |
| PUT | `/admin/clients/{client}/knowledge-base/{entry}` | `admin.clients.knowledge-base.update` | Atualizar item |
| DELETE | `/admin/clients/{client}/knowledge-base/{entry}` | `admin.clients.knowledge-base.destroy` | Excluir item |
| GET | `/admin/clients/{client}/blog` | `admin.clients.blog.index` | Blog do cliente |
| GET | `/admin/clients/{client}/blog/create` | `admin.clients.blog.create` | Novo post |
| POST | `/admin/clients/{client}/blog` | `admin.clients.blog.store` | Criar post |
| GET | `/admin/clients/{client}/blog/{post}/edit` | `admin.clients.blog.edit` | Editar post |
| PUT | `/admin/clients/{client}/blog/{post}` | `admin.clients.blog.update` | Atualizar post |
| DELETE | `/admin/clients/{client}/blog/{post}` | `admin.clients.blog.destroy` | Excluir post |
| POST | `/admin/clients/{client}/blog/{post}/approve` | `admin.clients.blog.approve` | Aprovar post |
| POST | `/admin/clients/{client}/blog/{post}/reject` | `admin.clients.blog.reject` | Reprovar post |
| POST | `/admin/clients/{client}/blog/{post}/publish` | `admin.clients.blog.publish` | Publicar post |
| GET | `/admin/clients/{client}/logs` | `admin.clients.logs.index` | Logs de atividade |

### Área do Cliente — `/client/...`

| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| GET | `/client/brand` | `client.brand` | Ver perfil de marca |
| GET | `/client/pages` | `client.pages.index` | Listar páginas |
| GET | `/client/pages/{page}` | `client.pages.show` | Ver página |
| POST | `/client/pages/{page}/approve` | `client.pages.approve` | Aprovar página |
| POST | `/client/pages/{page}/reject` | `client.pages.reject` | Reprovar página |
| GET | `/client/blog` | `client.blog.index` | Blog do cliente |
| GET | `/client/blog/{post}` | `client.blog.show` | Ver post |
| POST | `/client/blog/{post}/approve` | `client.blog.approve` | Aprovar post |
| POST | `/client/blog/{post}/reject` | `client.blog.reject` | Reprovar post |

---

## Workflow de Aprovação

```
draft → pending_approval → approved → published
                ↓ (reject)
              draft
```

- **Aprovar página/post**: status → `approved`, `approved_by` = auth user
- **Reprovar**: status → `draft`, `approved_by` → null
- **Publicar**: status → `published`, `published_at` = now()
- Apenas `pending_approval` pode ser aprovado/reprovado
- Apenas `approved` pode ser publicado

---

## Separação de Blog

- Posts da **agência** usam `type = 'agency'` — exibidos em `/blog` público
- Posts do **cliente** usam `type = 'client'` + `client_id` — nunca misturados com posts da agência
- Escopo `BlogPost::scopeClient($clientId)` garante isolamento

---

## Isolamento do Cliente

- Controllers da área do cliente obtêm o cliente via `auth()->user()->client`
- Nunca aceitam `client_id` da URL
- Verificam posse com `abort_unless(resource->client_id === client->id, 403)`

---

## Seeders

| Seeder | Descrição |
|--------|-----------|
| `DemoClientSeeder` | Cria `Cliente Demonstração` + users `cliente@demo.local` e `cliente@lymity.local` |
| `ClientBrandProfileSeeder` | Perfil de marca completo para clínica de estética demo |
| `ClientWebsiteSeeder` | Website `cliente-demo.local` (platform: internal, status: active) |
| `ClientWebsitePageSeeder` | Página Home (pending_approval) + Serviços (draft) |
| `ClientAssetSeeder` | Logo + Manual de identidade visual |
| `ClientKnowledgeBaseSeeder` | Informações comerciais + FAQ |
| `ClientBlogPostSeeder` | 2 posts de demonstração (1 pending_approval, 1 draft) |

---

## Credenciais de Demo

| Email | Senha | Tipo | Papel |
|-------|-------|------|-------|
| `admin@lymity.local` | `password` | agency | admin_geral |
| `cliente@lymity.local` | `password` | client | cliente_admin |
| `cliente@demo.local` | `password` | client | cliente_admin |

---

## Estrutura de Arquivos

```
app/
  Http/Controllers/
    Admin/
      ClientBrandProfileController.php
      ClientWebsiteController.php
      ClientWebsitePageController.php
      ClientAssetController.php
      ClientKnowledgeBaseController.php
      ClientBlogPostController.php
      ClientActivityLogController.php
    Client/
      BrandController.php
      PageController.php
      BlogController.php
  Models/
    ActivityLog.php
    ClientBrandProfile.php
    ClientWebsite.php
    ClientWebsitePage.php
    ClientAsset.php
    ClientKnowledgeBase.php
  Http/Requests/
    StoreClientBrandProfileRequest.php
    StoreClientWebsiteRequest.php
    StoreClientWebsitePageRequest.php
    StoreClientAssetRequest.php
    StoreClientKnowledgeBaseRequest.php
    StoreClientBlogPostRequest.php
database/
  migrations/
    2025_05_22_200001_create_activity_logs_table.php
    2025_05_22_200002_create_client_brand_profiles_table.php
    2025_05_22_200003_create_client_websites_table.php
    2025_05_22_200004_create_client_website_pages_table.php
    2025_05_22_200005_create_client_assets_table.php
    2025_05_22_200006_create_client_knowledge_bases_table.php
  seeders/
    ClientBrandProfileSeeder.php
    ClientWebsiteSeeder.php
    ClientWebsitePageSeeder.php
    ClientAssetSeeder.php
    ClientKnowledgeBaseSeeder.php
    ClientBlogPostSeeder.php
resources/views/
  admin/clients/
    brand/show.blade.php
    website/show.blade.php
    pages/{index,create,edit}.blade.php
    assets/{index,create}.blade.php
    knowledge-base/{index,create,edit}.blade.php
    blog/{index,create,edit}.blade.php
    logs/index.blade.php
  client/
    brand/show.blade.php
    pages/{index,show}.blade.php
    blog/{index,show}.blade.php
```
