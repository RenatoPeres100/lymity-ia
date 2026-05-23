<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;

class ClientBlogSeoController extends Controller
{
    public function index()
    {
        $clientId = auth()->user()->client_id;

        $posts = BlogPost::where('client_id', $clientId)
            ->where('type', 'client')
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->latest()
            ->paginate(12);

        return view('client.seo-blog.index', compact('posts'));
    }

    public function show(BlogPost $blogPost)
    {
        $clientId = auth()->user()->client_id;

        if ($blogPost->client_id !== $clientId || $blogPost->type !== 'client') {
            abort(403);
        }

        return view('client.seo-blog.show', compact('blogPost'));
    }

    public function approvals()
    {
        $clientId = auth()->user()->client_id;

        $approvals = ApprovalRequest::where('client_id', $clientId)
            ->where('approval_type', 'blog')
            ->with('approvable')
            ->latest()
            ->paginate(15);

        return view('client.seo-blog.approvals', compact('approvals'));
    }
}
