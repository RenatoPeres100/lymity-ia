<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\SocialPost;
use App\Services\Reports\ExecutiveReportService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private ExecutiveReportService $report) {}

    public function index(): View
    {
        $summary = $this->report->adminSummary();

        $recentApprovals = ApprovalRequest::with(['client', 'requester'])
            ->orderByDesc('created_at')->limit(5)->get();

        $recentTasks = AiTask::with('aiEmployee')
            ->orderByDesc('created_at')->limit(5)->get();

        $criticalLogs = ActivityLog::whereIn('level', ['error', 'critical'])
            ->orderByDesc('created_at')->limit(5)->get();

        $scheduledPosts = SocialPost::whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')->limit(5)->get();

        return view('admin.dashboard', compact(
            'summary', 'recentApprovals', 'recentTasks', 'criticalLogs', 'scheduledPosts'
        ));
    }
}
