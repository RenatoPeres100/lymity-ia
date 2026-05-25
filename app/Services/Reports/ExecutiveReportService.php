<?php

namespace App\Services\Reports;

use App\Models\ActivityLog;
use App\Models\AdCampaign;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\AiMemory;
use App\Models\AiWorkSchedule;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Budget;
use App\Models\CampaignMetric;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\SeoAudit;
use App\Models\SeoCluster;
use App\Models\SeoKeyword;
use App\Models\SocialPost;
use App\Models\User;

class ExecutiveReportService
{
    public function adminSummary(): array
    {
        return [
            'clients'              => Client::count(),
            'users_active'         => User::where('status', 'active')->count(),
            'ai_employees_active'  => AiEmployee::where('status', 'active')->count(),
            'ai_tasks_today'       => AiTask::whereDate('created_at', today())->count(),
            'ai_tasks_total'       => AiTask::count(),
            'approvals_pending'    => ApprovalRequest::where('status', 'pending')->count(),
            'approvals_critical'   => ApprovalRequest::where('status', 'pending')->where('sensitive_level', 'critical')->count(),
            'approvals_total'      => ApprovalRequest::count(),
            'social_posts'         => SocialPost::count(),
            'campaigns'            => AdCampaign::count(),
            'campaigns_active'     => AdCampaign::where('status', 'active')->count(),
            'blog_posts_published' => BlogPost::where('status', 'published')->count(),
            'blog_posts_total'     => BlogPost::count(),
            'proposals'            => Proposal::count(),
            'proposals_accepted'   => Proposal::where('status', 'accepted')->count(),
            'budgets'              => Budget::count(),
            'seo_keywords'         => SeoKeyword::count(),
            'logs_total'           => ActivityLog::count(),
            'logs_critical'        => ActivityLog::whereIn('level', ['error', 'critical'])->count(),
            'logs_today'           => ActivityLog::whereDate('created_at', today())->count(),
        ];
    }

    public function clientSummary(Client $client): array
    {
        $cid = $client->id;

        return [
            'approvals_pending'  => ApprovalRequest::where('client_id', $cid)->where('status', 'pending')->count(),
            'approvals_total'    => ApprovalRequest::where('client_id', $cid)->count(),
            'social_posts'       => SocialPost::where('client_id', $cid)->count(),
            'social_scheduled'   => SocialPost::where('client_id', $cid)->whereNotNull('scheduled_at')->count(),
            'campaigns'          => AdCampaign::where('client_id', $cid)->count(),
            'campaigns_active'   => AdCampaign::where('client_id', $cid)->where('status', 'active')->count(),
            'blog_posts'         => BlogPost::where('client_id', $cid)->count(),
            'seo_keywords'       => SeoKeyword::where('client_id', $cid)->count(),
            'proposals'          => Proposal::where('client_id', $cid)->count(),
            'proposals_accepted' => Proposal::where('client_id', $cid)->where('status', 'accepted')->count(),
            'budgets'            => Budget::where('client_id', $cid)->count(),
        ];
    }

    public function socialReport(?Client $client = null): array
    {
        $q = fn() => $client ? SocialPost::where('client_id', $client->id) : SocialPost::query();

        return [
            'total'     => $q()->count(),
            'draft'     => $q()->where('status', 'draft')->count(),
            'scheduled' => $q()->whereNotNull('scheduled_at')->where('status', 'approved')->count(),
            'published' => $q()->where('status', 'published')->count(),
            'pending'   => $q()->where('status', 'pending_approval')->count(),
            'by_type'   => $q()->selectRaw('content_type, count(*) as total')->groupBy('content_type')->pluck('total', 'content_type')->toArray(),
            'recent'    => $q()->with('client')->orderByDesc('created_at')->limit(10)->get(),
        ];
    }

    public function campaignReport(?Client $client = null): array
    {
        $q = fn() => $client ? AdCampaign::where('client_id', $client->id) : AdCampaign::query();

        $metrics = $client
            ? CampaignMetric::whereHas('campaign', fn ($sq) => $sq->where('client_id', $client->id))
            : CampaignMetric::query();

        return [
            'total'          => $q()->count(),
            'active'         => $q()->where('status', 'active')->count(),
            'paused'         => $q()->where('status', 'paused')->count(),
            'completed'      => $q()->where('status', 'completed')->count(),
            'by_platform'    => $q()->selectRaw('platform, count(*) as total')->groupBy('platform')->pluck('total', 'platform')->toArray(),
            'total_impressions' => (int) $metrics->clone()->sum('impressions'),
            'total_clicks'   => (int) $metrics->clone()->sum('clicks'),
            'total_cost'     => (float) $metrics->clone()->sum('cost'),
            'total_leads'    => (int) $metrics->clone()->sum('leads'),
            'avg_ctr'        => round((float) $metrics->clone()->avg('ctr'), 2),
            'avg_cpc'        => round((float) $metrics->clone()->avg('cpc'), 2),
            'avg_roas'       => round((float) $metrics->clone()->avg('roas'), 2),
            'recent'         => $q()->with('metrics')->orderByDesc('created_at')->limit(10)->get(),
        ];
    }

    public function seoReport(?Client $client = null): array
    {
        $qk = fn() => $client ? SeoKeyword::where('client_id', $client->id) : SeoKeyword::query();
        $qc = fn() => $client ? SeoCluster::where('client_id', $client->id) : SeoCluster::query();
        $qa = fn() => $client ? SeoAudit::where('client_id', $client->id) : SeoAudit::query();
        $qb = fn() => $client ? BlogPost::where('client_id', $client->id) : BlogPost::query();

        return [
            'keywords_total'   => $qk()->count(),
            'keywords_active'  => $qk()->where('status', 'active')->count(),
            'by_priority'      => $qk()->selectRaw('priority, count(*) as total')->groupBy('priority')->pluck('total', 'priority')->toArray(),
            'clusters_total'   => $qc()->count(),
            'audits_total'     => $qa()->count(),
            'avg_score'        => round((float) $qa()->avg('score'), 1),
            'blog_published'   => $qb()->where('status', 'published')->count(),
            'blog_total'       => $qb()->count(),
            'recent_keywords'  => $qk()->orderByDesc('created_at')->limit(10)->get(),
            'recent_audits'    => $qa()->orderByDesc('created_at')->limit(5)->get(),
        ];
    }

    public function aiReport(): array
    {
        return [
            'employees_total'  => AiEmployee::count(),
            'employees_active' => AiEmployee::where('status', 'active')->count(),
            'tasks_total'      => AiTask::count(),
            'tasks_queued'     => AiTask::where('status', 'queued')->count(),
            'tasks_running'    => AiTask::where('status', 'running')->count(),
            'tasks_done'       => AiTask::where('status', 'done')->count(),
            'tasks_failed'     => AiTask::where('status', 'failed')->count(),
            'tasks_today'      => AiTask::whereDate('created_at', today())->count(),
            'by_type'          => AiTask::selectRaw('task_type, count(*) as total')->groupBy('task_type')->pluck('total', 'task_type')->toArray(),
            'memories'         => AiMemory::count(),
            'schedules_active' => AiWorkSchedule::where('active', true)->count(),
            'recent_tasks'     => AiTask::with('aiEmployee')->orderByDesc('created_at')->limit(10)->get(),
            'recent_errors'    => AiTask::where('status', 'failed')->orderByDesc('created_at')->limit(5)->get(),
        ];
    }

    public function approvalReport(?Client $client = null): array
    {
        $q = fn() => $client ? ApprovalRequest::where('client_id', $client->id) : ApprovalRequest::query();

        return [
            'total'              => $q()->count(),
            'pending'            => $q()->where('status', 'pending')->count(),
            'approved'           => $q()->where('status', 'approved')->count(),
            'rejected'           => $q()->where('status', 'rejected')->count(),
            'changes_requested'  => $q()->where('status', 'changes_requested')->count(),
            'critical'           => $q()->where('sensitive_level', 'critical')->count(),
            'overdue'            => $q()->where('status', 'pending')->where('due_at', '<', now())->count(),
            'by_type'            => $q()->selectRaw('approval_type, count(*) as total')->groupBy('approval_type')->pluck('total', 'approval_type')->toArray(),
            'by_level'           => $q()->selectRaw('sensitive_level, count(*) as total')->groupBy('sensitive_level')->pluck('total', 'sensitive_level')->toArray(),
            'recent'             => $q()->with(['client', 'requester'])->orderByDesc('created_at')->limit(10)->get(),
        ];
    }
}
