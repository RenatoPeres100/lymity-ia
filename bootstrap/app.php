<?php

use App\Http\Middleware\EnsureAdminGeral;
use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\EnsureAgencyAccess;
use App\Http\Middleware\EnsureApiAuthenticated;
use App\Http\Middleware\EnsureCanAccessClient;
use App\Http\Middleware\EnsureCanAccessCompany;
use App\Http\Middleware\EnsureClientAccess;
use App\Http\Middleware\EnsureClientPanelAccess;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active'         => EnsureUserIsActive::class,
            'admin_geral'    => EnsureAdminGeral::class,
            'agency'         => EnsureAgencyAccess::class,
            'client_access'  => EnsureClientAccess::class,
            'api_auth'       => EnsureApiAuthenticated::class,
            'admin.panel'    => EnsureAdminPanelAccess::class,
            'client.panel'   => EnsureClientPanelAccess::class,
            'company.scope'  => EnsureCanAccessCompany::class,
            'client.scope'   => EnsureCanAccessClient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
