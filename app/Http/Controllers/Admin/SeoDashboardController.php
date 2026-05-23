<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\SeoAudit;
use App\Models\SeoCluster;
use App\Models\SeoContentPlan;
use App\Models\SeoKeyword;

class SeoDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'keywords'     => SeoKeyword::count(),
            'clusters'     => SeoCluster::count(),
            'plans'        => SeoContentPlan::count(),
            'audits'       => SeoAudit::count(),
            'blog_posts'   => BlogPost::count(),
            'published'    => BlogPost::where('status', 'published')->count(),
            'pending'      => BlogPost::where('status', 'pending_approval')->count(),
            'draft'        => BlogPost::where('status', 'draft')->count(),
        ];

        $recentPosts   = BlogPost::with('author', 'client')->latest()->take(5)->get();
        $recentAudits  = SeoAudit::with('client')->latest()->take(5)->get();

        return view('admin.seo.index', compact('stats', 'recentPosts', 'recentAudits'));
    }
}
