<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isAdminGeral()) {
            return $next($request);
        }

        if ($user->isClientUser() && $user->client_id) {
            return $next($request);
        }

        abort(403, 'Acesso restrito à área do cliente.');
    }
}
