<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\Admin\AiEmployeeController;
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
use App\Http\Controllers\Client\BlogController as ClientBlogController;
use App\Http\Controllers\Client\BrandController as ClientBrandController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\PageController as ClientPageController;
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

        // AI Employees
        Route::get('/ai-employees',              [AiEmployeeController::class, 'index'])->name('admin.ai-employees.index');
        Route::get('/ai-employees/{aiEmployee}', [AiEmployeeController::class, 'show'])->name('admin.ai-employees.show');

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
        Route::get('/blog',                          [ClientBlogController::class, 'index'])->name('client.blog.index');
        Route::get('/blog/{blogPost}',               [ClientBlogController::class, 'show'])->name('client.blog.show');
        Route::post('/blog/{blogPost}/approve',      [ClientBlogController::class, 'approve'])->name('client.blog.approve');
        Route::post('/blog/{blogPost}/reject',       [ClientBlogController::class, 'reject'])->name('client.blog.reject');
    });

});
