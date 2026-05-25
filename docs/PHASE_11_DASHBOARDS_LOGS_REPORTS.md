# Phase 11 — Dashboards, Logs & Reports

**Status:** Complete  
**Date:** 2026-05-25  
**Branch:** main

---

## Scope

Phase 11 delivered a complete observability and reporting layer across the Lymity IA platform:

- **Admin Executive Dashboard** — 12 KPI cards with quick-access grids
- **Advanced Activity Logs** — filterable table with CSV export (BOM-prefixed, UTF-8)
- **Security Logs** — filtered view for warning/error/critical events
- **Admin Reports** — 7 report pages (index, social, campaigns, SEO, AI, approvals, executive)
- **Client Reports** — 6 report pages (index, social, campaigns, SEO, approvals, executive)
- **ActivityLogService** — service layer with level-typed log helpers
- **ActivityLog model** — extended with `level` enum, `ai_employee_id`, polymorphic `subject`
- **ExecutiveReportService** — centralized data aggregation for all report pages
- **ActivityLogSeeder** — 15 demo logs spanning all levels and modules
- **Sidebar** — updated with Relatórios/Logs section for admin; Relatórios section for client

---

## Migration

**File:** `database/migrations/2026_05_25_110001_add_level_and_subject_to_activity_logs.php`

Adds to `activity_logs`:
| Column | Type | Default |
|--------|------|---------|
| `level` | enum('info','success','warning','error','critical') | 'info' |
| `ai_employee_id` | FK → ai_employees (nullable) | null |
| `subject_type` | string (nullable) | null |
| `subject_id` | unsignedBigInteger (nullable) | null |

All columns guarded with `Schema::hasColumn()` to be idempotent.

---

## Models

### ActivityLog (`app/Models/ActivityLog.php`)
- `$fillable` updated to include `level`, `ai_employee_id`, `subject_type`, `subject_id`
- `aiEmployee()` BelongsTo → AiEmployee
- `subject()` MorphTo (polymorphic)
- `getLevelColorAttribute()` — CSS text color class for level
- `getLevelBadgeAttribute()` — CSS badge class for level
- `record(string $action, ...)` — static shorthand, defaults level='info'

---

## Services

### ActivityLogService (`app/Services/Logs/ActivityLogService.php`)
```php
$svc->log(array $data): ActivityLog          // raw create
$svc->info($action, $description, $data)     // level=info
$svc->success($action, $description, $data)  // level=success
$svc->warning($action, $description, $data)  // level=warning
$svc->error($action, $description, $data)    // level=error
$svc->critical($action, $description, $data) // level=critical
$svc->logSubject($action, Model $subject, ...) // polymorphic subject
```

### ExecutiveReportService (`app/Services/Reports/ExecutiveReportService.php`)
Returns structured arrays consumed by all report controllers.

| Method | Scope |
|--------|-------|
| `adminSummary()` | All metrics platform-wide |
| `clientSummary(Client)` | Scoped to specific client |
| `socialReport(?Client)` | Social posts stats + CSS bar data |
| `campaignReport(?Client)` | Campaign stats + platform breakdown |
| `seoReport(?Client)` | Keyword/cluster/blog stats + priority bars |
| `aiReport()` | AI tasks + employee breakdown (admin only) |
| `approvalReport(?Client)` | Approval funnel stats + type breakdown |

---

## Controllers

### Admin
| Class | Route | View |
|-------|-------|------|
| `AdminDashboardController` | `GET /admin/dashboard` | `admin/dashboard.blade.php` |
| `ActivityLogController@index` | `GET /admin/activity-logs` | `admin/activity-logs/index.blade.php` |
| `ActivityLogController@export` | `GET /admin/activity-logs/export` | — (CSV download) |
| `SecurityLogController@index` | `GET /admin/security-logs` | `admin/security-logs.blade.php` |
| `Admin\ReportController@index` | `GET /admin/reports` | `admin/reports/index.blade.php` |
| `Admin\ReportController@social` | `GET /admin/reports/social` | `admin/reports/social.blade.php` |
| `Admin\ReportController@campaigns` | `GET /admin/reports/campaigns` | `admin/reports/campaigns.blade.php` |
| `Admin\ReportController@seo` | `GET /admin/reports/seo` | `admin/reports/seo.blade.php` |
| `Admin\ReportController@ai` | `GET /admin/reports/ai` | `admin/reports/ai.blade.php` |
| `Admin\ReportController@approvals` | `GET /admin/reports/approvals` | `admin/reports/approvals.blade.php` |
| `Admin\ReportController@executive` | `GET /admin/reports/executive` | `admin/reports/executive.blade.php` |

### Client
| Class | Route | View |
|-------|-------|------|
| `Client\ReportController@index` | `GET /client/reports` | `client/reports/index.blade.php` |
| `Client\ReportController@social` | `GET /client/reports/social` | `client/reports/social.blade.php` |
| `Client\ReportController@campaigns` | `GET /client/reports/campaigns` | `client/reports/campaigns.blade.php` |
| `Client\ReportController@seo` | `GET /client/reports/seo` | `client/reports/seo.blade.php` |
| `Client\ReportController@approvals` | `GET /client/reports/approvals` | `client/reports/approvals.blade.php` |
| `Client\ReportController@executive` | `GET /client/reports/executive` | `client/reports/executive.blade.php` |

**Client isolation:** `Client\ReportController::getClient()` returns `null` for `admin_geral` (sees all), otherwise returns `auth()->user()->client` for scoped data.

---

## Views

### Admin Views
- `admin/dashboard.blade.php` — 12 stat cards, approvals + logs grids, AI tasks grid, quick links
- `admin/activity-logs/index.blade.php` — 9 filter fields, paginated table (8 cols), CSV export button
- `admin/security-logs.blade.php` — warning/error/critical filtered table, critical approvals section
- `admin/reports/index.blade.php` — 4 stat cards, 6 navigation cards, log links
- `admin/reports/social.blade.php` — stats + CSS bar chart (platform breakdown) + table
- `admin/reports/campaigns.blade.php` — status + metric cards + platform CSS bars + table
- `admin/reports/seo.blade.php` — keyword stats + priority CSS bars + keywords table
- `admin/reports/ai.blade.php` — 8 stats + task type bars + recent/error grids
- `admin/reports/approvals.blade.php` — 7 stats + type/level bars + recent table
- `admin/reports/executive.blade.php` — full executive summary (all KPIs consolidated)

### Client Views
- `client/reports/index.blade.php` — 4 KPI cards + 5 navigation cards
- `client/reports/social.blade.php` — social stats + table
- `client/reports/campaigns.blade.php` — campaign stats + table
- `client/reports/seo.blade.php` — SEO stats + keywords table
- `client/reports/approvals.blade.php` — approval funnel + history table
- `client/reports/executive.blade.php` — executive summary for client

---

## CSV Export

`GET /admin/activity-logs/export`

- UTF-8 BOM prefix (`\xEF\xBB\xBF`) for Excel compatibility
- Semicolon-delimited
- Columns: ID, Data, Usuário, Cliente, Módulo, Ação, Nível, Descrição
- Applies same 9 filters as the index page (module, level, action, user_id, client_id, date range)
- Response headers: `Content-Disposition: attachment; filename="activity-logs-{date}.csv"`

---

## Sidebar Updates

**Admin sidebar** (`Sistema` section replaced with `Relatórios` + `Sistema`):
- Dashboard Executivo → `/admin/dashboard`
- Relatórios → `/admin/reports`
- Logs de Atividade → `/admin/activity-logs`
- Logs de Segurança → `/admin/security-logs`
- Configurações → `/admin/settings`
- (Removed the "Logs de Auditoria — Em breve" placeholder)

**Client sidebar** (new `Relatórios` section added):
- Relatórios → `/client/reports`

**Topbar phase badge:** Updated to "Fase 11 — Relatórios & Logs"

---

## CSS Badge Classes (`resources/css/app.css`)

20+ semantic badge classes covering all status/level values:

| Values | Style |
|--------|-------|
| `pending`, `queued` | Yellow |
| `approved`, `active`, `published`, `done`, `signed`, `success`, `accepted` | Green |
| `rejected`, `failed`, `error`, `critical`, `canceled` | Red |
| `draft`, `info` | Gray |
| `sent`, `running`, `changes_requested`, `pending_approval` | Blue |
| `paused`, `archived`, `warning` | Orange/Amber |
| `high` | Red |
| `medium` | Orange |
| `low` | Gray |

---

## Seeder

**`ActivityLogSeeder`** — 15 demo logs:
- `post.created` — info, social_media
- `approval.created` — info, approvals
- `approval.approved` — success, approvals
- `ai_task.executed` — success, ai
- `campaign.created` — success, campaigns
- `budget.created` — success, commercial
- `proposal.accepted` — success, commercial
- `ai_task.limit_reached` — warning, ai
- `approval.pending_timeout` — warning, approvals
- `ai_task.failed` — error, ai
- `security.login_failed_multiple` — critical, security
- `keyword.created` — info, seo
- `contract.signed` — success, commercial
- `blog_post.published` — success, seo
- `user.login` — info, auth

---

## Test Results

| Check | Result |
|-------|--------|
| PHP 8.3.31 | ✓ |
| Composer 2.9.8 | ✓ |
| Laravel 13.11.2 | ✓ |
| optimize:clear | ✓ |
| migrate | Nothing to migrate (already ran) |
| ActivityLogSeeder | ✓ 15 logs created |
| ActivityLogService (info/warning/critical) | ✓ |
| ExecutiveReportService adminSummary | ✓ 20 keys returned |
| ExecutiveReportService clientSummary | ✓ |
| ExecutiveReportService socialReport/approvalReport | ✓ |
| All 17 Phase 11 routes registered | ✓ |
| All Phase 11 routes return 302 (unauthenticated) | ✓ |
| Regression: all prior phase routes | ✓ 0 regressions |

---

## Phase Constraints Respected

- No Sanctum installed
- No force push
- No deletion of prior routes or views
- No hardcoded credentials or tokens
- No heavy charting libraries (pure CSS bars)
- No heavy PDF libraries (CSV only)
- Client isolation via `getClient()` helper
- All sensitive actions use ApprovalService / existing approval flow
- `ActivityLogService` logs important events
