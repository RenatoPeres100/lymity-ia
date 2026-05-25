<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin_geral')->first();
        $client = Client::first();
        $clientUser = User::where('user_type', 'client')->first();
        $aiEmployee = AiEmployee::first();

        if (!$admin) return;

        $adminId = $admin->id;
        $clientId = $client?->id;
        $clientUserId = $clientUser?->id;
        $aiId = $aiEmployee?->id;

        $logs = [
            // Info logs
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'post.created',
                'module'        => 'social_media',
                'level'         => 'info',
                'description'   => 'Post social criado para cliente: Campanha Verão 2026',
                'metadata'      => ['post_id' => 1, 'platform' => 'instagram'],
                'ip_address'    => '127.0.0.1',
            ],
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'approval.created',
                'module'        => 'approvals',
                'level'         => 'info',
                'description'   => 'Solicitação de aprovação criada para post social',
                'metadata'      => ['approval_type' => 'social_post'],
                'ip_address'    => '127.0.0.1',
            ],
            // Success logs
            [
                'user_id'       => $clientUserId ?? $adminId,
                'client_id'     => $clientId,
                'action'        => 'approval.approved',
                'module'        => 'approvals',
                'level'         => 'success',
                'description'   => 'Aprovação concedida: Post Instagram aprovado pelo cliente',
                'metadata'      => ['approval_id' => 1, 'approved_by' => $clientUserId ?? $adminId],
                'ip_address'    => '192.168.1.10',
            ],
            [
                'user_id'       => $adminId,
                'ai_employee_id'=> $aiId,
                'client_id'     => $clientId,
                'action'        => 'ai_task.executed',
                'module'        => 'ai',
                'level'         => 'success',
                'description'   => 'Tarefa IA executada com sucesso: Geração de legenda para post',
                'metadata'      => ['task_type' => 'caption_generation', 'tokens_used' => 312],
                'ip_address'    => '127.0.0.1',
            ],
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'campaign.created',
                'module'        => 'campaigns',
                'level'         => 'success',
                'description'   => 'Campanha Google Ads criada: Brand Awareness Q2',
                'metadata'      => ['platform' => 'google_ads', 'daily_budget' => 150.00],
                'ip_address'    => '127.0.0.1',
            ],
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'budget.created',
                'module'        => 'commercial',
                'level'         => 'success',
                'description'   => 'Orçamento criado: Pacote Social Media + SEO — R$ 3.500',
                'metadata'      => ['budget_value' => 3500.00, 'services' => ['social_media', 'seo']],
                'ip_address'    => '127.0.0.1',
            ],
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'proposal.accepted',
                'module'        => 'commercial',
                'level'         => 'success',
                'description'   => 'Proposta aceita pelo cliente: Proposta Onboarding Digital',
                'metadata'      => ['proposal_id' => 1, 'value' => 5000.00],
                'ip_address'    => '192.168.1.10',
            ],
            // Warning logs
            [
                'user_id'       => $adminId,
                'ai_employee_id'=> $aiId,
                'client_id'     => $clientId,
                'action'        => 'ai_task.limit_reached',
                'module'        => 'ai',
                'level'         => 'warning',
                'description'   => 'Limite de execuções diárias atingido para o funcionário IA: Social Media IA',
                'metadata'      => ['limit' => 10, 'executed_today' => 10],
                'ip_address'    => '127.0.0.1',
            ],
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'approval.pending_timeout',
                'module'        => 'approvals',
                'level'         => 'warning',
                'description'   => 'Aprovação pendente há mais de 48 horas sem resposta do cliente',
                'metadata'      => ['approval_id' => 2, 'pending_hours' => 52],
                'ip_address'    => '127.0.0.1',
            ],
            // Error log
            [
                'user_id'       => $adminId,
                'ai_employee_id'=> $aiId,
                'client_id'     => $clientId,
                'action'        => 'ai_task.failed',
                'module'        => 'ai',
                'level'         => 'error',
                'description'   => 'Falha na execução de tarefa IA: timeout ao conectar com API externa',
                'metadata'      => ['error' => 'Connection timeout after 30s', 'task_type' => 'seo_analysis'],
                'ip_address'    => '127.0.0.1',
            ],
            // Critical log
            [
                'user_id'       => $adminId,
                'client_id'     => null,
                'action'        => 'security.login_failed_multiple',
                'module'        => 'security',
                'level'         => 'critical',
                'description'   => 'Múltiplas tentativas de login falhas detectadas: possível ataque de força bruta',
                'metadata'      => ['ip' => '198.51.100.42', 'attempts' => 15, 'username' => 'admin@test.com'],
                'ip_address'    => '198.51.100.42',
            ],
            // Additional info logs for volume
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'keyword.created',
                'module'        => 'seo',
                'level'         => 'info',
                'description'   => 'Keyword SEO adicionada: "agência digital premium"',
                'metadata'      => ['keyword' => 'agência digital premium', 'intent' => 'transactional'],
                'ip_address'    => '127.0.0.1',
            ],
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'contract.signed',
                'module'        => 'commercial',
                'level'         => 'success',
                'description'   => 'Contrato assinado digitalmente: Contrato Mensal Social Media',
                'metadata'      => ['contract_id' => 1, 'value' => 2800.00],
                'ip_address'    => '192.168.1.10',
            ],
            [
                'user_id'       => $adminId,
                'client_id'     => $clientId,
                'action'        => 'blog_post.published',
                'module'        => 'seo',
                'level'         => 'success',
                'description'   => 'Artigo SEO publicado: "Como aumentar tráfego orgânico em 2026"',
                'metadata'      => ['slug' => 'aumentar-trafego-organico-2026', 'words' => 1420],
                'ip_address'    => '127.0.0.1',
            ],
            [
                'user_id'       => $adminId,
                'action'        => 'user.login',
                'module'        => 'auth',
                'level'         => 'info',
                'description'   => 'Login realizado com sucesso',
                'metadata'      => ['browser' => 'Chrome 124'],
                'ip_address'    => '192.168.1.1',
            ],
        ];

        foreach ($logs as $log) {
            ActivityLog::create(array_merge($log, [
                'created_at' => now()->subMinutes(rand(0, 10080)),
                'updated_at' => now()->subMinutes(rand(0, 10080)),
            ]));
        }
    }
}
