<?php

namespace App\Http\Controllers;

use App\Models\AiEmployee;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        // Client users should always be in their own area
        if ($user && $user->isClientUser()) {
            return redirect()->route('client.dashboard');
        }

        $stats = [
            'total_clients'              => Client::where('status', 'active')->count(),
            'total_users'                => User::where('status', 'active')->where('user_type', '!=', 'ai')->count(),
            'ai_employees'               => AiEmployee::where('status', 'active')->count(),
            'pending_approvals'          => ApprovalRequest::where('status', 'pending')->count(),
            'critical_approvals'         => ApprovalRequest::where('status', 'pending')->where('sensitive_level', 'critical')->count(),
            'overdue_approvals'          => ApprovalRequest::where('status', 'pending')->where('due_at', '<', now())->count(),
            'approved_this_month'        => ApprovalRequest::where('status', 'approved')->whereMonth('approved_at', now()->month)->count(),
            'planned_posts'              => BlogPost::where('status', 'pending_approval')->count(),
            'campaigns_analysis'         => 0,
            'blog_posts'                 => BlogPost::where('status', 'published')->count(),
            'cases'                      => CaseStudy::whereNotNull('published_at')->count(),
            'leads'                      => Lead::count(),
            'new_leads'                  => Lead::where('status', 'new')->count(),
        ];

        $recent_clients = Client::with('company')
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        $urgent_approvals = ApprovalRequest::with(['client', 'aiEmployee'])
            ->where('status', 'pending')
            ->whereIn('sensitive_level', ['critical', 'high'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recent_clients', 'urgent_approvals'));
    }
}
