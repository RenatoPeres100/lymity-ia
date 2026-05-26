<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\ClientFileController as AdminClientFileController;
use App\Http\Controllers\Admin\GoogleDriveAdminController;
use App\Http\Controllers\Client\FileController as ClientFileController;
use App\Http\Controllers\Client\GoogleDriveController as ClientGoogleDriveController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SecurityLogController;
use App\Http\Controllers\Client\ReportController as ClientReportController;
use App\Http\Controllers\App\AppApprovalController;
use App\Http\Controllers\App\AppCalendarController;
use App\Http\Controllers\App\AppDashboardController;
use App\Http\Controllers\App\AppProfileController;
use App\Http\Controllers\App\AppReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\Admin\ApprovalController as AdminApprovalController;
use App\Http\Controllers\Admin\AiEmployeeController;
use App\Http\Controllers\Admin\AiTaskController;
use App\Http\Controllers\Admin\AiTaskLogController;
use App\Http\Controllers\Admin\AiWorkScheduleController;
use App\Http\Controllers\Admin\AiMemoryController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\CaseStudyController;
use App\Http\Controllers\Admin\ClientActivityLogController;
use App\Http\Controllers\Admin\ClientAssetController;
use App\Http\Controllers\Admin\ClientBlogPostController;
use App\Http\Controllers\Admin\ClientBrandProfileController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientKnowledgeBaseController;
use App\Http\Controllers\Admin\ClientWebsiteController;
use App\Http\Controllers\Admin\ClientWebsitePageController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Client\ApprovalController as ClientApprovalController;
use App\Http\Controllers\Client\BlogController as ClientBlogController;
use App\Http\Controllers\Client\BrandController as ClientBrandController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\PageController as ClientPageController;
use App\Http\Controllers\Admin\SocialDashboardController;
use App\Http\Controllers\Admin\SocialPostController as AdminSocialPostController;
use App\Http\Controllers\Admin\SocialCalendarController as AdminSocialCalendarController;
use App\Http\Controllers\Admin\SocialChannelController;
use App\Http\Controllers\Admin\SocialContentBriefController;
use App\Http\Controllers\Admin\SocialAiController;
use App\Http\Controllers\Client\SocialPostController as ClientSocialPostController;
use App\Http\Controllers\Client\SocialCalendarController as ClientSocialCalendarController;
use App\Http\Controllers\Client\SocialApprovalController;
use App\Http\Controllers\Admin\SeoDashboardController;
use App\Http\Controllers\Admin\SeoKeywordController;
use App\Http\Controllers\Admin\SeoClusterController;
use App\Http\Controllers\Admin\SeoContentPlanController;
use App\Http\Controllers\Admin\SeoAuditController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Client\SeoDashboardController as ClientSeoDashboardController;
use App\Http\Controllers\Client\ClientBlogSeoController;
use App\Http\Controllers\Admin\AdsDashboardController;
use App\Http\Controllers\Admin\AdAccountController;
use App\Http\Controllers\Admin\AdCampaignController as AdminAdCampaignController;
use App\Http\Controllers\Admin\CampaignMetricController;
use App\Http\Controllers\Admin\CampaignBudgetChangeController;
use App\Http\Controllers\Admin\AdsAiController;
use App\Http\Controllers\Client\AdsDashboardController as ClientAdsDashboardController;
use App\Http\Controllers\Client\AdCampaignController as ClientAdCampaignController;
use App\Http\Controllers\Client\AdsApprovalController;
use App\Http\Controllers\Client\AdsReportController;
use App\Http\Controllers\Admin\ProposalController as AdminProposalController;
use App\Http\Controllers\Admin\BudgetController as AdminBudgetController;
use App\Http\Controllers\Admin\ClientContractController as AdminContractController;
use App\Http\Controllers\Client\ProposalController as ClientProposalController;
use App\Http\Controllers\Client\BudgetController as ClientBudgetController;
use App\Http\Controllers\Client\ContractController as ClientContractController;
use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\Admin\AiUsageController;
use Illuminate\Support\Facades\Route;

// ─── Public Site ──────────────────────────────────────────
Route::get('/',                 [PublicSiteController::class, 'home'])->name('home');
Route::get('/sobre',            [PublicSiteController::class, 'sobre'])->name('sobre');
Route::get('/servicos',         [PublicSiteController::class, 'servicos'])->name('servicos');
Route::get('/plataforma',       [PublicSiteController::class, 'plataforma'])->name('plataforma');
Route::get('/funcionarios-ia',  [PublicSiteController::class, 'funcionariosIa'])->name('funcionarios-ia');
Route::get('/cases',            [PublicSiteController::class, 'cases'])->name('cases');

// ─── Blog ─────────────────────────────────────────────────
Route::get('/blog',             [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}',      [BlogController::class, 'show'])->name('blog.show');

// ─── Contact ──────────────────────────────────────────────
Route::get('/contato',          [ContactController::class, 'show'])->name('contato');
Route::post('/contato',         [ContactController::class, 'store'])->name('contato.store');

// ─── Auth ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Authenticated ────────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Admin — Agency ───────────────────────────────────
    Route::prefix('admin')->middleware('agency')->group(function () {

        // Phase 12 — System Health
        Route::get('/system-health', SystemHealthController::class)->name('admin.system-health');

        // Phase 13 — Files & Google Drive
        Route::get('/files',                               [AdminFileController::class, 'index'])->name('admin.files.index');
        Route::get('/files/create',                        [AdminFileController::class, 'create'])->name('admin.files.create');
        Route::post('/files',                              [AdminFileController::class, 'store'])->name('admin.files.store');
        Route::get('/files/google-drive/connect',         [GoogleDriveAdminController::class, 'connect'])->name('admin.files.google-drive');
        Route::get('/files/{externalFile}',               [AdminFileController::class, 'show'])->name('admin.files.show');
        Route::delete('/files/{externalFile}',            [AdminFileController::class, 'destroy'])->name('admin.files.destroy');
        Route::post('/files/{externalFile}/relate',       [AdminFileController::class, 'relate'])->name('admin.files.relate');

        Route::get('/clients/{client}/files',                    [AdminClientFileController::class, 'index'])->name('admin.clients.files.index');
        Route::get('/clients/{client}/files/create',             [AdminClientFileController::class, 'create'])->name('admin.clients.files.create');
        Route::post('/clients/{client}/files',                   [AdminClientFileController::class, 'store'])->name('admin.clients.files.store');
        Route::get('/clients/{client}/files/google-drive',       [GoogleDriveAdminController::class, 'connectForClient'])->name('admin.clients.files.google-drive');
        Route::get('/clients/{client}/files/{externalFile}',     [AdminClientFileController::class, 'show'])->name('admin.clients.files.show');
        Route::delete('/clients/{client}/files/{externalFile}',  [AdminClientFileController::class, 'destroy'])->name('admin.clients.files.destroy');
        Route::post('/clients/{client}/folders',                 [AdminClientFileController::class, 'storeFolder'])->name('admin.clients.folders.store');

        // Real Phase 1 — Operation & Publishing Queue
        Route::get('/operation',         [App\Http\Controllers\Admin\OperationController::class, 'index'])->name('admin.operation');
        Route::get('/publishing-queue',  [App\Http\Controllers\Admin\PublishingQueueController::class, 'index'])->name('admin.publishing-queue');
        Route::get('/operation/logs',    fn() => redirect()->route('admin.activity-logs.index'))->name('admin.operation.logs');

        // Phase 11 — Dashboards, Reports, Logs
        Route::get('/dashboard',                    [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/reports',                      [AdminReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/social',               [AdminReportController::class, 'social'])->name('admin.reports.social');
        Route::get('/reports/campaigns',            [AdminReportController::class, 'campaigns'])->name('admin.reports.campaigns');
        Route::get('/reports/seo',                  [AdminReportController::class, 'seo'])->name('admin.reports.seo');
        Route::get('/reports/ai',                   [AdminReportController::class, 'ai'])->name('admin.reports.ai');
        Route::get('/reports/approvals',            [AdminReportController::class, 'approvals'])->name('admin.reports.approvals');
        Route::get('/reports/executive',            [AdminReportController::class, 'executive'])->name('admin.reports.executive');
        Route::get('/activity-logs',                [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/activity-logs/export',         [ActivityLogController::class, 'export'])->name('admin.activity-logs.export');
        Route::get('/security-logs',                [SecurityLogController::class, 'index'])->name('admin.security-logs.index');

        // Users & Clients (Phase 1)
        Route::get('/users',    [UserController::class,   'index'])->name('admin.users');
        Route::get('/clients',  [ClientController::class, 'index'])->name('admin.clients');
        Route::get('/settings', [SettingController::class,'index'])
            ->middleware('admin_geral')
            ->name('admin.settings');

        // Blog Posts (Agency)
        Route::get('/blog-posts',                  [BlogPostController::class, 'index'])->name('admin.blog-posts.index');
        Route::get('/blog-posts/create',           [BlogPostController::class, 'create'])->name('admin.blog-posts.create');
        Route::post('/blog-posts',                 [BlogPostController::class, 'store'])->name('admin.blog-posts.store');
        Route::get('/blog-posts/{blogPost}/edit',  [BlogPostController::class, 'edit'])->name('admin.blog-posts.edit');
        Route::put('/blog-posts/{blogPost}',       [BlogPostController::class, 'update'])->name('admin.blog-posts.update');
        Route::delete('/blog-posts/{blogPost}',    [BlogPostController::class, 'destroy'])->name('admin.blog-posts.destroy');

        // Blog Categories
        Route::get('/blog-categories',                       [BlogCategoryController::class, 'index'])->name('admin.blog-categories.index');
        Route::get('/blog-categories/create',                [BlogCategoryController::class, 'create'])->name('admin.blog-categories.create');
        Route::post('/blog-categories',                      [BlogCategoryController::class, 'store'])->name('admin.blog-categories.store');
        Route::get('/blog-categories/{blogCategory}/edit',   [BlogCategoryController::class, 'edit'])->name('admin.blog-categories.edit');
        Route::put('/blog-categories/{blogCategory}',        [BlogCategoryController::class, 'update'])->name('admin.blog-categories.update');
        Route::delete('/blog-categories/{blogCategory}',     [BlogCategoryController::class, 'destroy'])->name('admin.blog-categories.destroy');

        // Cases
        Route::get('/cases',                   [CaseStudyController::class, 'index'])->name('admin.cases.index');
        Route::get('/cases/create',            [CaseStudyController::class, 'create'])->name('admin.cases.create');
        Route::post('/cases',                  [CaseStudyController::class, 'store'])->name('admin.cases.store');
        Route::get('/cases/{caseStudy}/edit',  [CaseStudyController::class, 'edit'])->name('admin.cases.edit');
        Route::put('/cases/{caseStudy}',       [CaseStudyController::class, 'update'])->name('admin.cases.update');
        Route::delete('/cases/{caseStudy}',    [CaseStudyController::class, 'destroy'])->name('admin.cases.destroy');

        // Leads
        Route::get('/leads',           [LeadController::class, 'index'])->name('admin.leads.index');
        Route::get('/leads/{lead}',    [LeadController::class, 'show'])->name('admin.leads.show');
        Route::put('/leads/{lead}',    [LeadController::class, 'update'])->name('admin.leads.update');
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('admin.leads.destroy');

        // ─── Phase 5 — Approvals ─────────────────────────────────────────
        Route::get('/approvals',                                        [AdminApprovalController::class, 'index'])->name('admin.approvals.index');
        Route::get('/approvals/create',                                 [AdminApprovalController::class, 'create'])->name('admin.approvals.create');
        Route::post('/approvals',                                       [AdminApprovalController::class, 'store'])->name('admin.approvals.store');
        Route::get('/approvals/{approvalRequest}',                      [AdminApprovalController::class, 'show'])->name('admin.approvals.show');
        Route::post('/approvals/{approvalRequest}/approve',             [AdminApprovalController::class, 'approve'])->name('admin.approvals.approve');
        Route::post('/approvals/{approvalRequest}/reject',              [AdminApprovalController::class, 'reject'])->name('admin.approvals.reject');
        Route::post('/approvals/{approvalRequest}/request-changes',     [AdminApprovalController::class, 'requestChanges'])->name('admin.approvals.request-changes');
        Route::post('/approvals/{approvalRequest}/cancel',              [AdminApprovalController::class, 'cancel'])->name('admin.approvals.cancel');
        Route::post('/approvals/{approvalRequest}/comments',            [AdminApprovalController::class, 'storeComment'])->name('admin.approvals.comments');

        // ─── Phase 4 — AI Employees (full CRUD + actions) ───────────────
        Route::get('/ai-employees',                            [AiEmployeeController::class, 'index'])->name('admin.ai-employees.index');
        Route::get('/ai-employees/create',                     [AiEmployeeController::class, 'create'])->name('admin.ai-employees.create');
        Route::post('/ai-employees',                           [AiEmployeeController::class, 'store'])->name('admin.ai-employees.store');
        Route::get('/ai-employees/{aiEmployee}',               [AiEmployeeController::class, 'show'])->name('admin.ai-employees.show');
        Route::get('/ai-employees/{aiEmployee}/edit',          [AiEmployeeController::class, 'edit'])->name('admin.ai-employees.edit');
        Route::put('/ai-employees/{aiEmployee}',               [AiEmployeeController::class, 'update'])->name('admin.ai-employees.update');
        Route::delete('/ai-employees/{aiEmployee}',            [AiEmployeeController::class, 'destroy'])->name('admin.ai-employees.destroy');
        Route::post('/ai-employees/{aiEmployee}/pause',        [AiEmployeeController::class, 'pause'])->name('admin.ai-employees.pause');
        Route::post('/ai-employees/{aiEmployee}/activate',     [AiEmployeeController::class, 'activate'])->name('admin.ai-employees.activate');
        Route::post('/ai-employees/{aiEmployee}/disable',      [AiEmployeeController::class, 'disable'])->name('admin.ai-employees.disable');

        // AI Tasks
        Route::get('/ai-tasks',                          [AiTaskController::class, 'index'])->name('admin.ai-tasks.index');
        Route::get('/ai-tasks/create',                   [AiTaskController::class, 'create'])->name('admin.ai-tasks.create');
        Route::post('/ai-tasks',                         [AiTaskController::class, 'store'])->name('admin.ai-tasks.store');
        Route::get('/ai-tasks/{aiTask}',                 [AiTaskController::class, 'show'])->name('admin.ai-tasks.show');
        Route::post('/ai-tasks/{aiTask}/run',            [AiTaskController::class, 'run'])->name('admin.ai-tasks.run');
        Route::post('/ai-tasks/{aiTask}/approve',        [AiTaskController::class, 'approve'])->name('admin.ai-tasks.approve');
        Route::post('/ai-tasks/{aiTask}/reject',         [AiTaskController::class, 'reject'])->name('admin.ai-tasks.reject');
        Route::post('/ai-tasks/{aiTask}/cancel',         [AiTaskController::class, 'cancel'])->name('admin.ai-tasks.cancel');
        Route::post('/ai-tasks/{aiTask}/feedback',       [AiTaskController::class, 'storeFeedback'])->name('admin.ai-tasks.feedback');

        // AI Logs
        Route::get('/ai-logs', [AiTaskLogController::class, 'index'])->name('admin.ai-logs.index');

        // AI Work Schedules
        Route::get('/ai-schedules',                      [AiWorkScheduleController::class, 'index'])->name('admin.ai-schedules.index');
        Route::get('/ai-schedules/create',               [AiWorkScheduleController::class, 'create'])->name('admin.ai-schedules.create');
        Route::post('/ai-schedules',                     [AiWorkScheduleController::class, 'store'])->name('admin.ai-schedules.store');
        Route::get('/ai-schedules/{aiSchedule}/edit',    [AiWorkScheduleController::class, 'edit'])->name('admin.ai-schedules.edit');
        Route::put('/ai-schedules/{aiSchedule}',         [AiWorkScheduleController::class, 'update'])->name('admin.ai-schedules.update');
        Route::delete('/ai-schedules/{aiSchedule}',      [AiWorkScheduleController::class, 'destroy'])->name('admin.ai-schedules.destroy');
        Route::post('/ai-schedules/{aiSchedule}/toggle', [AiWorkScheduleController::class, 'toggle'])->name('admin.ai-schedules.toggle');

        // AI Memories
        Route::get('/ai-memories',                    [AiMemoryController::class, 'index'])->name('admin.ai-memories.index');
        Route::get('/ai-memories/create',             [AiMemoryController::class, 'create'])->name('admin.ai-memories.create');
        Route::post('/ai-memories',                   [AiMemoryController::class, 'store'])->name('admin.ai-memories.store');
        Route::get('/ai-memories/{aiMemory}/edit',    [AiMemoryController::class, 'edit'])->name('admin.ai-memories.edit');
        Route::put('/ai-memories/{aiMemory}',         [AiMemoryController::class, 'update'])->name('admin.ai-memories.update');
        Route::delete('/ai-memories/{aiMemory}',      [AiMemoryController::class, 'destroy'])->name('admin.ai-memories.destroy');

        // ─── Phase 6 — Social Media ───────────────────────────────────────
        Route::get('/social',                                   [SocialDashboardController::class, 'index'])->name('admin.social.index');

        // Posts
        Route::get('/social/posts',                            [AdminSocialPostController::class, 'index'])->name('admin.social.posts.index');
        Route::get('/social/posts/create',                     [AdminSocialPostController::class, 'create'])->name('admin.social.posts.create');
        Route::post('/social/posts',                           [AdminSocialPostController::class, 'store'])->name('admin.social.posts.store');
        Route::get('/social/posts/{post}',                     [AdminSocialPostController::class, 'show'])->name('admin.social.posts.show');
        Route::get('/social/posts/{post}/edit',                [AdminSocialPostController::class, 'edit'])->name('admin.social.posts.edit');
        Route::patch('/social/posts/{post}',                   [AdminSocialPostController::class, 'update'])->name('admin.social.posts.update');
        Route::delete('/social/posts/{post}',                  [AdminSocialPostController::class, 'destroy'])->name('admin.social.posts.destroy');
        Route::post('/social/posts/{post}/send-approval',      [AdminSocialPostController::class, 'sendToApproval'])->name('admin.social.posts.send-approval');
        Route::post('/social/posts/{post}/approve',            [AdminSocialPostController::class, 'approve'])->name('admin.social.posts.approve');
        Route::post('/social/posts/{post}/reject',             [AdminSocialPostController::class, 'reject'])->name('admin.social.posts.reject');
        Route::patch('/social/posts/{post}/schedule',          [AdminSocialPostController::class, 'schedule'])->name('admin.social.posts.schedule');
        Route::post('/social/posts/{post}/mark-published',     [AdminSocialPostController::class, 'markPublished'])->name('admin.social.posts.mark-published');
        Route::post('/social/posts/{post}/back-to-draft',      [AdminSocialPostController::class, 'backToDraft'])->name('admin.social.posts.back-to-draft');

        // Calendar
        Route::get('/social/calendar',                         [AdminSocialCalendarController::class, 'index'])->name('admin.social.calendar.index');
        Route::get('/social/calendar/create',                  [AdminSocialCalendarController::class, 'create'])->name('admin.social.calendar.create');
        Route::post('/social/calendar',                        [AdminSocialCalendarController::class, 'store'])->name('admin.social.calendar.store');
        Route::get('/social/calendar/{calendar}',              [AdminSocialCalendarController::class, 'show'])->name('admin.social.calendar.show');
        Route::patch('/social/calendar/{calendar}',            [AdminSocialCalendarController::class, 'update'])->name('admin.social.calendar.update');
        Route::delete('/social/calendar/{calendar}',           [AdminSocialCalendarController::class, 'destroy'])->name('admin.social.calendar.destroy');

        // Channels
        Route::get('/social/channels',                         [SocialChannelController::class, 'index'])->name('admin.social.channels.index');
        Route::get('/social/channels/create',                  [SocialChannelController::class, 'create'])->name('admin.social.channels.create');
        Route::post('/social/channels',                        [SocialChannelController::class, 'store'])->name('admin.social.channels.store');
        Route::get('/social/channels/{channel}/edit',          [SocialChannelController::class, 'edit'])->name('admin.social.channels.edit');
        Route::patch('/social/channels/{channel}',             [SocialChannelController::class, 'update'])->name('admin.social.channels.update');
        Route::delete('/social/channels/{channel}',            [SocialChannelController::class, 'destroy'])->name('admin.social.channels.destroy');

        // Briefs
        Route::get('/social/briefs',                           [SocialContentBriefController::class, 'index'])->name('admin.social.briefs.index');
        Route::get('/social/briefs/create',                    [SocialContentBriefController::class, 'create'])->name('admin.social.briefs.create');
        Route::post('/social/briefs',                          [SocialContentBriefController::class, 'store'])->name('admin.social.briefs.store');
        Route::get('/social/briefs/{brief}',                   [SocialContentBriefController::class, 'show'])->name('admin.social.briefs.show');
        Route::get('/social/briefs/{brief}/edit',              [SocialContentBriefController::class, 'edit'])->name('admin.social.briefs.edit');
        Route::patch('/social/briefs/{brief}',                 [SocialContentBriefController::class, 'update'])->name('admin.social.briefs.update');
        Route::delete('/social/briefs/{brief}',                [SocialContentBriefController::class, 'destroy'])->name('admin.social.briefs.destroy');

        // AI Generation
        Route::get('/social/ai/generate',                      [SocialAiController::class, 'generateForm'])->name('admin.social.ai.generate');
        Route::post('/social/ai/generate',                     [SocialAiController::class, 'generate'])->name('admin.social.ai.store');
        Route::get('/social/ai/posts/{post}/variants',         [SocialAiController::class, 'generateVariantsForm'])->name('admin.social.ai.variants');
        Route::post('/social/ai/posts/{post}/variants',        [SocialAiController::class, 'generateVariants'])->name('admin.social.ai.variants.store');
        Route::get('/social/ai/posts/{post}/improve',          [SocialAiController::class, 'improveForm'])->name('admin.social.ai.improve');
        Route::post('/social/ai/posts/{post}/improve',         [SocialAiController::class, 'improve'])->name('admin.social.ai.improve.store');

        // ─── Phase 7 — SEO & Blog IA ─────────────────────────────────────
        Route::get('/seo',                                          [SeoDashboardController::class, 'index'])->name('admin.seo.index');

        // Keywords
        Route::get('/seo/keywords',                                 [SeoKeywordController::class, 'index'])->name('admin.seo.keywords.index');
        Route::get('/seo/keywords/create',                          [SeoKeywordController::class, 'create'])->name('admin.seo.keywords.create');
        Route::post('/seo/keywords',                                [SeoKeywordController::class, 'store'])->name('admin.seo.keywords.store');
        Route::get('/seo/keywords/{seoKeyword}/edit',               [SeoKeywordController::class, 'edit'])->name('admin.seo.keywords.edit');
        Route::put('/seo/keywords/{seoKeyword}',                    [SeoKeywordController::class, 'update'])->name('admin.seo.keywords.update');
        Route::delete('/seo/keywords/{seoKeyword}',                 [SeoKeywordController::class, 'destroy'])->name('admin.seo.keywords.destroy');

        // Clusters
        Route::get('/seo/clusters',                                 [SeoClusterController::class, 'index'])->name('admin.seo.clusters.index');
        Route::get('/seo/clusters/create',                          [SeoClusterController::class, 'create'])->name('admin.seo.clusters.create');
        Route::post('/seo/clusters',                                [SeoClusterController::class, 'store'])->name('admin.seo.clusters.store');
        Route::get('/seo/clusters/{seoCluster}/edit',               [SeoClusterController::class, 'edit'])->name('admin.seo.clusters.edit');
        Route::put('/seo/clusters/{seoCluster}',                    [SeoClusterController::class, 'update'])->name('admin.seo.clusters.update');
        Route::delete('/seo/clusters/{seoCluster}',                 [SeoClusterController::class, 'destroy'])->name('admin.seo.clusters.destroy');

        // Content Plans
        Route::get('/seo/content-plans',                            [SeoContentPlanController::class, 'index'])->name('admin.seo.content-plans.index');
        Route::get('/seo/content-plans/create',                     [SeoContentPlanController::class, 'create'])->name('admin.seo.content-plans.create');
        Route::post('/seo/content-plans',                           [SeoContentPlanController::class, 'store'])->name('admin.seo.content-plans.store');
        Route::get('/seo/content-plans/{seoContentPlan}',           [SeoContentPlanController::class, 'show'])->name('admin.seo.content-plans.show');
        Route::get('/seo/content-plans/{seoContentPlan}/edit',      [SeoContentPlanController::class, 'edit'])->name('admin.seo.content-plans.edit');
        Route::put('/seo/content-plans/{seoContentPlan}',           [SeoContentPlanController::class, 'update'])->name('admin.seo.content-plans.update');

        // Audits
        Route::get('/seo/audits',                                   [SeoAuditController::class, 'index'])->name('admin.seo.audits.index');
        Route::get('/seo/audits/create',                            [SeoAuditController::class, 'create'])->name('admin.seo.audits.create');
        Route::post('/seo/audits',                                  [SeoAuditController::class, 'store'])->name('admin.seo.audits.store');
        Route::get('/seo/audits/{seoAudit}',                        [SeoAuditController::class, 'show'])->name('admin.seo.audits.show');
        Route::post('/seo/audits/{seoAudit}/generate-mock',         [SeoAuditController::class, 'generateMock'])->name('admin.seo.audits.generate-mock');

        // Blog SEO (AI generation & management)
        Route::get('/seo/blog',                                     [AdminBlogController::class, 'index'])->name('admin.seo.blog.index');
        Route::get('/seo/blog/generate-ai',                         [AdminBlogController::class, 'generateAiForm'])->name('admin.seo.blog.generate');
        Route::post('/seo/blog/generate-ai',                        [AdminBlogController::class, 'generateAi'])->name('admin.seo.blog.generate.store');
        Route::post('/seo/blog/{blogPost}/send-approval',           [AdminBlogController::class, 'sendToApproval'])->name('admin.seo.blog.send-approval');
        Route::post('/seo/blog/{blogPost}/publish',                 [AdminBlogController::class, 'publish'])->name('admin.seo.blog.publish');

        // ─── Phase 8 — Ads ───────────────────────────────────────────────────
        Route::get('/ads',                                          [AdsDashboardController::class, 'index'])->name('admin.ads.index');

        // Accounts
        Route::get('/ads/accounts',                                 [AdAccountController::class, 'index'])->name('admin.ads.accounts.index');
        Route::get('/ads/accounts/create',                          [AdAccountController::class, 'create'])->name('admin.ads.accounts.create');
        Route::post('/ads/accounts',                                [AdAccountController::class, 'store'])->name('admin.ads.accounts.store');
        Route::get('/ads/accounts/{adAccount}/edit',                [AdAccountController::class, 'edit'])->name('admin.ads.accounts.edit');
        Route::put('/ads/accounts/{adAccount}',                     [AdAccountController::class, 'update'])->name('admin.ads.accounts.update');

        // Campaigns
        Route::get('/ads/campaigns',                                [AdminAdCampaignController::class, 'index'])->name('admin.ads.campaigns.index');
        Route::get('/ads/campaigns/create',                         [AdminAdCampaignController::class, 'create'])->name('admin.ads.campaigns.create');
        Route::post('/ads/campaigns',                               [AdminAdCampaignController::class, 'store'])->name('admin.ads.campaigns.store');
        Route::get('/ads/campaigns/{adCampaign}',                   [AdminAdCampaignController::class, 'show'])->name('admin.ads.campaigns.show');
        Route::get('/ads/campaigns/{adCampaign}/edit',              [AdminAdCampaignController::class, 'edit'])->name('admin.ads.campaigns.edit');
        Route::put('/ads/campaigns/{adCampaign}',                   [AdminAdCampaignController::class, 'update'])->name('admin.ads.campaigns.update');
        Route::post('/ads/campaigns/{adCampaign}/send-approval',    [AdminAdCampaignController::class, 'sendApproval'])->name('admin.ads.campaigns.send-approval');
        Route::post('/ads/campaigns/{adCampaign}/schedule',         [AdminAdCampaignController::class, 'schedule'])->name('admin.ads.campaigns.schedule');
        Route::post('/ads/campaigns/{adCampaign}/pause-sandbox',    [AdminAdCampaignController::class, 'pauseSandbox'])->name('admin.ads.campaigns.pause-sandbox');
        Route::post('/ads/campaigns/{adCampaign}/generate-mock-metrics', [AdminAdCampaignController::class, 'generateMockMetrics'])->name('admin.ads.campaigns.generate-mock-metrics');
        Route::post('/ads/campaigns/generate-ai',                   [AdsAiController::class, 'generateAi'])->name('admin.ads.campaigns.generate-ai');

        // Metrics
        Route::get('/ads/metrics',                                  [CampaignMetricController::class, 'index'])->name('admin.ads.metrics.index');

        // Budget Approvals
        Route::get('/ads/budget-approvals',                         [CampaignBudgetChangeController::class, 'index'])->name('admin.ads.budget-approvals.index');
        Route::get('/ads/budget-approvals/create',                  [CampaignBudgetChangeController::class, 'create'])->name('admin.ads.budget-approvals.create');
        Route::post('/ads/budget-approvals',                        [CampaignBudgetChangeController::class, 'store'])->name('admin.ads.budget-approvals.store');
        Route::post('/ads/budget-approvals/{campaignBudgetChange}/apply', [CampaignBudgetChangeController::class, 'apply'])->name('admin.ads.budget-approvals.apply');

        // ─── Phase 9 — Commercial ────────────────────────
        Route::get('/proposals',                                        [AdminProposalController::class, 'index'])->name('admin.proposals.index');
        Route::get('/proposals/create',                                 [AdminProposalController::class, 'create'])->name('admin.proposals.create');
        Route::post('/proposals',                                       [AdminProposalController::class, 'store'])->name('admin.proposals.store');
        Route::get('/proposals/{proposal}',                             [AdminProposalController::class, 'show'])->name('admin.proposals.show');
        Route::get('/proposals/{proposal}/edit',                        [AdminProposalController::class, 'edit'])->name('admin.proposals.edit');
        Route::put('/proposals/{proposal}',                             [AdminProposalController::class, 'update'])->name('admin.proposals.update');
        Route::delete('/proposals/{proposal}',                          [AdminProposalController::class, 'destroy'])->name('admin.proposals.destroy');
        Route::post('/proposals/{proposal}/send-approval',              [AdminProposalController::class, 'sendApproval'])->name('admin.proposals.send-approval');
        Route::post('/proposals/{proposal}/send-client',                [AdminProposalController::class, 'sendClient'])->name('admin.proposals.send-client');
        Route::post('/proposals/generate-ai',                           [AdminProposalController::class, 'generateAi'])->name('admin.proposals.generate-ai');

        Route::get('/budgets',                                          [AdminBudgetController::class, 'index'])->name('admin.budgets.index');
        Route::get('/budgets/create',                                   [AdminBudgetController::class, 'create'])->name('admin.budgets.create');
        Route::post('/budgets',                                         [AdminBudgetController::class, 'store'])->name('admin.budgets.store');
        Route::get('/budgets/{budget}',                                 [AdminBudgetController::class, 'show'])->name('admin.budgets.show');
        Route::get('/budgets/{budget}/edit',                            [AdminBudgetController::class, 'edit'])->name('admin.budgets.edit');
        Route::put('/budgets/{budget}',                                 [AdminBudgetController::class, 'update'])->name('admin.budgets.update');
        Route::delete('/budgets/{budget}',                              [AdminBudgetController::class, 'destroy'])->name('admin.budgets.destroy');
        Route::post('/budgets/{budget}/send-approval',                  [AdminBudgetController::class, 'sendApproval'])->name('admin.budgets.send-approval');

        Route::get('/contracts',                                        [AdminContractController::class, 'index'])->name('admin.contracts.index');
        Route::get('/contracts/create',                                 [AdminContractController::class, 'create'])->name('admin.contracts.create');
        Route::post('/contracts',                                       [AdminContractController::class, 'store'])->name('admin.contracts.store');
        Route::get('/contracts/{clientContract}',                       [AdminContractController::class, 'show'])->name('admin.contracts.show');
        Route::get('/contracts/{clientContract}/edit',                  [AdminContractController::class, 'edit'])->name('admin.contracts.edit');
        Route::put('/contracts/{clientContract}',                       [AdminContractController::class, 'update'])->name('admin.contracts.update');
        Route::post('/contracts/{clientContract}/pending-signature',    [AdminContractController::class, 'pendingSignature'])->name('admin.contracts.pending-signature');
        Route::post('/contracts/{clientContract}/mark-signed',          [AdminContractController::class, 'markSigned'])->name('admin.contracts.mark-signed');
        Route::post('/contracts/{clientContract}/cancel',               [AdminContractController::class, 'cancel'])->name('admin.contracts.cancel');

        // ─── Phase 3 — Client Management ─────────────────
        Route::prefix('clients/{client}')->group(function () {

            // Brand Profile
            Route::get('/brand',    [ClientBrandProfileController::class, 'show'])->name('admin.clients.brand.show');
            Route::post('/brand',   [ClientBrandProfileController::class, 'store'])->name('admin.clients.brand.store');
            Route::put('/brand',    [ClientBrandProfileController::class, 'update'])->name('admin.clients.brand.update');

            // Website
            Route::get('/website',                     [ClientWebsiteController::class, 'show'])->name('admin.clients.website.show');
            Route::post('/website',                    [ClientWebsiteController::class, 'store'])->name('admin.clients.website.store');
            Route::put('/website/{website}',           [ClientWebsiteController::class, 'update'])->name('admin.clients.website.update');

            // Pages
            Route::get('/pages',                       [ClientWebsitePageController::class, 'index'])->name('admin.clients.pages.index');
            Route::get('/pages/create',                [ClientWebsitePageController::class, 'create'])->name('admin.clients.pages.create');
            Route::post('/pages',                      [ClientWebsitePageController::class, 'store'])->name('admin.clients.pages.store');
            Route::get('/pages/{page}/edit',           [ClientWebsitePageController::class, 'edit'])->name('admin.clients.pages.edit');
            Route::put('/pages/{page}',                [ClientWebsitePageController::class, 'update'])->name('admin.clients.pages.update');
            Route::delete('/pages/{page}',             [ClientWebsitePageController::class, 'destroy'])->name('admin.clients.pages.destroy');
            Route::post('/pages/{page}/approve',       [ClientWebsitePageController::class, 'approve'])->name('admin.clients.pages.approve');
            Route::post('/pages/{page}/reject',        [ClientWebsitePageController::class, 'reject'])->name('admin.clients.pages.reject');
            Route::post('/pages/{page}/publish',       [ClientWebsitePageController::class, 'publish'])->name('admin.clients.pages.publish');

            // Assets
            Route::get('/assets',                      [ClientAssetController::class, 'index'])->name('admin.clients.assets.index');
            Route::get('/assets/create',               [ClientAssetController::class, 'create'])->name('admin.clients.assets.create');
            Route::post('/assets',                     [ClientAssetController::class, 'store'])->name('admin.clients.assets.store');
            Route::delete('/assets/{asset}',           [ClientAssetController::class, 'destroy'])->name('admin.clients.assets.destroy');

            // Knowledge Base
            Route::get('/knowledge-base',                              [ClientKnowledgeBaseController::class, 'index'])->name('admin.clients.knowledge-base.index');
            Route::get('/knowledge-base/create',                       [ClientKnowledgeBaseController::class, 'create'])->name('admin.clients.knowledge-base.create');
            Route::post('/knowledge-base',                             [ClientKnowledgeBaseController::class, 'store'])->name('admin.clients.knowledge-base.store');
            Route::get('/knowledge-base/{knowledgeBase}/edit',         [ClientKnowledgeBaseController::class, 'edit'])->name('admin.clients.knowledge-base.edit');
            Route::put('/knowledge-base/{knowledgeBase}',              [ClientKnowledgeBaseController::class, 'update'])->name('admin.clients.knowledge-base.update');
            Route::delete('/knowledge-base/{knowledgeBase}',           [ClientKnowledgeBaseController::class, 'destroy'])->name('admin.clients.knowledge-base.destroy');

            // Client Blog
            Route::get('/blog',                        [ClientBlogPostController::class, 'index'])->name('admin.clients.blog.index');
            Route::get('/blog/create',                 [ClientBlogPostController::class, 'create'])->name('admin.clients.blog.create');
            Route::post('/blog',                       [ClientBlogPostController::class, 'store'])->name('admin.clients.blog.store');
            Route::get('/blog/{blogPost}/edit',        [ClientBlogPostController::class, 'edit'])->name('admin.clients.blog.edit');
            Route::put('/blog/{blogPost}',             [ClientBlogPostController::class, 'update'])->name('admin.clients.blog.update');
            Route::delete('/blog/{blogPost}',          [ClientBlogPostController::class, 'destroy'])->name('admin.clients.blog.destroy');
            Route::post('/blog/{blogPost}/approve',    [ClientBlogPostController::class, 'approve'])->name('admin.clients.blog.approve');
            Route::post('/blog/{blogPost}/reject',     [ClientBlogPostController::class, 'reject'])->name('admin.clients.blog.reject');
            Route::post('/blog/{blogPost}/publish',    [ClientBlogPostController::class, 'publish'])->name('admin.clients.blog.publish');

            // Logs
            Route::get('/logs', [ClientActivityLogController::class, 'index'])->name('admin.clients.logs.index');
        });

        // ─── Phase 15 — AI Provider Settings & Usage ──────────────────────
        Route::get('/ai-settings',         [AiSettingsController::class, 'index'])->name('admin.ai-settings.index');
        Route::post('/ai-settings',        [AiSettingsController::class, 'update'])->name('admin.ai-settings.update');
        Route::get('/ai-settings/test',    [AiSettingsController::class, 'testForm'])->name('admin.ai-settings.test');
        Route::post('/ai-settings/test',   [AiSettingsController::class, 'testConnection'])->name('admin.ai-settings.test.run');
        Route::get('/ai-usage',            [AiUsageController::class, 'index'])->name('admin.ai-usage.index');
    });

    // ─── Client Area ──────────────────────────────────────
    Route::prefix('client')->middleware('client_access')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

        // Phase 13 — Client Files
        Route::get('/files',                          [ClientFileController::class, 'index'])->name('client.files.index');
        Route::get('/files/create',                   [ClientFileController::class, 'create'])->name('client.files.create');
        Route::post('/files',                         [ClientFileController::class, 'store'])->name('client.files.store');
        Route::get('/files/google-drive/connect',    [ClientGoogleDriveController::class, 'connect'])->name('client.files.google-drive');
        Route::get('/files/{externalFile}',           [ClientFileController::class, 'show'])->name('client.files.show');
        Route::delete('/files/{externalFile}',        [ClientFileController::class, 'destroy'])->name('client.files.destroy');
        Route::post('/folders',                       [ClientFileController::class, 'storeFolder'])->name('client.folders.store');

        // Phase 11 — Client Reports
        Route::get('/reports',                      [ClientReportController::class, 'index'])->name('client.reports.index');
        Route::get('/reports/social',               [ClientReportController::class, 'social'])->name('client.reports.social');
        Route::get('/reports/campaigns',            [ClientReportController::class, 'campaigns'])->name('client.reports.campaigns');
        Route::get('/reports/seo',                  [ClientReportController::class, 'seo'])->name('client.reports.seo');
        Route::get('/reports/approvals',            [ClientReportController::class, 'approvals'])->name('client.reports.approvals');
        Route::get('/reports/executive',            [ClientReportController::class, 'executive'])->name('client.reports.executive');

        // Phase 3 — Client self-service
        Route::get('/brand',                         [ClientBrandController::class, 'show'])->name('client.brand');
        Route::get('/pages',                         [ClientPageController::class, 'index'])->name('client.pages.index');
        Route::get('/pages/{page}',                  [ClientPageController::class, 'show'])->name('client.pages.show');
        Route::post('/pages/{page}/approve',         [ClientPageController::class, 'approve'])->name('client.pages.approve');
        Route::post('/pages/{page}/reject',          [ClientPageController::class, 'reject'])->name('client.pages.reject');
        // Phase 5 — Client Approvals
        Route::get('/approvals',                                        [ClientApprovalController::class, 'index'])->name('client.approvals.index');
        Route::get('/approvals/{approvalRequest}',                      [ClientApprovalController::class, 'show'])->name('client.approvals.show');
        Route::post('/approvals/{approvalRequest}/approve',             [ClientApprovalController::class, 'approve'])->name('client.approvals.approve');
        Route::post('/approvals/{approvalRequest}/reject',              [ClientApprovalController::class, 'reject'])->name('client.approvals.reject');
        Route::post('/approvals/{approvalRequest}/request-changes',     [ClientApprovalController::class, 'requestChanges'])->name('client.approvals.request-changes');
        Route::post('/approvals/{approvalRequest}/comments',            [ClientApprovalController::class, 'storeComment'])->name('client.approvals.comments');

        Route::get('/blog',                          [ClientBlogController::class, 'index'])->name('client.blog.index');
        Route::get('/blog/{blogPost}',               [ClientBlogController::class, 'show'])->name('client.blog.show');
        Route::post('/blog/{blogPost}/approve',      [ClientBlogController::class, 'approve'])->name('client.blog.approve');
        Route::post('/blog/{blogPost}/reject',       [ClientBlogController::class, 'reject'])->name('client.blog.reject');

        // Phase 6 — Client Social Media
        Route::get('/social/posts',                  [ClientSocialPostController::class, 'index'])->name('client.social.posts.index');
        Route::get('/social/posts/{post}',           [ClientSocialPostController::class, 'show'])->name('client.social.posts.show');
        Route::get('/social/calendar',               [ClientSocialCalendarController::class, 'index'])->name('client.social.calendar.index');
        Route::get('/social/approvals',              [SocialApprovalController::class, 'index'])->name('client.social.approvals.index');
        Route::post('/social/approvals/{approvalRequest}/approve', [SocialApprovalController::class, 'approve'])->name('client.social.approvals.approve');
        Route::post('/social/approvals/{approvalRequest}/reject',  [SocialApprovalController::class, 'reject'])->name('client.social.approvals.reject');

        // Phase 7 — Client SEO
        Route::get('/seo',                                          [ClientSeoDashboardController::class, 'index'])->name('client.seo.index');
        Route::get('/seo/blog',                                     [ClientBlogSeoController::class, 'index'])->name('client.seo.blog.index');
        Route::get('/seo/blog/approvals',                           [ClientBlogSeoController::class, 'approvals'])->name('client.seo.blog.approvals');
        Route::get('/seo/blog/{blogPost}',                          [ClientBlogSeoController::class, 'show'])->name('client.seo.blog.show');

        // Phase 8 — Client Ads
        Route::get('/ads',                                          [ClientAdsDashboardController::class, 'index'])->name('client.ads.index');
        Route::get('/ads/campaigns',                                [ClientAdCampaignController::class, 'index'])->name('client.ads.campaigns.index');
        Route::get('/ads/campaigns/{adCampaign}',                   [ClientAdCampaignController::class, 'show'])->name('client.ads.campaigns.show');
        Route::get('/ads/approvals',                                [AdsApprovalController::class, 'index'])->name('client.ads.approvals.index');
        Route::get('/ads/reports',                                  [AdsReportController::class, 'index'])->name('client.ads.reports.index');

        // Phase 9 — Client Commercial
        Route::get('/proposals',                                    [ClientProposalController::class, 'index'])->name('client.proposals.index');
        Route::get('/proposals/{proposal}',                         [ClientProposalController::class, 'show'])->name('client.proposals.show');
        Route::post('/proposals/{proposal}/accept',                 [ClientProposalController::class, 'accept'])->name('client.proposals.accept');
        Route::post('/proposals/{proposal}/reject',                 [ClientProposalController::class, 'reject'])->name('client.proposals.reject');
        Route::post('/proposals/{proposal}/comment',                [ClientProposalController::class, 'comment'])->name('client.proposals.comment');

        Route::get('/budgets',                                      [ClientBudgetController::class, 'index'])->name('client.budgets.index');
        Route::get('/budgets/{budget}',                             [ClientBudgetController::class, 'show'])->name('client.budgets.show');
        Route::post('/budgets/{budget}/approve',                    [ClientBudgetController::class, 'approve'])->name('client.budgets.approve');
        Route::post('/budgets/{budget}/reject',                     [ClientBudgetController::class, 'reject'])->name('client.budgets.reject');
        Route::post('/budgets/{budget}/comment',                    [ClientBudgetController::class, 'comment'])->name('client.budgets.comment');

        Route::get('/contracts',                                    [ClientContractController::class, 'index'])->name('client.contracts.index');
        Route::get('/contracts/{clientContract}',                   [ClientContractController::class, 'show'])->name('client.contracts.show');
    });

});

// Phase 10 — Mobile PWA App
Route::prefix('app')->name('app.')->middleware(['auth', 'active', 'client_access'])->group(function () {

    Route::get('/',                                              AppDashboardController::class)->name('dashboard');
    Route::get('/calendar',                                      AppCalendarController::class)->name('calendar');
    Route::get('/reports',                                       AppReportController::class)->name('reports');
    Route::get('/profile',                                       AppProfileController::class)->name('profile');

    // Approvals
    Route::get('/approvals',                                     [AppApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/{approvalRequest}',                   [AppApprovalController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/{approvalRequest}/approve',          [AppApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{approvalRequest}/reject',           [AppApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('/approvals/{approvalRequest}/request-changes',  [AppApprovalController::class, 'requestChanges'])->name('approvals.request-changes');
    Route::post('/approvals/{approvalRequest}/comments',         [AppApprovalController::class, 'comment'])->name('approvals.comment');
});
