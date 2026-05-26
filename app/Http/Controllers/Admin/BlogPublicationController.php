<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\Blog\BlogPipelineService;
use Illuminate\Http\Request;

class BlogPublicationController extends Controller
{
    public function __construct(private BlogPipelineService $pipeline) {}

    public function submitApproval(BlogPost $blogPost, Request $request)
    {
        $this->pipeline->submitForApproval($blogPost, $request->user());
        return back()->with('success', 'Post enviado para aprovação.');
    }

    public function approve(BlogPost $blogPost, Request $request)
    {
        $this->pipeline->approve($blogPost, $request->user());
        return back()->with('success', 'Post aprovado com sucesso.');
    }

    public function reject(BlogPost $blogPost, Request $request)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $this->pipeline->reject($blogPost, $request->user(), $request->input('reason'));
        return back()->with('success', 'Post reprovado.');
    }

    public function schedule(BlogPost $blogPost, Request $request)
    {
        $request->validate(['scheduled_at' => 'required|date|after:now']);
        $this->pipeline->schedule($blogPost, $request->input('scheduled_at'), $request->user());
        return back()->with('success', 'Post agendado para ' . $request->input('scheduled_at') . '.');
    }

    public function publishNow(BlogPost $blogPost, Request $request)
    {
        $this->pipeline->publishNow($blogPost, $request->user());
        $blogPost->refresh();
        if ($blogPost->isPublished()) {
            return back()->with('success', 'Post publicado com sucesso!');
        }
        return back()->with('error', 'Falha na publicação: ' . $blogPost->publication_error);
    }

    public function archive(BlogPost $blogPost, Request $request)
    {
        $blogPost->update(['status' => 'archived']);
        $this->pipeline->registerLog($blogPost, 'archived', $request->user());
        return back()->with('success', 'Post arquivado.');
    }

    public function logs(BlogPost $blogPost)
    {
        $logs = \App\Models\ActivityLog::where('subject_type', BlogPost::class)
            ->where('subject_id', $blogPost->id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.blog.posts.logs', compact('blogPost', 'logs'));
    }
}
