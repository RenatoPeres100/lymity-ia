<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateSocialPostAiRequest;
use App\Http\Requests\GenerateSocialVariantsRequest;
use App\Models\SocialPost;
use App\Services\Social\SocialAiService;
use Illuminate\Http\Request;

class SocialAiController extends Controller
{
    public function __construct(private SocialAiService $aiService) {}

    public function generateForm()
    {
        return view('admin.social.ai.generate');
    }

    public function generate(GenerateSocialPostAiRequest $request)
    {
        // Geração solta bloqueada — toda geração de IA exige uma AgentTask ativa
        \Illuminate\Support\Facades\Log::warning('[SocialAiController] Tentativa de geração solta bloqueada.', [
            'user_id' => $request->user()?->id,
            'ip'      => $request->ip(),
        ]);

        return redirect()->route('admin.agent-tasks.index')
            ->with('warning', 'Crie uma Tarefa Operacional antes de gerar conteúdo com IA. '
                . 'Use uma tarefa com task_type instagram_post_recurring ou instagram_carousel_recurring e status active.');
    }

    public function generateVariantsForm(SocialPost $post)
    {
        return view('admin.social.ai.variants', compact('post'));
    }

    public function generateVariants(GenerateSocialVariantsRequest $request, SocialPost $post)
    {
        try {
            $variants = $this->aiService->generateVariants($post, $request->validated('platforms'), $request->user());

            return redirect()
                ->route('admin.social.posts.show', $post)
                ->with('success', count($variants) . ' variação(ões) gerada(s).');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Erro ao gerar variações: ' . $e->getMessage());
        }
    }

    public function improveForm(SocialPost $post)
    {
        return view('admin.social.ai.improve', compact('post'));
    }

    public function improve(Request $request, SocialPost $post)
    {
        $request->validate(['instruction' => 'required|string|max:1000']);

        try {
            $task = $this->aiService->improvePost($post, $request->instruction, $request->user());

            return redirect()
                ->route('admin.social.posts.show', $post)
                ->with('success', "Melhoria IA processada. Tarefa #{$task->id}.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Erro ao melhorar post: ' . $e->getMessage());
        }
    }
}
