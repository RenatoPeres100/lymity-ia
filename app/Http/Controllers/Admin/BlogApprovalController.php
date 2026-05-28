<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Services\Blog\BlogPipelineService;
use Illuminate\Http\Request;

class BlogApprovalController extends Controller
{
    public function index()
    {
        $approvals = ApprovalRequest::where('approvable_type', BlogPost::class)
            ->with(['approvable', 'aiEmployee', 'requester'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.blog.approvals.index', compact('approvals'));
    }

    public function show(ApprovalRequest $approvalRequest)
    {
        abort_unless($approvalRequest->approvable_type === BlogPost::class, 404);
        $approvalRequest->load('approvable.contentBrief', 'aiEmployee', 'requester');
        $post = $approvalRequest->approvable;
        return view('admin.blog.approvals.show', compact('approvalRequest', 'post'));
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest, BlogPipelineService $pipeline)
    {
        abort_unless($approvalRequest->approvable_type === BlogPost::class, 404);
        $post = $approvalRequest->approvable;

        try {
            $pipeline->approvePost($post, auth()->user());
            return redirect()->route('admin.blog.approvals.index')
                ->with('success', "Post \"{$post->title}\" aprovado. Pronto para agendamento.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest, BlogPipelineService $pipeline)
    {
        abort_unless($approvalRequest->approvable_type === BlogPost::class, 404);
        $post = $approvalRequest->approvable;

        $request->validate(['reason' => 'nullable|string|max:1000']);

        try {
            $pipeline->rejectPost($post, auth()->user(), $request->reason);
            return redirect()->route('admin.blog.approvals.index')
                ->with('success', "Post \"{$post->title}\" reprovado.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function schedulePost(Request $request, BlogPost $blogPost, BlogPipelineService $pipeline)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        try {
            $pipeline->schedulePost($blogPost, $request->scheduled_at, auth()->user());
            return redirect()->route('admin.blog.pipeline.index')
                ->with('success', "Post agendado para " . \Carbon\Carbon::parse($request->scheduled_at)->format('d/m/Y H:i') . ".");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
