<?php

namespace App\Http\Middleware;

use App\Services\Auth\AccessScopeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessClient
{
    public function __construct(private AccessScopeService $scope) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $clientId = (int) $request->route('client')
            ?? (int) $request->route('client_id')
            ?? null;

        if ($clientId && !$this->scope->canSeeClient($user, $clientId)) {
            $this->scope->logDenied($user, 'access.denied.cross_client', $request->path(), 'client', $clientId);
            abort(403, 'Você não tem acesso a este cliente.');
        }

        return $next($request);
    }
}
