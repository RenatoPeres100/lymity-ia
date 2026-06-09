<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Blog\BlogPipelineService;
use Illuminate\Http\Request;

class BlogAiController extends Controller
{
    public function __construct(private BlogPipelineService $pipeline) {}

    public function create()
    {
        $provider    = config('ai.provider', 'mock');
        $realEnabled = config('ai.real_enabled', false);
        $blocked     = ($provider === 'mock' || !$realEnabled);

        return view('admin.blog.pipeline.generate-ai', compact('provider', 'realEnabled', 'blocked'));
    }

    public function store(Request $request)
    {
        // Geração solta bloqueada — toda geração de IA exige uma AgentTask ativa
        \Illuminate\Support\Facades\Log::warning('[BlogAiController] Tentativa de geração solta bloqueada.', [
            'user_id' => $request->user()?->id,
            'ip'      => $request->ip(),
        ]);

        return redirect()->route('admin.agent-tasks.index')
            ->with('warning', 'Crie uma Tarefa Operacional antes de gerar conteúdo com IA. '
                . 'Use uma tarefa com task_type blog_post_recurring e status active.');
    }
}
