<?php

namespace App\Http\Middleware;

use App\Services\Auth\AccessScopeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy alias — now only admin_geral can access the admin panel.
 * Kept for backward compatibility with any routes still referencing 'agency' middleware.
 */
class EnsureAgencyAccess
{
    public function __construct(private AccessScopeService $scope) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->isAdminGeral()) {
            if ($user) {
                $this->scope->logDenied($user, 'access.denied.admin_panel', $request->path());
            }
            abort(403, 'Acesso restrito ao Administrador Geral.');
        }

        return $next($request);
    }
}
