# Phase 7 — SEO & AI Blog Generation

## Overview

Phase 7 introduces a complete SEO and AI-powered blog generation module to the Lymity IA platform.

## Features

### SEO Management
- **Keywords**: Create and track SEO keywords with intent, priority, volume, difficulty and status
- **Clusters**: Organize keywords into thematic clusters for content strategy
- **Content Plans**: Monthly editorial calendars with client association
- **Audits**: SEO technical audits with mock recommendations (AI-powered in production)

### AI Blog Generation
- Generate full blog posts with a single click via the SEO IA employee
- Auto-generated content includes: title, subtitle, excerpt, 8+ paragraph content, SEO title, meta description, focus keyword, secondary keywords
- Full approval workflow integration (pending_approval → approved → published)
- Support for both agency blog and client blog posts

### Client Area
- SEO Dashboard: view keywords, blog posts and audit scores
- Blog view: browse all AI-generated posts
- Approval tracking: see pending blog approvals

## Architecture

### Database Tables
- `seo_keywords` — keyword management
- `seo_clusters` — topical cluster groups
- `seo_content_plans` — monthly content calendars
- `seo_audits` — SEO technical audits
- `seo_recommendations` — audit recommendations

### Models
- `SeoKeyword`, `SeoCluster`, `SeoContentPlan`, `SeoAudit`, `SeoRecommendation`

### Services
- `App\Services\Seo\SeoContentService` — main business logic (create keywords, clusters, plans, generate blog posts, approval workflow)
- `App\Services\Seo\SeoAiService` — creates and runs AI tasks
- `App\Services\Seo\SeoAuditService` — creates audits and generates mock recommendations

### Controllers (Admin)
- `SeoDashboardController` — SEO dashboard with stats
- `SeoKeywordController` — keyword CRUD
- `SeoClusterController` — cluster CRUD
- `SeoContentPlanController` — content plan CRUD + show
- `SeoAuditController` — audit CRUD + generateMock action
- `AdminBlogController` — blog post list (all posts), AI generation form, send to approval, publish

### Controllers (Client)
- `SeoDashboardController` — client SEO overview
- `ClientBlogSeoController` — client blog posts index, show, approvals

## Routes

### Admin Routes
| Method | URL | Name | Action |
|--------|-----|------|--------|
| GET | /admin/seo | admin.seo.index | SEO Dashboard |
| GET | /admin/seo/keywords | admin.seo.keywords.index | List keywords |
| GET | /admin/seo/keywords/create | admin.seo.keywords.create | Create keyword form |
| POST | /admin/seo/keywords | admin.seo.keywords.store | Store keyword |
| GET | /admin/seo/keywords/{id}/edit | admin.seo.keywords.edit | Edit keyword form |
| PUT | /admin/seo/keywords/{id} | admin.seo.keywords.update | Update keyword |
| DELETE | /admin/seo/keywords/{id} | admin.seo.keywords.destroy | Delete keyword |
| GET | /admin/seo/clusters | admin.seo.clusters.index | List clusters |
| ... | ... | ... | ... |
| GET | /admin/seo/audits | admin.seo.audits.index | List audits |
| GET | /admin/seo/audits/{id} | admin.seo.audits.show | Audit detail + recommendations |
| POST | /admin/seo/audits/{id}/generate-mock | admin.seo.audits.generate-mock | Generate mock recommendations |
| GET | /admin/seo/blog | admin.seo.blog.index | All blog posts |
| GET | /admin/seo/blog/generate-ai | admin.seo.blog.generate | AI generation form |
| POST | /admin/seo/blog/generate-ai | admin.seo.blog.generate.store | Generate blog post |
| POST | /admin/seo/blog/{id}/send-approval | admin.seo.blog.send-approval | Send to approval |
| POST | /admin/seo/blog/{id}/publish | admin.seo.blog.publish | Publish post |

### Client Routes
| Method | URL | Name | Action |
|--------|-----|------|--------|
| GET | /client/seo | client.seo.index | Client SEO dashboard |
| GET | /client/seo/blog | client.seo.blog.index | Client blog posts |
| GET | /client/seo/blog/approvals | client.seo.blog.approvals | Blog approvals |
| GET | /client/seo/blog/{id} | client.seo.blog.show | View single post |

## Blog Post Generation Flow

1. Admin opens `/admin/seo/blog/generate-ai`
2. Selects type (agency/client), enters keyword
3. `SeoContentService::generateBlogPost()` called
4. `SeoAiService::createAndRunTask()` creates AiTask with type `generate_blog_post`
5. `AiTaskService::runTask()` calls `MockAiProvider::generate()` which returns JSON
6. JSON parsed to extract title, slug, content, SEO fields
7. `BlogPost` created with `status=pending_approval`
8. `ApprovalService::createApproval()` creates approval request
9. Admin/client reviews in approvals module
10. On approval: `syncContentStatus()` sets `status=approved`
11. Admin publishes: `SeoContentService::publishBlogPost()` sets `status=published`

## Seeders

- `SeoKeywordSeeder` — 5 sample keywords
- `SeoClusterSeeder` — 3 sample clusters
- `SeoContentPlanSeeder` — 3 sample content plans
- `SeoAuditSeeder` — 2 sample audits (one with mock recommendations)

Run: `php artisan db:seed --class=SeoKeywordSeeder`

## MockAiProvider Task Types Added

- `generate_blog_post` — Returns JSON with full blog post content
- `generate_seo_plan` — Returns JSON with monthly content plan
- `generate_keyword_cluster` — Returns JSON with keyword cluster
- `improve_blog_post` — Returns improved post content
- `generate_meta_description` — Returns meta description string
- `generate_seo_audit_mock` — Returns JSON with score and recommendations

## Approval Integration

Phase 7 reuses the existing `ApprovalService` from Phase 5. The `syncContentStatus()` method already handles `BlogPost` (via `syncContentStatus` private method which was added in Phase 5 to handle both `BlogPost` and `ClientWebsitePage`).

Status flow:
- `draft` → send to approval → `pending_approval`
- `pending_approval` → approved → `approved`
- `pending_approval` → rejected → `draft`
- `approved` → publish → `published`
