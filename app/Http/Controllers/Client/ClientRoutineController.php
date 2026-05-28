<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ClientRoutineController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user->hasPermission('client.routines.view'), 403, 'Sem permissão para acessar as Rotinas.');

        $routines = collect();

        if (class_exists(\App\Models\AgentRoutine::class)) {
            $routines = \App\Models\AgentRoutine::where('client_id', $user->client_id)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('client.routines.index', compact('routines'));
    }
}
