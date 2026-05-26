<?php

namespace App\Console\Commands;

use App\Services\Demo\DemoFlowService;
use Illuminate\Console\Command;

class RunFullDemoFlowCommand extends Command
{
    protected $signature = 'demo:run-full-flow {--client= : Nome parcial do cliente}';
    protected $description = 'Executa o fluxo completo de demonstração da plataforma Lymity IA';

    public function handle(DemoFlowService $service): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║        LYMITY IA — DEMO FLOW COMPLETO            ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        $clientName = $this->option('client');
        $result     = $service->run($clientName);

        if (!$result['success']) {
            $this->error('Erro: ' . $result['error']);
            return self::FAILURE;
        }

        $this->line("  Cliente selecionado: <fg=cyan>{$result['client']}</>");
        $this->line('');

        $successCount = 0;
        $errorCount   = 0;

        foreach ($result['steps'] as $step) {
            $icon = match ($step['status']) {
                'success' => '<fg=green>✓</>',
                'error'   => '<fg=red>✗</>',
                'warning' => '<fg=yellow>⚠</>',
                default   => '•',
            };

            $label = str_pad("[{$step['step']}] {$step['title']}", 32);
            $this->line("  {$icon} {$label}  {$step['detail']}");

            if ($step['status'] === 'success') {
                $successCount++;
            } elseif ($step['status'] === 'error') {
                $errorCount++;
            }
        }

        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info('  VARIÁVEIS DE SAÍDA');
        $this->info('══════════════════════════════════════════════════');
        $this->line("  SOCIAL_POST_ID={$service->socialPostId}");
        $this->line("  SOCIAL_APPROVAL_STATUS={$service->socialApprovalStatus}");
        $this->line("  SOCIAL_POST_STATUS={$service->socialPostStatus}");
        $this->line("  AD_CAMPAIGN_ID={$service->adCampaignId}");
        $this->line("  CAMPAIGN_APPROVAL_STATUS={$service->campaignApprovalStatus}");
        $this->line("  BLOG_POST_ID={$service->blogPostId}");
        $this->line("  BLOG_APPROVAL_STATUS={$service->blogApprovalStatus}");
        $this->line("  BUDGET_ID={$service->budgetId}");
        $this->line("  BUDGET_STATUS={$service->budgetStatus}");
        $this->line("  AI_REPORT_TASK_ID={$service->aiReportTaskId}");
        $this->line("  ACTIVITY_LOGS_COUNT={$service->activityLogsCount}");
        $finalStatus = $errorCount === 0 ? 'OK' : 'ERROR';
        $this->line("  FINAL_STATUS={$finalStatus}");

        $this->info('══════════════════════════════════════════════════');
        $this->info('');

        if ($errorCount === 0) {
            $this->info("  Resultado: {$successCount}/" . count($result['steps']) . " etapas concluídas com sucesso.");
            $this->info('  Status: <fg=green>DEMO FLOW COMPLETO — OK</>');
        } else {
            $this->warn("  Resultado: {$errorCount} etapa(s) com erro. Verifique os logs.");
            $this->warn('  Status: <fg=yellow>DEMO FLOW COM AVISOS</>');
        }

        $this->info('══════════════════════════════════════════════════');
        $this->info('');

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
