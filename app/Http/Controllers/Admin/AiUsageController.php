<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentTask;
use App\Models\AiEmployee;
use App\Models\AiProviderCall;
use App\Models\AiUsageRecord;
use App\Services\AI\AICostGuardService;
use App\Services\Ai\AiCostService;
use Illuminate\Http\Request;

class AiUsageController extends Controller
{
    public function __construct(
        private AiCostService      $costService,
        private AICostGuardService $costGuard,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        // New usage records (from agent tasks)
        $usageQuery = AiUsageRecord::with(['aiEmployee', 'agentTask'])->orderByDesc('created_at');
        if ($user->role !== 'admin_geral' && $user->company_id) {
            $usageQuery->where('company_id', $user->company_id);
        }
        if ($request->filled('employee')) {
            $usageQuery->where('ai_employee_id', $request->employee);
        }
        if ($request->filled('task')) {
            $usageQuery->where('agent_task_id', $request->task);
        }
        if ($request->filled('period')) {
            match ($request->period) {
                'today' => $usageQuery->whereDate('created_at', today()),
                'week'  => $usageQuery->where('created_at', '>=', now()->startOfWeek()),
                default => $usageQuery->where('created_at', '>=', now()->startOfMonth()),
            };
        }

        $records = $usageQuery->paginate(30)->withQueryString();

        $daily   = $this->costGuard->getDailyUsage();
        $monthly = $this->costGuard->getMonthlyUsage();

        $employees = AiEmployee::where('active', true)->orderBy('name')->get();
        $tasks     = AgentTask::orderBy('title')->get();

        $byEmployee = AiUsageRecord::selectRaw('ai_employee_id, SUM(input_tokens) as total_input, SUM(output_tokens) as total_output, SUM(estimated_cost_usd) as total_cost, COUNT(*) as requests')
            ->where('created_at', '>=', now()->startOfMonth())
            ->when($user->company_id, fn($q) => $q->where('company_id', $user->company_id))
            ->groupBy('ai_employee_id')
            ->with('aiEmployee')
            ->get();

        $byTask = AiUsageRecord::selectRaw('agent_task_id, SUM(estimated_cost_usd) as total_cost, COUNT(*) as requests')
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereNotNull('agent_task_id')
            ->when($user->company_id, fn($q) => $q->where('company_id', $user->company_id))
            ->groupBy('agent_task_id')
            ->with('agentTask')
            ->get();

        return view('admin.ai-usage.index', compact(
            'records', 'daily', 'monthly', 'employees', 'tasks', 'byEmployee', 'byTask'
        ));
    }

    /** Legacy usage view (AiProviderCall) */
    public function legacyIndex(Request $request)
    {
        $query = AiProviderCall::with(['aiEmployee', 'aiTask', 'client', 'user'])
            ->orderByDesc('created_at');

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        $calls   = $query->paginate(50)->withQueryString();
        $summary = $this->costService->getUsageSummary();

        $totalCostAllTime  = (float) AiProviderCall::where('provider', '!=', 'mock')->sum('estimated_cost');
        $totalCallsAllTime = AiProviderCall::count();
        $successRate       = $totalCallsAllTime > 0
            ? round(AiProviderCall::where('status', 'success')->count() / $totalCallsAllTime * 100, 1)
            : 100;

        return view('admin.ai-settings.usage', compact(
            'calls', 'summary', 'totalCostAllTime', 'totalCallsAllTime', 'successRate',
        ));
    }
}
