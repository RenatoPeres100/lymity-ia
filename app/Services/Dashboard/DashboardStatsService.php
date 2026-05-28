<?php

namespace App\Services\Dashboard;

use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Client;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * Admin user stats — single DB round-trip instead of 5 separate count queries.
     */
    public function getUserStats(User $actor): array
    {
        $rows = User::visibleTo($actor)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'active') as active,
                SUM(status IN ('inactive','blocked')) as inactive,
                SUM(user_type IN ('internal','agency')) as internal,
                SUM(user_type = 'client') as clients
            ")
            ->first();

        return [
            'total'    => (int) ($rows->total    ?? 0),
            'active'   => (int) ($rows->active   ?? 0),
            'inactive' => (int) ($rows->inactive  ?? 0),
            'internal' => (int) ($rows->internal  ?? 0),
            'clients'  => (int) ($rows->clients   ?? 0),
        ];
    }

    /**
     * Client dashboard stats — single query grouped by status.
     */
    public function getClientStats(User $user): array
    {
        $clientId = $user->client_id;

        if (!$clientId) {
            return ['pending_approvals' => 0, 'changes_requested' => 0, 'approved_history' => 0];
        }

        $rows = ApprovalRequest::where('client_id', $clientId)
            ->selectRaw("
                SUM(status = 'pending') as pending,
                SUM(status = 'changes_requested') as changes_requested,
                SUM(status = 'approved') as approved_history
            ")
            ->first();

        return [
            'pending_approvals'  => (int) ($rows->pending           ?? 0),
            'changes_requested'  => (int) ($rows->changes_requested ?? 0),
            'approved_history'   => (int) ($rows->approved_history  ?? 0),
            'contents_in_creation' => 0,
            'active_campaigns'   => 0,
            'reports_available'  => 0,
        ];
    }

    /**
     * Operation dashboard stats — one query per count, scoped.
     */
    public function getOperationStats(User $user): array
    {
        return [
            'approvals_pending'  => ApprovalRequest::visibleTo($user)->where('status', 'pending')->count(),
            'approvals_critical' => ApprovalRequest::visibleTo($user)->where('status', 'pending')->where('sensitive_level', 'critical')->count(),
            'blog_pending'       => BlogPost::visibleTo($user)->where('status', 'pending_approval')->count(),
            'posts_scheduled'    => SocialPost::visibleTo($user)->where('status', 'scheduled')->count(),
        ];
    }

    /**
     * Client counts for admin clients index — single aggregated query.
     */
    public function getClientIndexStats(User $actor): array
    {
        $rows = Client::visibleTo($actor)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'active') as active,
                SUM(status = 'inactive') as inactive,
                SUM(status = 'archived') as archived
            ")
            ->first();

        return [
            'total'    => (int) ($rows->total    ?? 0),
            'active'   => (int) ($rows->active   ?? 0),
            'inactive' => (int) ($rows->inactive ?? 0),
            'archived' => (int) ($rows->archived ?? 0),
        ];
    }
}
