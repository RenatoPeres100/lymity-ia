<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgencyAccess
{
    private const AGENCY_ROLES = [
        'admin_geral', 'agencia_admin', 'agencia_operador',
        'social_media', 'gestor_trafego', 'seo', 'copywriter', 'designer', 'blog_writer',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, self::AGENCY_ROLES)) {
            abort(403, 'Acesso restrito a membros da agência.');
        }

        return $next($request);
    }
}
