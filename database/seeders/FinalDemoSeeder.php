<?php

namespace Database\Seeders;

use App\Models\AdAccount;
use App\Models\AdCampaign;
use App\Models\AiEmployee;
use App\Models\AiMemory;
use App\Models\AiTask;
use App\Models\AiWorkSchedule;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\CampaignMetric;
use App\Models\Client;
use App\Models\ClientBrandProfile;
use App\Models\ClientContract;
use App\Models\ClientFolder;
use App\Models\ClientKnowledgeBase;
use App\Models\ClientWebsite;
use App\Models\ClientWebsitePage;
use App\Models\Company;
use App\Models\ExternalFile;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\SeoAudit;
use App\Models\SeoCluster;
use App\Models\SeoKeyword;
use App\Models\SocialCalendar;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\StorageIntegration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FinalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('email', 'contato@lymity.local')->first();
        $admin   = User::where('email', 'admin@lymity.local')->first();

        $clients = Client::whereIn('name', ['Cliente Demonstração', 'Cliente Demo B2B'])->get()->keyBy('name');
        $client1 = $clients->get('Cliente Demonstração');
        $client2 = $clients->get('Cliente Demo B2B');

        $socialAI   = AiEmployee::where('role_key', 'social_media_ai')->first();
        $trafficAI  = AiEmployee::where('role_key', 'traffic_manager_ai')->first();
        $seoAI      = AiEmployee::where('role_key', 'seo_ai')->first();
        $copyAI     = AiEmployee::where('role_key', 'copywriter_ai')->first();
        $analystAI  = AiEmployee::where('role_key', 'analyst_ai')->first();
        $pmAI       = AiEmployee::where('role_key', 'project_manager_ai')->first();

        foreach ([$client1, $client2] as $client) {
            if (!$client) continue;
            $this->seedClient($client, $company, $admin, $socialAI, $trafficAI, $seoAI, $copyAI, $analystAI, $pmAI);
        }
    }

    private function seedClient($client, $company, $admin, $socialAI, $trafficAI, $seoAI, $copyAI, $analystAI, $pmAI): void
    {
        $cid  = $client->id;
        $slug = Str::slug($client->name);

        // Brand profile
        ClientBrandProfile::updateOrCreate(
            ['client_id' => $cid],
            [
                'tone_of_voice'   => $client->brand_voice,
                'target_audience' => $client->target_audience,
                'visual_style'    => 'Minimalista, moderno e profissional. Paleta: #4a6cf7 / #0f172a. Tipografia: Inter + Poppins.',
                'notes'           => 'Perfil de marca atualizado via FinalDemoSeeder.',
            ]
        );

        // Website — table has: client_id, domain, platform (enum: internal/wordpress/external), status
        $website = ClientWebsite::updateOrCreate(
            ['client_id' => $cid],
            [
                'domain'   => $client->website ?? "https://{$slug}.local",
                'platform' => 'internal',
                'status'   => 'active',
                'notes'    => "Site principal de {$client->name}",
            ]
        );

        // Website pages — table uses client_website_id (not client_id) + slug unique
        if ($website) {
            ClientWebsitePage::updateOrCreate(
                ['client_website_id' => $website->id, 'slug' => 'home'],
                ['title' => 'Home', 'page_type' => 'home', 'status' => 'published', 'created_by' => $admin?->id]
            );
            ClientWebsitePage::updateOrCreate(
                ['client_website_id' => $website->id, 'slug' => 'sobre'],
                ['title' => 'Sobre', 'page_type' => 'about', 'status' => 'published', 'created_by' => $admin?->id]
            );
        }

        // Knowledge base — table has: client_id, title, content, source, status (no type)
        ClientKnowledgeBase::updateOrCreate(
            ['client_id' => $cid, 'title' => "Briefing {$client->name}"],
            [
                'content' => "Briefing completo do cliente {$client->name}. Segmento: {$client->segment}. Público: {$client->target_audience}.",
                'source'  => 'internal',
                'status'  => 'active',
            ]
        );

        // Folders
        foreach (['Materiais de Marca', 'Criativos', 'Relatórios'] as $folderName) {
            ClientFolder::updateOrCreate(
                ['client_id' => $cid, 'name' => $folderName],
                ['local_path' => "client-files/{$cid}/" . Str::slug($folderName)]
            );
        }

        // Storage integration
        StorageIntegration::updateOrCreate(
            ['client_id' => $cid, 'provider' => 'local'],
            ['status' => 'active']
        );

        // Social channels — table has: platform, account_name, account_url, status (no name/handle)
        foreach (['instagram', 'facebook', 'linkedin'] as $platform) {
            SocialChannel::updateOrCreate(
                ['client_id' => $cid, 'platform' => $platform],
                [
                    'company_id'   => $client->company_id,
                    'account_name' => ucfirst($platform) . " {$client->name}",
                    'account_url'  => "https://{$platform}.com/{$slug}",
                    'status'       => 'active',
                ]
            );
        }

        // Social calendar — table has: client_id, company_id, month, year, strategy_summary, status (no name)
        $calendar = SocialCalendar::updateOrCreate(
            ['client_id' => $cid, 'month' => now()->month, 'year' => now()->year],
            [
                'company_id'       => $client->company_id,
                'strategy_summary' => "Calendário editorial de {$client->name} para " . now()->format('m/Y') . ".",
                'status'           => 'active',
            ]
        );

        // Social posts — content_type enum: feed|reels|story|carousel|short_video|article|thread
        $postData = [
            ['title' => "Novidade: {$client->name} apresenta nova solução", 'status' => 'draft'],
            ['title' => "Dica da semana — {$client->segment}",              'status' => 'pending_approval'],
            ['title' => "Case de sucesso {$client->name}",                  'status' => 'approved'],
            ['title' => "Post agendado para amanhã",                        'status' => 'scheduled',  'scheduled_at' => now()->addDay()],
            ['title' => "Campanha especial — destaque do mês",              'status' => 'published',  'published_at' => now()->subDay()],
        ];

        foreach ($postData as $pd) {
            SocialPost::updateOrCreate(
                ['client_id' => $cid, 'title' => $pd['title']],
                [
                    'company_id'     => $client->company_id,
                    'ai_employee_id' => $socialAI?->id,
                    'content_type'   => 'feed',
                    'objective'      => 'engagement',
                    'main_caption'   => "Conteúdo demo para {$client->name}: {$pd['title']}",
                    'status'         => $pd['status'],
                    'scheduled_at'   => $pd['scheduled_at'] ?? null,
                    'published_at'   => $pd['published_at'] ?? null,
                    'created_by'     => $admin?->id,
                ]
            );
        }

        // ApprovalRequests — approval_type enum: post|campaign|budget|blog|website_page|proposal|external_action|ai_task|other
        $approvalScenarios = [
            ['type' => 'post',     'title' => "Aprovação: Post Instagram {$client->name}", 'status' => 'pending',           'level' => 'low'],
            ['type' => 'campaign', 'title' => "Aprovação: Campanha Google Ads",            'status' => 'approved',          'level' => 'high'],
            ['type' => 'budget',   'title' => "Aprovação: Orçamento Q1 {$client->name}",  'status' => 'rejected',          'level' => 'high'],
            ['type' => 'proposal', 'title' => "Aprovação: Proposta Comercial",             'status' => 'changes_requested', 'level' => 'medium'],
            ['type' => 'ai_task',  'title' => "Aprovação: Tarefa IA Sensível",             'status' => 'pending',           'level' => 'critical'],
        ];

        foreach ($approvalScenarios as $sc) {
            ApprovalRequest::updateOrCreate(
                ['client_id' => $cid, 'title' => $sc['title']],
                [
                    'requested_by'   => $admin?->id,
                    'approval_type'  => $sc['type'],
                    'description'    => "Aprovação demo para {$client->name}. Status: {$sc['status']}.",
                    'status'         => $sc['status'],
                    'sensitive_level'=> $sc['level'],
                ]
            );
        }

        // SEO keywords — table: keyword, search_intent, priority (low/medium/high), difficulty, volume, status (planned/in_progress/used/archived)
        foreach ([
            ["{$client->segment} melhor",    'high',   'commercial'],
            ["contratar {$client->segment}", 'high',   'transactional'],
            ["{$client->segment} preço",     'medium', 'commercial'],
            ["{$client->segment} online",    'low',    'informational'],
        ] as [$kw, $priority, $intent]) {
            SeoKeyword::updateOrCreate(
                ['client_id' => $cid, 'keyword' => $kw],
                [
                    'company_id'    => $client->company_id,
                    'priority'      => $priority,
                    'search_intent' => $intent,
                    'status'        => 'planned',
                    'volume'        => rand(500, 5000),
                    'difficulty'    => rand(20, 80),
                ]
            );
        }

        // SEO cluster — needs main_keyword (required), no pillar_topic
        SeoCluster::updateOrCreate(
            ['client_id' => $cid, 'name' => "Cluster Principal {$client->name}"],
            [
                'company_id'   => $client->company_id,
                'main_keyword' => $client->segment,
                'description'  => "Cluster de conteúdo para o segmento {$client->segment}.",
            ]
        );

        // SEO audit — columns: website_url, title, summary, score, status (draft/completed/archived). No company_id or issues_count.
        SeoAudit::updateOrCreate(
            ['client_id' => $cid, 'website_url' => $client->website ?? "https://{$slug}.local"],
            [
                'title'   => "Auditoria SEO — {$client->name}",
                'summary' => "Auditoria demo para {$client->name}. Score: " . rand(55, 90) . "/100.",
                'score'   => rand(55, 90),
                'status'  => 'completed',
            ]
        );

        // Blog posts — no ai_employee_id or company_id column; uses author_id
        BlogPost::updateOrCreate(
            ['client_id' => $cid, 'slug' => Str::slug("5-tendencias-{$slug}-" . now()->year)],
            [
                'title'        => "5 tendências em {$client->segment} para " . now()->year,
                'author_id'    => $admin?->id,
                'excerpt'      => "Confira as principais tendências do segmento {$client->segment}.",
                'content'      => "<p>Conteúdo demo sobre tendências em {$client->segment}. Gerado pelo Copywriter IA da Lymity.</p>",
                'status'       => 'published',
                'type'         => 'client',
                'published_at' => now()->subDays(3),
            ]
        );

        BlogPost::updateOrCreate(
            ['client_id' => $cid, 'slug' => Str::slug("como-{$slug}-atende-clientes")],
            [
                'title'     => "Como {$client->name} atende seus clientes",
                'author_id' => $admin?->id,
                'excerpt'   => "Conheça os diferenciais de {$client->name}.",
                'content'   => "<p>Artigo demo sobre os serviços de {$client->name}.</p>",
                'status'    => 'draft',
                'type'      => 'client',
            ]
        );

        // Ad accounts — table has: account_name (not name), external_account_id (not account_id), no currency
        $googleAccount = AdAccount::updateOrCreate(
            ['client_id' => $cid, 'platform' => 'google_ads'],
            [
                'company_id'          => $client->company_id,
                'account_name'        => "Google Ads — {$client->name}",
                'external_account_id' => 'demo-' . $cid . '-google',
                'status'              => 'active',
            ]
        );

        AdAccount::updateOrCreate(
            ['client_id' => $cid, 'platform' => 'meta_ads'],
            [
                'company_id'          => $client->company_id,
                'account_name'        => "Meta Ads — {$client->name}",
                'external_account_id' => 'demo-' . $cid . '-meta',
                'status'              => 'active',
            ]
        );

        // Campaign — uses daily_budget/total_budget (not budget/budget_type)
        $campaign = AdCampaign::updateOrCreate(
            ['client_id' => $cid, 'name' => "Campanha Awareness — {$client->name}"],
            [
                'company_id'     => $client->company_id,
                'ad_account_id'  => $googleAccount?->id,
                'ai_employee_id' => $trafficAI?->id,
                'platform'       => 'google_ads',
                'objective'      => 'awareness',
                'status'         => 'active',
                'total_budget'   => 3000.00,
                'start_date'     => now()->startOfMonth()->toDateString(),
            ]
        );

        if ($campaign) {
            CampaignMetric::updateOrCreate(
                ['ad_campaign_id' => $campaign->id, 'date' => now()->subDays(1)->toDateString()],
                [
                    'impressions' => rand(5000, 20000),
                    'clicks'      => rand(200, 800),
                    'cost'        => round(rand(50, 200) + rand(0, 99) / 100, 2),
                    'leads'       => rand(5, 30),
                    'ctr'         => round(rand(2, 8) + rand(0, 99) / 100, 2),
                    'cpc'         => round(rand(1, 5) + rand(0, 99) / 100, 2),
                    'roas'        => round(rand(200, 500) / 100, 2),
                ]
            );
        }

        // Proposal — no company_id or notes column; status enum includes 'sent'
        $proposal = Proposal::updateOrCreate(
            ['client_id' => $cid, 'title' => "Proposta Gestão Digital — {$client->name}"],
            [
                'ai_employee_id' => $analystAI?->id,
                'created_by'     => $admin?->id,
                'status'         => 'sent',
                'valid_until'    => now()->addDays(30)->toDateString(),
                'description'    => "Proposta demo de gestão digital para {$client->name}.",
            ]
        );

        if ($proposal) {
            // ProposalItem uses 'name' not 'description' as identifier
            ProposalItem::updateOrCreate(
                ['proposal_id' => $proposal->id, 'name' => 'Gestão de Social Media'],
                ['quantity' => 1, 'unit_price' => 2500.00, 'total_price' => 2500.00]
            );
            ProposalItem::updateOrCreate(
                ['proposal_id' => $proposal->id, 'name' => 'Gestão de Tráfego Pago'],
                ['quantity' => 1, 'unit_price' => 1500.00, 'total_price' => 1500.00]
            );
        }

        // Budget — no company_id, created_by, valid_until, notes; has month/year
        $budget = Budget::updateOrCreate(
            ['client_id' => $cid, 'title' => "Orçamento Mensal — {$client->name}"],
            [
                'month'  => now()->month,
                'year'   => now()->year,
                'status' => 'draft',
            ]
        );

        if ($budget) {
            // BudgetItem uses name, amount (not description/quantity/unit_price/total_price)
            BudgetItem::updateOrCreate(
                ['budget_id' => $budget->id, 'name' => 'Verba de Mídia Google'],
                ['category' => 'media', 'amount' => 3000.00, 'status' => 'active']
            );
        }

        // Contract — no company_id, created_by, starts_at, ends_at, value; status: draft|pending_signature|signed|canceled|archived
        ClientContract::updateOrCreate(
            ['client_id' => $cid, 'title' => "Contrato de Serviços — {$client->name}"],
            [
                'description' => "Contrato demo de gestão digital para {$client->name}.",
                'status'      => 'signed',
            ]
        );

        // AI Tasks — task_type column exists from phase 4 migration; status: pending_approval (not waiting_approval)
        AiTask::updateOrCreate(
            ['client_id' => $cid, 'title' => "Gerar posts Instagram — {$client->name}"],
            [
                'ai_employee_id'    => $socialAI?->id,
                'created_by'        => $admin?->id,
                'task_type'         => 'social_post_generation',
                'status'            => 'completed',
                'priority'          => 'normal',
                'requires_approval' => false,
                'sensitive_action'  => false,
                'input_payload'     => ['client' => $client->name, 'platform' => 'instagram'],
                'output_payload'    => ['posts_generated' => 5],
                'finished_at'       => now()->subHours(2),
            ]
        );

        AiTask::updateOrCreate(
            ['client_id' => $cid, 'title' => "Análise SEO — {$client->name}"],
            [
                'ai_employee_id'    => $seoAI?->id,
                'created_by'        => $admin?->id,
                'task_type'         => 'seo_audit',
                'status'            => 'pending_approval',
                'priority'          => 'high',
                'requires_approval' => true,
                'sensitive_action'  => true,
                'input_payload'     => ['url' => $client->website ?? "https://{$slug}.local"],
            ]
        );

        // ExternalFiles (no physical file)
        ExternalFile::updateOrCreate(
            ['client_id' => $cid, 'name' => "Manual da Marca — {$client->name}.pdf"],
            [
                'file_type'    => 'pdf',
                'mime_type'    => 'application/pdf',
                'external_url' => 'https://example.com/manual-demo.pdf',
                'source'       => 'imported',
                'uploaded_by'  => $admin?->id,
                'notes'        => 'Arquivo demo — sem arquivo físico real.',
            ]
        );
    }
}
