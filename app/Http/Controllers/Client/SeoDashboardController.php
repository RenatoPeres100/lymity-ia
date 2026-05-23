<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\SeoAudit;
use App\Models\SeoKeyword;

class SeoDashboardController extends Controller
{
    public function index()
    {
        $clientId = auth()->user()->client_id;

        $stats = [
            'keywords'   => SeoKeyword::where('client_id', $clientId)->count(),
            'audits'     => SeoAudit::where('client_id', $clientId)->count(),
            'blog_posts' => BlogPost::where('client_id', $clientId)->where('type', 'client')->count(),
            'published'  => BlogPost::where('client_id', $clientId)->where('type', 'client')->where('status', 'published')->count(),
            'pending'    => BlogPost::where('client_id', $clientId)->where('type', 'client')->where('status', 'pending_approval')->count(),
        ];

        $recentPosts  = BlogPost::where('client_id', $clientId)->where('type', 'client')->latest()->take(5)->get();
        $recentAudits = SeoAudit::where('client_id', $clientId)->with('recommendations')->latest()->take(3)->get();

        return view('client.seo.index', compact('stats', 'recentPosts', 'recentAudits'));
    }
}
