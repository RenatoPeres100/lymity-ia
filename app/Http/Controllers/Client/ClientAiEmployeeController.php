<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ClientAiEmployeeController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user->hasPermission('client.ai_employees.view'), 403, 'Sem permissão para acessar os Funcionários IA.');

        $employees = collect();

        if (class_exists(\App\Models\AiEmployee::class)) {
            $employees = \App\Models\AiEmployee::where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        return view('client.ai-employees.index', compact('employees'));
    }
}
