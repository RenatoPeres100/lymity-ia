# Fase 2 — Site Institucional Premium & Blog

## Resumo

Implementação do site institucional público da Lymity AI Agency, blog com posts completos, cases de resultado, formulário de contato com captura de leads, estrutura completa dos 8 Funcionários IA e todos os CRUDs administrativos necessários.

---

## O que foi criado

### Modelos (9 novos)

| Modelo | Tabela | Finalidade |
|---|---|---|
| `BlogCategory` | `blog_categories` | Categorias do blog com ícone |
| `BlogPost` | `blog_posts` | Posts do blog com status de aprovação |
| `CaseStudy` | `case_studies` | Cases de resultado de clientes |
| `Lead` | `leads` | Leads capturados pelo formulário de contato |
| `AiEmployee` | `ai_employees` | Funcionários IA especializados |
| `AiSkill` | `ai_skills` | Habilidades dos agentes IA |
| `AiTask` | `ai_tasks` | Tarefas executadas pelos agentes |
| `AiTaskLog` | `ai_task_logs` | Logs de execução das tarefas |
| `AiApproval` | `ai_approvals` | Registro de aprovações humanas |

### Migrações (7 novas)

- `2025_05_22_100001_create_blog_categories_table` — Categorias do blog (name, slug, icon, description)
- `2025_05_22_100002_create_blog_posts_table` — Posts (title, slug, content, category_id, status enum, tags json, is_featured, seo_description, published_at)
- `2025_05_22_100003_create_case_studies_table` — Cases (title, client_name, industry, challenge, solution, results json, testimonial, tags json, published_at)
- `2025_05_22_100004_create_leads_table` — Leads (name, email, phone, company, message, service_interest, status enum, notes, utm_*, ip_address)
- `2025_05_22_100005_create_ai_employees_table` — AI Employees (name, role, description, avatar_emoji, skills json, routines json, status, approval_required, can_publish, can_send_messages, can_manage_ads_budget)
- `2025_05_22_100006_create_ai_skills_table` — Skills + pivot ai_employee_skill
- `2025_05_22_100007_create_ai_tasks_table` — Tasks + ai_task_logs + ai_approvals

### Seeders (6 novos)

| Seeder | Dados |
|---|---|
| `AiSkillSeeder` | 24 habilidades cobrindo todas as disciplinas digitais |
| `AiEmployeeSeeder` | 8 agentes especializados (Social Media, Copywriter, SEO, Tráfego, Analytics, Automação, CRM, Dev) |
| `BlogCategorySeeder` | 6 categorias com ícone emoji |
| `BlogPostSeeder` | 3 posts completos com conteúdo real (IA, tráfego, automação) |
| `CaseStudySeeder` | 2 cases com métricas JSON, depoimentos e tags |
| `LeadSeeder` | 2 leads demo |

### Controladores (8 novos)

**Público:**
- `PublicSiteController` — home, sobre, servicos, plataforma, funcionariosIa, cases
- `BlogController` — index (paginado), show (com posts relacionados)
- `ContactController` — show, store (com StoreLeadRequest)

**Admin:**
- `Admin\BlogPostController` — CRUD completo com auto-slug e parse de tags
- `Admin\BlogCategoryController` — CRUD com auto-slug e ícone
- `Admin\CaseStudyController` — CRUD com parse de results_raw → JSON
- `Admin\LeadController` — index, show, update (status + notes), destroy
- `Admin\AiEmployeeController` — index, show (somente leitura)

### Views públicas (7 novas/reescritas)

| View | URL |
|---|---|
| `site/home.blade.php` | `/` |
| `site/sobre.blade.php` | `/sobre` |
| `site/servicos.blade.php` | `/servicos` |
| `site/plataforma.blade.php` | `/plataforma` |
| `site/funcionarios-ia.blade.php` | `/funcionarios-ia` |
| `site/cases.blade.php` | `/cases` |
| `site/blog.blade.php` | `/blog` |
| `site/blog-post.blade.php` | `/blog/{slug}` |
| `site/contato.blade.php` | `/contato` |

### Views admin (13 novas)

```
admin/blog/posts/index.blade.php
admin/blog/posts/create.blade.php
admin/blog/posts/edit.blade.php
admin/blog/categories/index.blade.php
admin/blog/categories/create.blade.php
admin/blog/categories/edit.blade.php
admin/cases/index.blade.php
admin/cases/create.blade.php
admin/cases/edit.blade.php
admin/leads/index.blade.php
admin/leads/show.blade.php
admin/ai-employees/index.blade.php
admin/ai-employees/show.blade.php
```

### Layout público

`resources/views/components/layouts/public.blade.php`
- Fixed nav com scroll effect e efeito blur
- Mobile hamburger menu
- Footer com links por categoria
- Utilitários CSS: `.section`, `.container`, `.section-label`, `.section-title`, `.section-subtitle`, `.pub-card`, `.pub-card-dark`, `.pub-btn-primary`, `.pub-btn-outline`, `.pub-btn-outline-light`

---

## Segurança dos Funcionários IA

Todos os Funcionários IA são criados com os seguintes padrões de segurança:

```
approval_required     = true   // Toda ação sensível exige aprovação
can_publish           = false  // Não podem publicar sem aprovação
can_send_messages     = false  // Não podem enviar mensagens sem aprovação
can_manage_ads_budget = false  // Não podem alterar orçamento
```

---

## Status dos Blog Posts

O campo `status` segue o fluxo: `draft` → `pending_approval` → `approved` → `published` → `archived`

- Posts publicados publicamente devem ter `status = published` e `published_at <= now()`
- Posts em aprovação ficam visíveis apenas no admin
- O número na stat card "Posts plan." mostra posts `pending_approval`

---

## Rotas registradas

### Públicas
```
GET /                 → home
GET /sobre            → sobre
GET /servicos         → servicos
GET /plataforma       → plataforma
GET /funcionarios-ia  → funcionarios-ia
GET /cases            → cases
GET /blog             → blog index (paginado, 9/página)
GET /blog/{slug}      → blog post individual
GET /contato          → contact form
POST /contato         → contact form submit → Lead
```

### Admin (auth + active + agency)
```
GET/POST  /admin/blog-posts
GET/PUT/DELETE /admin/blog-posts/{blogPost}
GET/POST  /admin/blog-categories
GET/PUT/DELETE /admin/blog-categories/{blogCategory}
GET/POST  /admin/cases
GET/PUT/DELETE /admin/cases/{caseStudy}
GET       /admin/leads
GET/PUT/DELETE /admin/leads/{lead}
GET       /admin/ai-employees
GET       /admin/ai-employees/{aiEmployee}
```

---

## Checklist de verificação

- [x] `php artisan migrate:fresh --seed` — OK
- [x] `npm run build` — OK (57KB CSS compilado)
- [x] GET / → 200
- [x] GET /sobre → 200
- [x] GET /servicos → 200
- [x] GET /plataforma → 200
- [x] GET /funcionarios-ia → 200
- [x] GET /cases → 200
- [x] GET /blog → 200
- [x] GET /blog/{slug} → 200
- [x] GET /contato → 200
- [x] POST /contato → 419 (CSRF protection working)
- [x] GET /admin/blog-posts → 200
- [x] GET /admin/blog-categories → 200
- [x] GET /admin/cases → 200
- [x] GET /admin/leads → 200
- [x] GET /admin/ai-employees → 200
- [x] BlogPost::count() = 3
- [x] BlogCategory::count() = 6
- [x] CaseStudy::count() = 2
- [x] Lead::count() = 2
- [x] AiEmployee::count() = 8

---

## Próxima fase

**Fase 3** — Integrações com API Claude, tarefas IA, painel de aprovações, integrações Google Ads / Meta Ads, relatórios, sistema de notificações.
