<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\SocialPost;
use App\Services\Reports\ExecutiveReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private ExecutiveReportService $report) {}

    public function index(): View
    {
        $user    = Auth::user();
        $summary = $this->report->adminSummary($user);

        $recentApprovals = ApprovalRequest::visibleTo($user)
            ->with(['client', 'requester'])
            ->orderByDesc('created_at')->limit(5)->get();

        $recentTasks = AiTask::visibleTo($user)
            ->with('aiEmployee')
            ->orderByDesc('created_at')->limit(5)->get();

        $criticalLogs = ActivityLog::visibleTo($user)
            ->whereIn('level', ['error', 'critical'])
            ->orderByDesc('created_at')->limit(5)->get();

        $scheduledPosts = SocialPost::visibleTo($user)
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')->limit(5)->get();

        return view('admin.dashboard', compact(
            'summary', 'recentApprovals', 'recentTasks', 'criticalLogs', 'scheduledPosts'
        ));
    }
}
