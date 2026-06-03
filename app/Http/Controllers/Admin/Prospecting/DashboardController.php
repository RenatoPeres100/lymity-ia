<?php

namespace App\Http\Controllers\Admin\Prospecting;

use App\Http\Controllers\Controller;
use App\Models\ProspectActivity;
use App\Models\ProspectLead;
use App\Models\ProspectMessageSuggestion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        abort_unless($user->hasPermission('prospecting.view'), 403);

        $baseLeads      = ProspectLead::visibleTo($user);
        $baseActivities = ProspectActivity::visibleTo($user);

        $stats = [
            'open'               => (clone $baseLeads)->where('status', 'open')->count(),
            'hot'                => (clone $baseLeads)->where('status', 'open')->where('interest_level', 'hot')->count(),
            'meeting_scheduled'  => (clone $baseLeads)->where('status', 'open')
                ->whereHas('stage', fn($q) => $q->where('key', 'meeting_scheduled'))->count(),
            'proposal_sent'      => (clone $baseLeads)->where('status', 'open')
                ->whereHas('stage', fn($q) => $q->where('key', 'proposal_sent'))->count(),
            'won'                => (clone $baseLeads)->where('status', 'won')->count(),
            'lost'               => (clone $baseLeads)->where('status', 'lost')->count(),
            'overdue_followups'  => (clone $baseLeads)->where('status', 'open')
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '<', now())
                ->count(),
            'upcoming_followups' => (clone $baseLeads)->where('status', 'open')
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '>=', now())
                ->where('next_follow_up_at', '<=', now()->addDays(7))
                ->count(),
        ];

        $upcomingActivities = (clone $baseActivities)
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->limit(8)
            ->with('lead')
            ->get();

        $topLeads = (clone $baseLeads)
            ->where('status', 'open')
            ->whereNotNull('fit_score')
            ->orderByDesc('fit_score')
            ->limit(5)
            ->with('stage')
            ->get();

        $recentMessages = ProspectMessageSuggestion::visibleTo($user)
            ->where('status', 'draft')
            ->orderByDesc('created_at')
            ->limit(5)
            ->with('lead')
            ->get();

        $recentLeads = (clone $baseLeads)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->with('stage')
            ->get();

        return view('admin.prospecting.dashboard', compact(
            'stats', 'upcomingActivities', 'topLeads', 'recentMessages', 'recentLeads'
        ));
    }
}
