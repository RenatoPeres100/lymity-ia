<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ClientAiLogController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user->hasPermission('client.ai_logs.view'), 403, 'Sem permissão para acessar os Logs IA.');

        $logs = collect();

        if (class_exists(\App\Models\AiTaskLog::class)) {
            $logs = \App\Models\AiTaskLog::whereHas('task', fn ($q) => $q->where('client_id', $user->client_id))
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();
        }

        return view('client.ai-logs.index', compact('logs'));
    }
}
