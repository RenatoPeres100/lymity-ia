<?php

namespace App\Console\Commands;

use App\Mail\ApprovalRequestCreatedMail;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestApprovalEmailCommand extends Command
{
    protected $signature   = 'approvals:test-email {email : E-mail de destino para o teste}';
    protected $description = 'Send a test approval notification email (does not create a real approval)';

    public function handle(): int
    {
        $emailTo = $this->argument('email');

        $this->info("Enviando e-mail de teste para: {$emailTo}");
        $this->line("MAIL_MAILER: " . env('MAIL_MAILER', 'log'));

        // Build a mock approval and user without saving to DB
        $approval = new ApprovalRequest([
            'id'             => 0,
            'title'          => 'Teste de notificação por e-mail',
            'description'    => 'Este é um e-mail de teste gerado pelo comando approvals:test-email. Nenhuma aprovação real foi criada.',
            'approval_type'  => 'blog',
            'status'         => 'pending',
            'sensitive_level'=> 'medium',
        ]);
        $approval->created_at = now();

        $recipient = new User([
            'id'   => 0,
            'name' => 'Destinatário de Teste',
            'email'=> $emailTo,
            'role' => 'admin_geral',
        ]);

        $panelUrl  = config('app.url') . '/admin/approvals';
        $actionUrls = [
            'approve'         => config('app.url') . '/approval-email/0/confirm/approve?test=1',
            'request_changes' => config('app.url') . '/approval-email/0/confirm/request_changes?test=1',
            'reject'          => config('app.url') . '/approval-email/0/confirm/reject?test=1',
        ];

        try {
            Mail::to($emailTo)->send(new ApprovalRequestCreatedMail($approval, $recipient, $panelUrl, $actionUrls));
            $this->info('E-mail de teste enviado com sucesso.');
            if (env('MAIL_MAILER', 'log') === 'log') {
                $this->warn('MAIL_MAILER=log — verifique storage/logs/laravel.log para o conteúdo do e-mail.');
            }
        } catch (Throwable $e) {
            $this->error('Falha ao enviar e-mail de teste: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
