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
        $provider    = config('ai.provider', 'mock');
        $realEnabled = config('ai.real_enabled', false);

        if ($provider === 'mock' || !$realEnabled) {
            return back()->withErrors(['ai' => 'Provider de IA não configurado. Configure um provedor real (AI_PROVIDER e AI_REAL_ENABLED=true) para gerar conteúdo.']);
        }

        $request->validate([
            'tema'        => 'required|string|max:200',
            'keyword'     => 'required|string|max:100',
            'keywords_sec'=> 'nullable|string|max:300',
            'objetivo'    => 'nullable|string|max:300',
            'tom'         => 'nullable|string|max:100',
            'publico'     => 'nullable|string|max:200',
            'cta'         => 'nullable|string|max:200',
        ]);

        $result = $this->pipeline->createDraftFromAi($request->all(), $request->user());

        if (is_array($result) && isset($result['error'])) {
            return back()->withErrors(['ai' => $result['error']]);
        }

        return redirect()->route('admin.blog.pipeline.index')
            ->with('success', 'Geração de artigo enfileirada. O rascunho aparecerá em breve.');
    }
}
