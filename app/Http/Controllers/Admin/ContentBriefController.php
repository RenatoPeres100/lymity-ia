<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBrief;
use App\Services\Blog\BlogBriefGenerationService;
use App\Services\Blog\BlogDraftGenerationService;
use Illuminate\Http\Request;

class ContentBriefController extends Controller
{
    public function index()
    {
        $briefs = ContentBrief::with('generatedByAgent', 'approvedBy')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.blog.briefs.index', compact('briefs'));
    }

    public function create()
    {
        return view('admin.blog.briefs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'topic'              => 'required|string|max:500',
            'goal'               => 'nullable|string',
            'audience'           => 'nullable|string',
            'primary_keyword'    => 'nullable|string|max:150',
            'secondary_keywords' => 'nullable|string',
            'funnel_stage'       => 'nullable|in:awareness,consideration,conversion,retention',
            'search_intent'      => 'nullable|in:informational,commercial,transactional,navigational',
            'cta_suggestion'     => 'nullable|string',
        ]);

        $data['secondary_keywords'] = array_filter(array_map('trim', explode(',', $data['secondary_keywords'] ?? '')));
        $data['status']             = 'draft';

        $brief = ContentBrief::create($data);

        return redirect()->route('admin.blog.briefs.show', $brief)
            ->with('success', 'Briefing criado com sucesso.');
    }

    public function show(ContentBrief $contentBrief)
    {
        $contentBrief->load('generatedByAgent', 'approvedBy', 'blogPosts');
        return view('admin.blog.briefs.show', compact('contentBrief'));
    }

    public function edit(ContentBrief $contentBrief)
    {
        return view('admin.blog.briefs.edit', compact('contentBrief'));
    }

    public function update(Request $request, ContentBrief $contentBrief)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'topic'              => 'required|string|max:500',
            'goal'               => 'nullable|string',
            'audience'           => 'nullable|string',
            'primary_keyword'    => 'nullable|string|max:150',
            'secondary_keywords' => 'nullable|string',
            'funnel_stage'       => 'nullable|in:awareness,consideration,conversion,retention',
            'search_intent'      => 'nullable|in:informational,commercial,transactional,navigational',
            'cta_suggestion'     => 'nullable|string',
        ]);

        $data['secondary_keywords'] = array_filter(array_map('trim', explode(',', $data['secondary_keywords'] ?? '')));
        $contentBrief->update($data);

        return redirect()->route('admin.blog.briefs.show', $contentBrief)
            ->with('success', 'Briefing atualizado.');
    }

    public function generateAi(Request $request, BlogBriefGenerationService $service)
    {
        $request->validate([
            'topic'   => 'required|string|max:300',
            'keyword' => 'nullable|string|max:150',
            'goal'    => 'nullable|string|max:500',
        ]);

        try {
            $brief = $service->generate($request->only(['topic', 'keyword', 'goal']), auth()->user());
            return redirect()->route('admin.blog.briefs.show', $brief)
                ->with('success', "Briefing gerado pela IA: {$brief->title}");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approve(ContentBrief $contentBrief)
    {
        $contentBrief->update([
            'status'           => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at'      => now(),
        ]);

        return back()->with('success', 'Briefing aprovado.');
    }

    public function generateDraft(ContentBrief $contentBrief, BlogDraftGenerationService $service)
    {
        try {
            $post     = $service->generateFromBrief($contentBrief, auth()->user());
            $approval = \App\Models\ApprovalRequest::where('approvable_type', \App\Models\BlogPost::class)
                ->where('approvable_id', $post->id)
                ->latest()
                ->first();

            $msg = "Artigo gerado: {$post->title}. Aguardando aprovação.";

            if ($approval) {
                return redirect()->route('admin.blog.approvals.show', $approval)->with('success', $msg);
            }
            return redirect()->route('admin.blog.approvals.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function archive(ContentBrief $contentBrief)
    {
        $contentBrief->update(['status' => 'archived']);
        return back()->with('success', 'Briefing arquivado.');
    }
}
