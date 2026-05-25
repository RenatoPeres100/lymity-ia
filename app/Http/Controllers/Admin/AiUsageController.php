<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProviderCall;
use App\Services\Ai\AiCostService;
use Illuminate\Http\Request;

class AiUsageController extends Controller
{
    public function __construct(private AiCostService $costService) {}

    public function index(Request $request)
    {
        $query = AiProviderCall::with(['aiEmployee', 'aiTask', 'client', 'user'])
            ->orderByDesc('created_at');

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $calls   = $query->paginate(50)->withQueryString();
        $summary = $this->costService->getUsageSummary();

        // Additional stats
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
