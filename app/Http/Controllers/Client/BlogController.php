<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $client = auth()->user()->client;
        $posts  = BlogPost::with('category')
            ->client($client?->id)
            ->latest()
            ->paginate(15);

        return view('client.blog.index', compact('client', 'posts'));
    }

    public function show(BlogPost $blogPost): View
    {
        $client = auth()->user()->client;
        abort_unless($blogPost->client_id === $client?->id, 403);

        return view('client.blog.show', compact('client', 'blogPost'));
    }

    public function approve(BlogPost $blogPost): RedirectResponse
    {
        $client = auth()->user()->client;
        abort_unless($blogPost->client_id === $client?->id, 403);
        abort_unless($blogPost->status === 'pending_approval', 403, 'Post não está aguardando aprovação.');

        $blogPost->update(['status' => 'approved']);

        ActivityLog::record('approved', 'client_blog_posts',
            "Post '{$blogPost->title}' aprovado pelo cliente", $client->id,
            ['post_id' => $blogPost->id, 'approved_by' => auth()->id()]);

        return back()->with('success', 'Post aprovado com sucesso!');
    }

    public function reject(BlogPost $blogPost): RedirectResponse
    {
        $client = auth()->user()->client;
        abort_unless($blogPost->client_id === $client?->id, 403);
        abort_unless($blogPost->status === 'pending_approval', 403, 'Post não está aguardando aprovação.');

        $blogPost->update(['status' => 'draft']);

        ActivityLog::record('rejected', 'client_blog_posts',
            "Post '{$blogPost->title}' reprovado pelo cliente", $client->id,
            ['post_id' => $blogPost->id, 'rejected_by' => auth()->id()]);

        return back()->with('success', 'Post reprovado.');
    }
}
