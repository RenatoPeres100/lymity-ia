<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTaskLog;
use App\Models\AiUsageRecord;

class BlogAiLogController extends Controller
{
    public function index()
    {
        $logs = AiTaskLog::whereIn('task_type', ['generate_brief', 'generate_draft'])
            ->with('employee')
            ->orderByDesc('id')
            ->paginate(30);

        $usageRecords = AiUsageRecord::with('agent')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $totalCostMonth = AiUsageRecord::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('estimated_cost_usd');

        $totalCallsToday = AiUsageRecord::whereDate('created_at', today())->count();

        return view('admin.blog.ai-logs.index', compact(
            'logs', 'usageRecords', 'totalCostMonth', 'totalCallsToday'
        ));
    }
}
