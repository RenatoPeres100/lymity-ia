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
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
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

        // Blog Posts
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
    });

    // ─── Client Area ──────────────────────────────────────
    Route::prefix('client')->middleware('client_access')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    });

});
