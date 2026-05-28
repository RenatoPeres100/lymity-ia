<?php

namespace App\Http\Middleware;

use App\Services\Auth\AccessScopeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientPanelAccess
{
    public function __construct(private AccessScopeService $scope) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isActive()) {
            $this->scope->logDenied($user, 'access.denied.inactive_user', $request->path());
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->withErrors(['email' => 'Sua conta está inativa.']);
        }

        if ($user->isAiEmployee()) {
            $this->scope->logDenied($user, 'access.denied.ai_employee_panel', $request->path());
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->withErrors(['email' => 'Acesso não permitido para funcionários IA.']);
        }

        if ($user->canAccessClientPanel()) {
            return $next($request);
        }

        $this->scope->logDenied($user, 'access.denied.client_panel', $request->path());
        return redirect()->route('login')
            ->withErrors(['email' => 'Acesso não autorizado à área do cliente.']);
    }
}
