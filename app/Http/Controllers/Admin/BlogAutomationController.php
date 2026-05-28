<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiEmployee;
use App\Models\AiTaskLog;
use App\Models\AgencyBrandContext;
use App\Models\ContentBrief;
use App\Services\AI\AICostGuardService;
use App\Services\Blog\BlogBriefGenerationService;
use App\Services\Blog\BlogDraftGenerationService;
use Illuminate\Http\Request;

class BlogAutomationController extends Controller
{
    public function index(AICostGuardService $costGuard)
    {
        $geminiConfigured = config('ai.provider') === 'google' && !empty(config('ai.gemini_api_key'));
        $geminiModel      = config('ai.gemini_text_model');
        $fallbackModel    = config('ai.gemini_text_fallback_model');
        $textOnlyMode     = config('ai.text_only_mode', true);
        $imagePipeline    = config('features.blog_image_pipeline', false);

        $brandCtx = AgencyBrandContext::where('active', true)->whereNull('client_id')->latest()->first();

        $planner = AiEmployee::where('slug', 'blog-planner-ia')->first();
        $writer  = AiEmployee::where('slug', 'blog-writer-ia')->first();

        $dailyUsage   = $costGuard->getDailyUsage();
        $monthlyUsage = $costGuard->getMonthlyUsage();

        $recentLogs = AiTaskLog::where('task_type', 'like', '%brief%')
            ->orWhere('task_type', 'like', '%draft%')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.blog.automation.index', compact(
            'geminiConfigured', 'geminiModel', 'fallbackModel', 'textOnlyMode', 'imagePipeline',
            'brandCtx', 'planner', 'writer', 'dailyUsage', 'monthlyUsage', 'recentLogs'
        ));
    }

    public function generateBrief(Request $request, BlogBriefGenerationService $service)
    {
        $request->validate([
            'topic'   => 'required|string|max:300',
            'keyword' => 'nullable|string|max:150',
            'goal'    => 'nullable|string|max:500',
        ]);

        try {
            $brief = $service->generate($request->only(['topic', 'keyword', 'goal']), auth()->user());
            return redirect()->route('admin.blog.briefs.show', $brief)
                ->with('success', "Briefing gerado com sucesso: {$brief->title}");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function generateDraft(Request $request, ContentBrief $contentBrief, BlogDraftGenerationService $service)
    {
        try {
            $post = $service->generateFromBrief($contentBrief, auth()->user());
            return redirect()->route('admin.blog.approvals.show', \App\Models\ApprovalRequest::where('approvable_type', \App\Models\BlogPost::class)->where('approvable_id', $post->id)->latest()->first())
                ->with('success', "Artigo gerado: {$post->title}. Aguardando aprovação.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
