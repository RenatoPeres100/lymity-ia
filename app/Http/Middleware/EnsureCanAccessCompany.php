<?php

namespace App\Http\Middleware;

use App\Services\Auth\AccessScopeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessCompany
{
    public function __construct(private AccessScopeService $scope) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = (int) $request->route('company')
            ?? (int) $request->route('company_id')
            ?? null;

        if ($companyId && !$this->scope->canSeeCompany($user, $companyId)) {
            $this->scope->logDenied($user, 'access.denied.cross_company', $request->path(), 'company', $companyId);
            abort(403, 'Você não tem acesso a esta empresa.');
        }

        return $next($request);
    }
}
