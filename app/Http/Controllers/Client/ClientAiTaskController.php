<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ClientAiTaskController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user->hasPermission('client.ai_tasks.view'), 403, 'Sem permissão para acessar as Tarefas IA.');

        $tasks = collect();

        if (class_exists(\App\Models\AiTask::class)) {
            $tasks = \App\Models\AiTask::where('client_id', $user->client_id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        return view('client.ai-tasks.index', compact('tasks'));
    }
}
