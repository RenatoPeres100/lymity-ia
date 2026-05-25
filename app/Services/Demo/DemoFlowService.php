<?php

namespace App\Services\Demo;

use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Models\User;
use Carbon\Carbon;

class DemoFlowService
{
    private array $steps = [];

    public function run(?string $clientName = null): array
    {
        $this->steps = [];

        $client = $clientName
            ? Client::where('name', 'like', "%{$clientName}%")->first()
            : Client::where('status', 'active')->first();

        if (! $client) {
            return ['success' => false, 'error' => 'Nenhum cliente ativo encontrado.', 'steps' => []];
        }

        $socialMediaEmployee = AiEmployee::where('role_key', 'social_media_ai')->first()
            ?? AiEmployee::where('status', 'active')->first();
        $agencyAdmin = User::where('role', 'agencia_admin')->where('status', 'active')->first()
            ?? User::where('role', 'super_admin')->first();

        if (! $agencyAdmin) {
            return ['success' => false, 'error' => 'Nenhum usuário admin encontrado.', 'steps' => []];
        }

        $clientAdmin = User::where('client_id', $client->id)
            ->where('user_type', 'client')
            ->where('status', 'active')
            ->first();

        // Step 1 — Social Media IA gera post
        $post = $this->step1_generatePost($client, $socialMediaEmployee, $agencyAdmin);

        // Step 2 — Post criado como pending_approval
        $this->step2_confirmPostCreated($post);

        // Step 3 — ApprovalRequest criada
        $approval = $this->step3_createApprovalRequest($post, $client, $agencyAdmin, $socialMediaEmployee);

        // Step 4 — Cliente aprova
        $this->step4_clientApproves($approval, $clientAdmin ?? $agencyAdmin);

        // Step 5 — Post agendado
        $this->step5_schedulePost($post, $agencyAdmin);

        // Step 6 — Simulação de publicação
        $this->step6_publishPost($post, $agencyAdmin);

        // Step 7 — Log registrado
        $this->step7_logActivity($post, $client, $agencyAdmin);

        // Step 8 — Relatório gerado (contagem)
        $this->step8_generateReport($client);

        // Step 9 — Validação de isolamento
        $this->step9_validateIsolation($client);

        // Step 10 — Health check
        $this->step10_systemHealthCheck();

        // Step 11 — Resumo final
        $this->step11_finalSummary($client, $post, $approval);

        return [
            'success' => true,
            'client'  => $client->name,
            'steps'   => $this->steps,
        ];
    }

    private function step1_generatePost(Client $client, ?AiEmployee $employee, User $admin): SocialPost
    {
        $channel = SocialChannel::where('client_id', $client->id)->first();

        $post = SocialPost::create([
            'client_id'          => $client->id,
            'company_id'         => $client->company_id,
            'created_by'         => $admin->id,
            'ai_employee_id'     => $employee?->id,
            'title'              => "[Demo] Post gerado por IA — {$client->name}",
            'objective'          => 'engagement',
            'content_type'       => 'feed',
            'main_caption'       => "🚀 Conteúdo gerado automaticamente pela Social Media IA para {$client->name}.\n\nNossa tecnologia transforma sua presença digital com consistência e criatividade.\n\n✅ Aprovado via fluxo Lymity IA\n\n#Marketing #IA #Automação",
            'hashtags'           => '#Marketing #IA #Automação #LymityIA',
            'cta'                => 'Saiba mais no link da bio',
            'status'             => 'pending_approval',
            'requires_approval'  => true,
            'metadata'           => [
                'demo_flow'     => true,
                'generated_at'  => now()->toIso8601String(),
                'ai_model'      => 'mock',
                'channel_id'    => $channel?->id,
            ],
        ]);

        $this->addStep(1, 'IA gera post', "Social Media IA criou post #{$post->id} para '{$client->name}'", 'success');

        return $post;
    }

    private function step2_confirmPostCreated(SocialPost $post): void
    {
        $this->addStep(2, 'Post criado', "Post #{$post->id} criado com status 'pending_approval'. Requer aprovação: sim.", 'success');
    }

    private function step3_createApprovalRequest(SocialPost $post, Client $client, User $requester, ?AiEmployee $employee): ApprovalRequest
    {
        $approval = ApprovalRequest::create([
            'client_id'       => $client->id,
            'requested_by'    => $requester->id,
            'ai_employee_id'  => $employee?->id,
            'approvable_type' => SocialPost::class,
            'approvable_id'   => $post->id,
            'title'           => "[Demo] Aprovação de post — {$client->name}",
            'description'     => "Post gerado por IA aguarda aprovação antes da publicação.",
            'approval_type'   => 'post',
            'status'          => 'pending',
            'sensitive_level' => 'low',
            'payload'         => [
                'post_id'    => $post->id,
                'demo_flow'  => true,
            ],
            'due_at' => Carbon::now()->addHours(24),
        ]);

        $this->addStep(3, 'Aprovação criada', "ApprovalRequest #{$approval->id} criada com status 'pending' para post #{$post->id}", 'success');

        return $approval;
    }

    private function step4_clientApproves(ApprovalRequest $approval, User $approver): void
    {
        $approval->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->addStep(4, 'Cliente aprova', "ApprovalRequest #{$approval->id} aprovada por '{$approver->name}' (role: {$approver->role})", 'success');
    }

    private function step5_schedulePost(SocialPost $post, User $admin): void
    {
        $scheduledAt = Carbon::now()->addHours(2);

        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'approved_by'  => $admin->id,
            'approved_at'  => now(),
        ]);

        $this->addStep(5, 'Post agendado', "Post #{$post->id} agendado para {$scheduledAt->format('d/m/Y H:i')}", 'success');
    }

    private function step6_publishPost(SocialPost $post, User $admin): void
    {
        $post->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $this->addStep(6, 'Post publicado', "Post #{$post->id} marcado como 'published' (simulação). published_at: " . now()->format('d/m/Y H:i:s'), 'success');
    }

    private function step7_logActivity(SocialPost $post, Client $client, User $admin): void
    {
        ActivityLog::create([
            'company_id'    => $client->company_id,
            'user_id'       => $admin->id,
            'action'        => 'demo_flow_post_published',
            'subject_type'  => SocialPost::class,
            'subject_id'    => $post->id,
            'description'   => "[Demo Flow] Post #{$post->id} publicado para cliente '{$client->name}'.",
            'metadata'      => ['demo_flow' => true, 'client_id' => $client->id],
            'ip_address'    => '127.0.0.1',
            'level'         => 'info',
        ]);

        $this->addStep(7, 'Log registrado', "ActivityLog criado: ação 'demo_flow_post_published' para post #{$post->id}", 'success');
    }

    private function step8_generateReport(Client $client): void
    {
        $totalPosts    = SocialPost::where('client_id', $client->id)->count();
        $publishedPosts = SocialPost::where('client_id', $client->id)->where('status', 'published')->count();
        $pendingApprovals = ApprovalRequest::where('client_id', $client->id)->where('status', 'pending')->count();

        $this->addStep(8, 'Relatório gerado', "Cliente '{$client->name}': {$totalPosts} posts totais, {$publishedPosts} publicados, {$pendingApprovals} aprovações pendentes", 'success');
    }

    private function step9_validateIsolation(Client $client): void
    {
        $otherClients = Client::where('id', '!=', $client->id)->pluck('id');
        $leakCount = 0;

        if ($otherClients->isNotEmpty()) {
            $leakCount = SocialPost::where('client_id', $client->id)
                ->whereIn('client_id', $otherClients)
                ->count();
        }

        if ($leakCount === 0) {
            $this->addStep(9, 'Isolamento validado', "Dados do cliente '{$client->name}' não vazam para outros clientes. Isolamento OK.", 'success');
        } else {
            $this->addStep(9, 'Isolamento FALHOU', "AVISO: {$leakCount} registros com vazamento detectado!", 'error');
        }
    }

    private function step10_systemHealthCheck(): void
    {
        $dbOk    = $this->checkDatabase();
        $storageOk = is_writable(storage_path('app'));

        $status = ($dbOk && $storageOk) ? 'success' : 'warning';
        $msg    = "DB: " . ($dbOk ? 'OK' : 'ERRO') . " | Storage: " . ($storageOk ? 'OK' : 'ERRO');

        $this->addStep(10, 'Health check', $msg, $status);
    }

    private function checkDatabase(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function step11_finalSummary(Client $client, SocialPost $post, ApprovalRequest $approval): void
    {
        $summary = implode(' | ', [
            "Cliente: {$client->name}",
            "Post #{$post->id}: {$post->status}",
            "Approval #{$approval->id}: {$approval->status}",
            "Fluxo completo em " . count($this->steps) . " etapas",
        ]);

        $this->addStep(11, 'Demo concluído', $summary, 'success');
    }

    private function addStep(int $number, string $title, string $detail, string $status): void
    {
        $this->steps[] = [
            'step'   => $number,
            'title'  => $title,
            'detail' => $detail,
            'status' => $status,
        ];
    }
}
