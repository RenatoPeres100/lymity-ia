<?php

use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Client\ApprovalController as ApiApprovalController;
use App\Http\Controllers\Api\Client\BlogPostController as ApiBlogPostController;
use App\Http\Controllers\Api\Client\BudgetController as ApiBudgetController;
use App\Http\Controllers\Api\Client\CampaignController as ApiCampaignController;
use App\Http\Controllers\Api\Client\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\Client\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\Client\ProposalController as ApiProposalController;
use App\Http\Controllers\Api\Client\SocialPostController as ApiSocialPostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Lymity IA
|--------------------------------------------------------------------------
|
| Autenticação: sessão web (cookie-based). Para acesso externo / app nativo,
| instalar Laravel Sanctum e adicionar middleware auth:sanctum.
|
| Testar via navegador logado: acessar /api/me diretamente.
| Testar via curl com sessão:
|   1. POST /login com credenciais para obter cookie de sessão
|   2. Incluir cookie e X-XSRF-TOKEN nas requisições subsequentes
|
*/

Route::middleware('api_auth')->group(function () {

    Route::get('/me', MeController::class)->name('api.me');

    Route::prefix('client')->name('api.client.')->group(function () {

        Route::get('/dashboard',        [ApiDashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications',    [ApiNotificationController::class, 'index'])->name('notifications');
        Route::get('/social/posts',     [ApiSocialPostController::class, 'index'])->name('social.posts');
        Route::get('/ads/campaigns',    [ApiCampaignController::class, 'index'])->name('ads.campaigns');
        Route::get('/blog/posts',       [ApiBlogPostController::class, 'index'])->name('blog.posts');
        Route::get('/budgets',          [ApiBudgetController::class, 'index'])->name('budgets');
        Route::get('/proposals',        [ApiProposalController::class, 'index'])->name('proposals');

        // Approvals
        Route::get('/approvals',                                          [ApiApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{approvalRequest}',                        [ApiApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{approvalRequest}/approve',               [ApiApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{approvalRequest}/reject',                [ApiApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{approvalRequest}/request-changes',       [ApiApprovalController::class, 'requestChanges'])->name('approvals.request-changes');
    });
});
