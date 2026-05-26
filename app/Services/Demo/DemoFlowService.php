<?php

namespace App\Services\Demo;

use App\Models\ActivityLog;
use App\Models\AdAccount;
use App\Models\AdCampaign;
use App\Models\AiEmployee;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Budget;
use App\Models\Client;
use App\Models\ClientBrandProfile;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\User;
use Carbon\Carbon;

class DemoFlowService
{
    private array $steps = [];

    // IDs collected during flow for output
    public int $socialPostId      = 0;
    public string $socialApprovalStatus  = '';
    public string $socialPostStatus      = '';
    public int $adCampaignId      = 0;
    public string $campaignApprovalStatus = '';
    public int $blogPostId        = 0;
    public string $blogApprovalStatus    = '';
    public int $budgetId          = 0;
    public string $budgetStatus          = '';
    public int $aiReportTaskId    = 0;
    public int $activityLogsCount = 0;

    public function run(?string $clientName = null): array
    {
        $this->steps = [];

        $client = $clientName
            ? Client::where('name', 'like', "%{$clientName}%")->first()
            : Client::where('status', 'active')->first();

        if (!$client) {
            return ['success' => false, 'error' => 'Nenhum cliente ativo encontrado.', 'steps' => []];
        }

        $agencyAdmin = User::where('role', 'agencia_admin')->where('status', 'active')->first()
            ?? User::where('role', 'super_admin')->first();

        if (!$agencyAdmin) {
            return ['success' => false, 'error' => 'Nenhum usuário admin encontrado.', 'steps' => []];
        }

        $clientAdmin = User::where('client_id', $client->id)
            ->where('user_type', 'client')
            ->where('status', 'active')
            ->first() ?? $agencyAdmin;

        $socialEmployee   = AiEmployee::where('role_key', 'social_media_ai')->first() ?? AiEmployee::where('status', 'active')->first();
        $trafficEmployee  = AiEmployee::where('role_key', 'traffic_manager_ai')->first();
        $seoEmployee      = AiEmployee::where('role_key', 'seo_ai')->first();
        $analystEmployee  = AiEmployee::where('role_key', 'analyst_ai')->first();

        // 1 — Cliente demo localizado
        $this->step(1, 'Cliente localizado', "Cliente '{$client->name}' (id={$client->id}) selecionado.", 'success');

        // 2 — Perfil da marca
        $this->step2_brandProfile($client, $agencyAdmin);

        // 3 — Social Media IA gera post
        $post = $this->step3_generatePost($client, $socialEmployee, $agencyAdmin);

        // 4 — Post vai para ApprovalRequest
        $postApproval = $this->step4_createApprovalRequest($post, $client, $agencyAdmin, $socialEmployee, 'post', "post#{$post->id}");

        // 5 — Cliente aprova post
        $this->step5_approve($postApproval, $clientAdmin, 5, 'Cliente aprova post', "Post #{$post->id}");
        $this->socialApprovalStatus = $postApproval->fresh()->status;

        // 6 — Post agendado
        $this->step6_schedulePost($post, $agencyAdmin);
        $this->socialPostStatus = $post->fresh()->status;
        $this->socialPostId     = $post->id;

        // 7 — Gestor de Tráfego IA gera campanha sandbox
        $campaign = $this->step7_generateCampaign($client, $trafficEmployee, $agencyAdmin);
        $this->adCampaignId = $campaign->id;

        // 8 — Campanha vai para ApprovalRequest
        $campaignApproval = $this->step4_createApprovalRequest($campaign, $client, $agencyAdmin, $trafficEmployee, 'campaign', "campaign#{$campaign->id}", 8);

        // 9 — Cliente aprova campanha
        $this->step5_approve($campaignApproval, $clientAdmin, 9, 'Cliente aprova campanha', "Campanha #{$campaign->id}");
        $this->campaignApprovalStatus = $campaignApproval->fresh()->status;

        // 10 — SEO IA gera blog post
        $blogPost = $this->step10_generateBlogPost($client, $seoEmployee, $agencyAdmin);
        $this->blogPostId = $blogPost->id;

        // 11 — Blog vai para ApprovalRequest
        $blogApproval = $this->step4_createApprovalRequest($blogPost, $client, $agencyAdmin, $seoEmployee, 'blog', "blog#{$blogPost->id}", 11);

        // 12 — Admin/cliente aprova blog
        $this->step5_approve($blogApproval, $agencyAdmin, 12, 'Admin aprova blog post', "BlogPost #{$blogPost->id}");
        $this->blogApprovalStatus = $blogApproval->fresh()->status;

        // 13 — Criar orçamento
        $budget = $this->step13_createBudget($client, $agencyAdmin);
        $this->budgetId = $budget->id;

        // 14 — Cliente aprova orçamento
        $this->step14_approveBudget($budget, $clientAdmin);
        $this->budgetStatus = $budget->fresh()->status;

        // 15 — Analista IA gera relatório mock
        $reportTask = $this->step15_generateReport($client, $analystEmployee, $agencyAdmin);
        $this->aiReportTaskId = $reportTask->id;

        // 16 — Tudo aparece em ActivityLog
        $this->step16_logAll($client, $agencyAdmin, $post, $campaign, $blogPost, $budget, $reportTask);

        // 17 — Admin visualiza logs
        $this->step17_adminLogs($client);

        // 18 — Resumo final
        $this->step18_summary($client, $post, $postApproval, $campaign, $blogPost, $budget, $reportTask);

        $this->activityLogsCount = ActivityLog::where('client_id', $client->id)->count();

        return [
            'success' => true,
            'client'  => $client->name,
            'steps'   => $this->steps,
        ];
    }

    private function step2_brandProfile(Client $client, User $admin): void
    {
        $profile = ClientBrandProfile::firstOrCreate(
            ['client_id' => $client->id],
            [
                'brand_name'        => $client->name,
                'brand_positioning' => 'Empresa inovadora com foco em resultados.',
                'tone_of_voice'     => 'Profissional, moderno e próximo.',
                'target_audience'   => 'Empreendedores e gestores B2B.',
                'main_offer'        => 'Soluções digitais completas.',
            ]
        );

        $this->step(2, 'Perfil da marca', "ClientBrandProfile #{$profile->id} criado/atualizado para '{$client->name}'", 'success');
    }

    private function step3_generatePost(Client $client, ?AiEmployee $employee, User $admin): SocialPost
    {
        $channel = SocialChannel::where('client_id', $client->id)->first();

        $post = SocialPost::create([
            'client_id'         => $client->id,
            'company_id'        => $client->company_id,
            'created_by'        => $admin->id,
            'ai_employee_id'    => $employee?->id,
            'title'             => "[Demo] Post IA — {$client->name}",
            'objective'         => 'engagement',
            'content_type'      => 'feed',
            'main_caption'      => "🚀 Conteúdo gerado por IA para {$client->name}.\n\n✅ Aprovado via fluxo Lymity IA\n\n#Marketing #IA #Automação",
            'hashtags'          => '#Marketing #IA #Automação #LymityIA',
            'cta'               => 'Saiba mais no link da bio',
            'status'            => 'pending_approval',
            'requires_approval' => true,
            'metadata'          => ['demo_flow' => true, 'ai_model' => 'mock', 'channel_id' => $channel?->id],
        ]);

        $this->step(3, 'Social IA gera post', "Social Media IA criou post #{$post->id} para '{$client->name}'", 'success');

        return $post;
    }

    private function step4_createApprovalRequest(
        $approvable,
        Client $client,
        User $requester,
        ?AiEmployee $employee,
        string $type,
        string $label,
        int $stepNum = 4
    ): ApprovalRequest {
        $approval = ApprovalRequest::create([
            'client_id'       => $client->id,
            'requested_by'    => $requester->id,
            'ai_employee_id'  => $employee?->id,
            'approvable_type' => get_class($approvable),
            'approvable_id'   => $approvable->id,
            'title'           => "[Demo] Aprovação de {$label}",
            'description'     => "Aguarda aprovação antes da execução.",
            'approval_type'   => $type,
            'status'          => 'pending',
            'sensitive_level' => 'low',
            'payload'         => ['demo_flow' => true, 'label' => $label],
            'due_at'          => Carbon::now()->addHours(24),
        ]);

        $this->step($stepNum, "ApprovalRequest ({$label})", "ApprovalRequest #{$approval->id} criada para {$label}", 'success');

        return $approval;
    }

    private function step5_approve(ApprovalRequest $approval, User $approver, int $stepNum, string $title, string $label): void
    {
        $approval->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->step($stepNum, $title, "ApprovalRequest #{$approval->id} aprovada por '{$approver->name}' para {$label}", 'success');
    }

    private function step6_schedulePost(SocialPost $post, User $admin): void
    {
        $scheduledAt = Carbon::now()->addHours(2);
        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'approved_by'  => $admin->id,
            'approved_at'  => now(),
        ]);

        $this->step(6, 'Post agendado', "Post #{$post->id} agendado para {$scheduledAt->format('d/m/Y H:i')}", 'success');
    }

    private function step7_generateCampaign(Client $client, ?AiEmployee $employee, User $admin): AdCampaign
    {
        $adAccount = AdAccount::where('client_id', $client->id)->first();

        $campaign = AdCampaign::create([
            'client_id'         => $client->id,
            'company_id'        => $client->company_id,
            'ad_account_id'     => $adAccount?->id,
            'created_by'        => $admin->id,
            'ai_employee_id'    => $employee?->id,
            'platform'          => 'google_ads',
            'name'              => "[Demo] Campanha sandbox — {$client->name}",
            'objective'         => 'awareness',
            'status'            => 'draft',
            'daily_budget'      => 50.00,
            'total_budget'      => 1500.00,
            'start_date'        => Carbon::now()->addDays(7),
            'end_date'          => Carbon::now()->addDays(37),
            'strategy_summary'  => "Campanha sandbox gerada por Gestor de Tráfego IA para demonstração.",
            'requires_approval' => true,
        ]);

        $this->step(7, 'Tráfego IA gera campanha', "AdCampaign #{$campaign->id} criada em modo sandbox para '{$client->name}'", 'success');

        return $campaign;
    }

    private function step10_generateBlogPost(Client $client, ?AiEmployee $employee, User $admin): BlogPost
    {
        $category = \App\Models\BlogCategory::first();

        $slug = 'demo-blog-' . $client->id . '-' . time();

        $blogPost = BlogPost::create([
            'client_id'     => $client->id,
            'author_id'     => $admin->id,
            'category_id'   => $category?->id,
            'type'          => 'client',
            'title'         => "[Demo] Blog IA — {$client->name}",
            'slug'          => $slug,
            'excerpt'       => 'Post de demonstração gerado por SEO IA para validação do fluxo.',
            'content'       => '<p>Conteúdo gerado por SEO IA para <strong>' . $client->name . '</strong>. Este é um post de demonstração.</p>',
            'status'        => 'draft',
            'focus_keyword' => 'marketing digital',
        ]);

        $this->step(10, 'SEO IA gera blog post', "BlogPost #{$blogPost->id} criado como draft para '{$client->name}'", 'success');

        return $blogPost;
    }

    private function step13_createBudget(Client $client, User $admin): Budget
    {
        $budget = Budget::create([
            'client_id'    => $client->id,
            'title'        => "[Demo] Orçamento — {$client->name}",
            'description'  => 'Orçamento de demonstração criado durante auditoria do fluxo.',
            'month'        => now()->month,
            'year'         => now()->year,
            'status'       => 'pending_approval',
            'total_amount' => 3500.00,
        ]);

        $this->step(13, 'Orçamento criado', "Budget #{$budget->id} criado (R\$ 3.500,00) para '{$client->name}'", 'success');

        return $budget;
    }

    private function step14_approveBudget(Budget $budget, User $approver): void
    {
        $budget->update(['status' => 'approved']);

        $this->step(14, 'Cliente aprova orçamento', "Budget #{$budget->id} aprovado por '{$approver->name}'", 'success');
    }

    private function step15_generateReport(Client $client, ?AiEmployee $employee, User $admin): AiTask
    {
        $task = AiTask::create([
            'ai_employee_id'    => $employee?->id,
            'client_id'         => $client->id,
            'created_by'        => $admin->id,
            'title'             => "[Demo] Relatório mock — {$client->name}",
            'description'       => 'Analista IA gera relatório de insights mock para demonstração.',
            'task_type'         => 'generate_report',
            'type'              => 'generate_report',
            'status'            => 'completed',
            'priority'          => 'low',
            'requires_approval' => false,
            'sensitive_action'  => false,
            'output'            => json_encode([
                'demo_flow'   => true,
                'client'      => $client->name,
                'insights'    => [
                    'Posts publicados este mês: 5',
                    'Taxa de engajamento média: 4.2%',
                    'Campanha com maior ROI: Google Ads Search',
                    'Palavra-chave de maior volume: marketing digital',
                ],
                'generated_at' => now()->toIso8601String(),
            ]),
            'finished_at'       => now(),
        ]);

        $this->step(15, 'Analista IA gera relatório', "AiTask #{$task->id} concluída com insights mock para '{$client->name}'", 'success');

        return $task;
    }

    private function step16_logAll(
        Client $client,
        User $admin,
        SocialPost $post,
        AdCampaign $campaign,
        BlogPost $blogPost,
        Budget $budget,
        AiTask $reportTask
    ): void {
        $actions = [
            ['demo_flow_post_approved',     SocialPost::class,   $post->id,        "Post #{$post->id} aprovado e agendado."],
            ['demo_flow_campaign_approved',  AdCampaign::class,   $campaign->id,    "Campanha #{$campaign->id} aprovada (sandbox)."],
            ['demo_flow_blog_approved',      BlogPost::class,     $blogPost->id,    "Blog #{$blogPost->id} aprovado."],
            ['demo_flow_budget_approved',    Budget::class,       $budget->id,      "Orçamento #{$budget->id} aprovado."],
            ['demo_flow_report_generated',   AiTask::class,       $reportTask->id,  "Relatório #{$reportTask->id} gerado por Analista IA."],
        ];

        foreach ($actions as [$action, $subjectType, $subjectId, $description]) {
            ActivityLog::create([
                'client_id'    => $client->id,
                'user_id'      => $admin->id,
                'action'       => $action,
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'description'  => "[Demo Flow] {$description}",
                'metadata'     => ['demo_flow' => true, 'client_id' => $client->id],
                'ip_address'   => '127.0.0.1',
                'level'        => 'info',
            ]);
        }

        $count = ActivityLog::where('client_id', $client->id)->count();
        $this->step(16, 'ActivityLogs registrados', "{$count} logs registrados para empresa do cliente '{$client->name}'", 'success');
    }

    private function step17_adminLogs(Client $client): void
    {
        $recentLogs = ActivityLog::where('client_id', $client->id)
            ->orderByDesc('id')
            ->take(3)
            ->get(['action', 'level', 'description']);

        $summary = $recentLogs->map(fn($l) => $l->action)->implode(', ');
        $this->step(17, 'Admin visualiza logs', "Últimos logs: {$summary}", 'success');
    }

    private function step18_summary(
        Client $client,
        SocialPost $post,
        ApprovalRequest $postApproval,
        AdCampaign $campaign,
        BlogPost $blogPost,
        Budget $budget,
        AiTask $reportTask
    ): void {
        $summary = implode(' | ', [
            "Cliente: {$client->name}",
            "Post #{$post->id}: {$post->fresh()->status}",
            "Campanha #{$campaign->id}: {$campaign->fresh()->status}",
            "Blog #{$blogPost->id}: {$blogPost->fresh()->status}",
            "Budget #{$budget->id}: {$budget->fresh()->status}",
            "ReportTask #{$reportTask->id}: {$reportTask->status}",
            "Fluxo: " . count($this->steps) . " etapas",
        ]);

        $this->step(18, 'Demo concluído', $summary, 'success');
    }

    private function step(int $number, string $title, string $detail, string $status): void
    {
        $this->steps[] = [
            'step'   => $number,
            'title'  => $title,
            'detail' => $detail,
            'status' => $status,
        ];
    }
}
