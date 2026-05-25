<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        if ($user->isAiEmployee()) {
            return response()->json(['message' => 'Acesso não permitido para funcionários IA.'], 403);
        }

        if (!$user->isActive()) {
            return response()->json(['message' => 'Conta inativa.'], 403);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
