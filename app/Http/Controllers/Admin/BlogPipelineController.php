<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class BlogPipelineController extends Controller
{
    public function index()
    {
        $drafts          = BlogPost::agency()->draft()->with('author', 'aiEmployee')->orderByDesc('updated_at')->get();
        $pendingApproval = BlogPost::agency()->pendingApproval()->with('author')->orderByDesc('updated_at')->get();
        $approved        = BlogPost::agency()->approved()->with('author', 'approver')->orderByDesc('approved_at')->get();
        $scheduled       = BlogPost::agency()->scheduled()->with('author')->orderBy('scheduled_at')->get();
        $published       = BlogPost::agency()->published()->with('author')->orderByDesc('published_at')->take(20)->get();
        $failed          = BlogPost::agency()->where('status', 'failed')->with('author')->orderByDesc('updated_at')->get();

        return view('admin.blog.pipeline.index', compact(
            'drafts', 'pendingApproval', 'approved', 'scheduled', 'published', 'failed'
        ));
    }
}
