<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Auth;

class OperationController extends Controller
{
    public function index()
    {
        $user           = Auth::user();
        $isRealProvider = config('ai.provider') !== 'mock' && config('ai.real_enabled');

        $stats = [
            'blog_pending'        => BlogPost::visibleTo($user)->whereIn('status', ['draft', 'pending_review'])->count(),
            'instagram_pending'   => SocialPost::visibleTo($user)->where('status', 'pending_approval')->count(),
            'approvals_pending'   => ApprovalRequest::visibleTo($user)->where('status', 'pending')->count(),
            'scheduled_posts'     => SocialPost::visibleTo($user)->where('status', 'scheduled')->where('scheduled_at', '>=', now())->count(),
            'ai_employees_active' => AiEmployee::where('status', 'active')->count(),
            'ai_failures'         => AiTask::visibleTo($user)->where('status', 'failed')
                ->where('updated_at', '>=', now()->subDay())
                ->count(),
        ];

        $nextScheduled = SocialPost::visibleTo($user)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();

        $blogPending = BlogPost::visibleTo($user)
            ->whereIn('status', ['draft', 'pending_review'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $instagramPending = SocialPost::visibleTo($user)
            ->where('status', 'pending_approval')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $approvalsPending = ApprovalRequest::visibleTo($user)
            ->where('status', 'pending')
            ->with('requestedBy', 'client')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $scheduledPosts = SocialPost::visibleTo($user)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(8)
            ->get();

        $aiTasks = AiTask::visibleTo($user)
            ->with('aiEmployee')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $aiEmployees = AiEmployee::where('status', 'active')
            ->take(6)
            ->get();

        $recentLogs = ActivityLog::visibleTo($user)
            ->whereIn('action', [
                'ai_generation_completed', 'ai_generation_failed', 'approved', 'rejected',
                'post_published', 'post_scheduled', 'blog_published',
                'demo_flow_post_approved', 'demo_flow_campaign_approved',
            ])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $recentFailures = AiTask::visibleTo($user)
            ->where('status', 'failed')
            ->with('aiEmployee')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $aiProvider    = config('ai.provider', 'mock');
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
