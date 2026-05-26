<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\SocialPost;

class OperationController extends Controller
{
    public function index()
    {
        $isRealProvider = config('ai.provider') !== 'mock' && config('ai.real_enabled');

        // Contadores rápidos
        $stats = [
            'blog_pending'        => BlogPost::whereIn('status', ['draft', 'pending_review'])->count(),
            'instagram_pending'   => SocialPost::where('status', 'pending_approval')->count(),
            'approvals_pending'   => ApprovalRequest::where('status', 'pending')->count(),
            'scheduled_posts'     => SocialPost::where('status', 'scheduled')->where('scheduled_at', '>=', now())->count(),
            'ai_employees_active' => AiEmployee::where('status', 'active')->count(),
            'ai_failures'         => AiTask::where('status', 'failed')
                ->where('updated_at', '>=', now()->subDay())
                ->count(),
        ];

        $nextScheduled = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();

        // Posts de blog pendentes (últimos 10)
        $blogPending = BlogPost::whereIn('status', ['draft', 'pending_review'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Posts Instagram pendentes
        $instagramPending = SocialPost::where('status', 'pending_approval')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Aprovações pendentes
        $approvalsPending = ApprovalRequest::where('status', 'pending')
            ->with('requestedBy', 'client')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        // Publicações agendadas
        $scheduledPosts = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(8)
            ->get();

        // Tarefas IA recentes
        $aiTasks = AiTask::with('aiEmployee')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Funcionários IA ativos
        $aiEmployees = AiEmployee::where('status', 'active')
            ->take(6)
            ->get();

        // Logs operacionais recentes
        $recentLogs = ActivityLog::whereIn('action', [
            'ai_generation_completed', 'ai_generation_failed', 'approved', 'rejected',
            'post_published', 'post_scheduled', 'blog_published',
            'demo_flow_post_approved', 'demo_flow_campaign_approved',
        ])
        ->orderByDesc('created_at')
        ->take(10)
        ->get();

        // Falhas recentes (IA)
        $recentFailures = AiTask::where('status', 'failed')
            ->with('aiEmployee')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        // Status do provider de IA
        $aiProvider = config('ai.provider', 'mock');
        $aiRealEnabled = config('ai.real_enabled', false);

        return view('admin.operation.index', compact(
            'stats',
            'nextScheduled',
            'blogPending',
            'instagramPending',
            'approvalsPending',
            'scheduledPosts',
            'aiTasks',
            'aiEmployees',
            'recentLogs',
            'recentFailures',
            'aiProvider',
            'aiRealEnabled',
            'isRealProvider'
        ));
    }
}
