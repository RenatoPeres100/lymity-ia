<?php

namespace App\Http\Controllers;

use App\Models\AiEmployee;
use App\Models\BlogPost;
use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_clients'       => Client::where('status', 'active')->count(),
            'total_users'         => User::where('status', 'active')->where('user_type', '!=', 'ai')->count(),
            'ai_employees'        => AiEmployee::where('status', 'active')->count(),
            'pending_approvals'   => 0,
            'planned_posts'       => BlogPost::where('status', 'pending_approval')->count(),
            'campaigns_analysis'  => 0,
            'blog_posts'          => BlogPost::where('status', 'published')->count(),
            'cases'               => CaseStudy::whereNotNull('published_at')->count(),
            'leads'               => Lead::count(),
            'new_leads'           => Lead::where('status', 'new')->count(),
        ];

        $recent_clients = Client::with('company')
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recent_clients'));
    }
}
