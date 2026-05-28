<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardStatsService $dashStats) {}

    public function index(): View
    {
        $user   = Auth::user();
        $client = $user->client;

        // Single aggregated query replaces 3 separate count queries
        $stats = $this->dashStats->getClientStats($user);

        $pending_approvals = $user->client_id
            ? ApprovalRequest::where('client_id', $user->client_id)
                ->where('status', 'pending')
                ->with(['requester'])
                ->latest()
                ->take(5)
                ->get()
            : collect();

        return view('client.dashboard', compact('stats', 'client', 'user', 'pending_approvals'));
    }
}
