<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with(['user', 'client'])
            ->where(function ($q) {
                $q->whereIn('level', ['warning', 'error', 'critical'])
                  ->orWhereIn('action', [
                      'login', 'logout', 'login_failed', 'unauthorized_access',
                      'sensitive_action', 'budget_changed', 'proposal_sent',
                      'approval_rejected', 'approval_critical',
                  ]);
            })
            ->orderByDesc('created_at');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('action', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }

        $logs = $query->paginate(50)->withQueryString();

        $criticalApprovals = ApprovalRequest::where('sensitive_level', 'critical')
            ->orderByDesc('created_at')->limit(10)->get();

        return view('admin.security-logs', compact('logs', 'criticalApprovals'));
    }
}
