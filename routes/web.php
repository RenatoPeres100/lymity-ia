<?php

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
    });

    // ─── Client Area ──────────────────────────────────────
    Route::prefix('client')->middleware('client_access')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

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
    });

});
