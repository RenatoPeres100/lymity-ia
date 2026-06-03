<?php

namespace App\Console\Commands;

use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\Approvals\ApprovalEmailNotificationService;
use Illuminate\Console\Command;

class TestApprovalEmailCommand extends Command
{
    protected $signature   = 'approvals:test-email {email : E-mail de destino para o teste}';
    protected $description = 'Send a test approval notification email using a real pending approval (or creates a temporary one)';

    public function handle(ApprovalEmailNotificationService $service): int
    {
        $emailTo = $this->argument('email');

        // 1. Find the recipient user by email (or admin geral)
        $recipient = User::where('email', $emailTo)->first()
            ?? User::where('role', 'admin_geral')->first();

        if (!$recipient) {
            $this->error("Nenhum usuário encontrado para o e-mail: {$emailTo}");
            return 1;
        }

        // 2. Pick a real pending approval with content attached, or any pending one
        $approval = ApprovalRequest::with(['client', 'aiEmployee', 'approvable'])
            ->where('status', 'pending')
            ->whereNotNull('approvable_id')
            ->latest()
            ->first();

        // Fallback: any pending approval
        $approval ??= ApprovalRequest::with(['client', 'aiEmployee', 'approvable'])
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Last resort: create a temporary test approval linked to a real blog post
        $temporary = false;
        if (!$approval) {
            $post = BlogPost::latest()->first();
            $approval = ApprovalRequest::create([
                'title'           => 'Aprovação de teste — ' . now()->format('d/m/Y H:i'),
                'description'     => 'Aprovação criada automaticamente pelo comando approvals:test-email para validar o fluxo de e-mail.',
                'approval_type'   => $post ? 'blog' : 'other',
                'approvable_type' => $post ? BlogPost::class : null,
                'approvable_id'   => $post?->id,
                'status'          => 'pending',
                'sensitive_level' => 'medium',
                'requested_by'    => $recipient->id,
            ]);
            $approval->load(['client', 'aiEmployee', 'approvable']);
            $temporary = true;
        }

        $this->info("Usando aprovação #{$approval->id}: {$approval->title}");
        $this->info("Destinatário: {$recipient->name} <{$recipient->email}>");
        $this->line("MAIL_MAILER: " . config('mail.default'));

        // 3. Send using the real service (force = true to bypass dedup check)
        $service->sendCreatedNotification($approval, force: true);

        $this->info('E-mail de teste enviado com sucesso.');

        if ($temporary) {
            $approval->delete();
            $this->line('(Aprovação temporária removida após envio.)');
        }

        return 0;
    }
}
