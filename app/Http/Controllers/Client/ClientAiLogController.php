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
            $logs = \App\Models\AiTaskLog::where('client_id', $user->client_id)
                ->select(['id', 'ai_task_id', 'client_id', 'level', 'message', 'status', 'created_at'])
                ->orderByDesc('created_at')
                ->paginate(30);
        }

        return view('client.ai-logs.index', compact('logs'));
    }
}
