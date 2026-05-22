<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// ─── Auth ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Authenticated ────────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin — Agency access
    Route::prefix('admin')->middleware('agency')->group(function () {
        Route::get('/users',    [UserController::class,    'index'])->name('admin.users');
        Route::get('/clients',  [ClientController::class,  'index'])->name('admin.clients');
        Route::get('/settings', [SettingController::class, 'index'])
            ->middleware('admin_geral')
            ->name('admin.settings');
    });

    // Client area
    Route::prefix('client')->middleware('client_access')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    });

});
