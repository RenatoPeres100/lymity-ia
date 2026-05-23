<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user   = Auth::user();
        $client = $user->client;

        $clientId = $client?->id;

        $stats = [
            'contents_in_creation'  => 0,
            'pending_approvals'     => $clientId ? ApprovalRequest::where('client_id', $clientId)->where('status', 'pending')->count() : 0,
            'changes_requested'     => $clientId ? ApprovalRequest::where('client_id', $clientId)->where('status', 'changes_requested')->count() : 0,
            'approved_history'      => $clientId ? ApprovalRequest::where('client_id', $clientId)->where('status', 'approved')->count() : 0,
            'active_campaigns'      => 0,
            'reports_available'     => 0,
        ];

        $pending_approvals = $clientId
            ? ApprovalRequest::where('client_id', $clientId)->where('status', 'pending')->latest()->take(5)->get()
            : collect();

        return view('client.dashboard', compact('stats', 'client', 'user', 'pending_approvals'));
    }
}
