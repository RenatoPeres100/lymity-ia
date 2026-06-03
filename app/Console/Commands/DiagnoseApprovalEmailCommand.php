<?php

namespace App\Console\Commands;

use App\Models\ApprovalEmailNotification;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DiagnoseApprovalEmailCommand extends Command
{
    protected $signature   = 'approvals:diagnose-email';
    protected $description = 'Diagnose approval email notification system status';

    public function handle(): int
    {
        $this->info('=== Diagnóstico: Notificações de E-mail de Aprovações ===');
        $this->newLine();

        $this->line('<options=bold>Configurações:</>');
        $enabled = config('approval-notifications.email_enabled') ? '<info>habilitado</info>' : '<error>DESABILITADO</error>';
        $this->line("  APPROVAL_EMAIL_NOTIFICATIONS_ENABLED: {$enabled}");
        $this->line("  APPROVAL_EMAIL_SIGNED_LINK_TTL_HOURS: " . config('approval-notifications.signed_link_ttl_hours'));
        $reminderEnabled = config('approval-notifications.reminder_enabled') ? '<info>habilitado</info>' : '<error>DESABILITADO</error>';
        $this->line("  APPROVAL_EMAIL_REMINDER_ENABLED: {$reminderEnabled}");
        $this->line("  APPROVAL_EMAIL_REMINDER_HOURS_BEFORE_DUE: " . config('approval-notifications.reminder_hours_before_due'));
        $this->line("  APPROVAL_EMAIL_MAX_REMINDERS: " . config('approval-notifications.max_reminders'));
        $this->newLine();

        $this->line('<options=bold>Configuração de E-mail (SMTP):</>');
        $mailer = env('MAIL_MAILER', 'log');
        $mailerStr = $mailer === 'log'
            ? "<comment>log (e-mails vão para storage/logs, não são enviados)</comment>"
            : "<info>{$mailer}</info>";
        $this->line("  MAIL_MAILER: {$mailerStr}");
        $host = env('MAIL_HOST', '');
        $this->line("  MAIL_HOST: " . ($host ? "<info>{$host}</info>" : '<error>NÃO CONFIGURADO</error>'));
        $this->line("  MAIL_PORT: " . env('MAIL_PORT', '—'));
        $this->line("  MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', '—'));
        $pass = env('MAIL_PASSWORD', '');
        $this->line("  MAIL_PASSWORD: " . ($pass ? '<info>configurada</info>' : '<comment>não configurada</comment>'));
        $this->newLine();

        $this->line('<options=bold>Fila:</>');
        $queueConn = env('QUEUE_CONNECTION', 'sync');
        $queueStr  = in_array($queueConn, ['redis', 'database', 'beanstalkd'])
            ? "<info>{$queueConn}</info>"
            : "<comment>{$queueConn} (sem worker assíncrono)</comment>";
        $this->line("  QUEUE_CONNECTION: {$queueStr}");
        $this->newLine();

        $this->line('<options=bold>Dados do sistema:</>');
        $totalPending = ApprovalRequest::where('status', 'pending')->count();
        $totalSent    = ApprovalEmailNotification::where('status', 'sent')->count();
        $totalFailed  = ApprovalEmailNotification::where('status', 'failed')->count();
        $totalSkipped = ApprovalEmailNotification::where('status', 'skipped')->count();
        $this->line("  Aprovações pendentes: {$totalPending}");
        $this->line("  E-mails enviados (total): {$totalSent}");
        $this->line("  E-mails com falha: " . ($totalFailed > 0 ? "<error>{$totalFailed}</error>" : '0'));
        $this->line("  E-mails ignorados: {$totalSkipped}");
        $this->newLine();

        $lastFailed = ApprovalEmailNotification::where('status', 'failed')
            ->orderByDesc('failed_at')->first();
        if ($lastFailed) {
            $this->line('<options=bold>Último erro de e-mail:</>');
            $this->line("  Data: {$lastFailed->failed_at}");
            $this->line("  Email: {$lastFailed->email}");
            $this->line("  Erro: " . Str::limit($lastFailed->error_message ?? '—', 200));
            $this->newLine();
        }

        $this->line('<options=bold>Destinatários:</>');
        $adminCount = User::whereIn('role', ['admin_geral', 'agencia_admin'])
            ->where('status', 'active')->whereNotNull('email')->count();
        $this->line("  Admins gerais/agência ativos com e-mail: {$adminCount}");
        $clientUsers = User::whereIn('role', ['cliente', 'cliente_admin'])
            ->where('status', 'active')->whereNotNull('email')
            ->whereNotNull('client_id')->distinct('client_id')->count('client_id');
        $this->line("  Clientes com admins aptos a aprovar: {$clientUsers}");
        $this->newLine();

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER=log — e-mails vão para storage/logs/laravel.log (não enviados por SMTP).');
        }
        if (!$host && $mailer !== 'log') {
            $this->error('MAIL_HOST não configurado. Configure o SMTP no .env.');
        }
        if ($adminCount === 0) {
            $this->error('Nenhum admin ativo com e-mail. Aprovações da agência não serão notificadas.');
        }

        $this->info('Diagnóstico concluído.');
        return 0;
    }
}
