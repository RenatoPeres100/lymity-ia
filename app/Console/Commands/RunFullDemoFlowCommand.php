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
        $result = $service->run($clientName);

        if (! $result['success']) {
            $this->error('Erro: ' . $result['error']);
            return self::FAILURE;
        }

        $this->line("  Cliente selecionado: <fg=cyan>{$result['client']}</>");
        $this->line('');

        $successCount = 0;
        $errorCount   = 0;

        foreach ($result['steps'] as $step) {
            $icon   = match ($step['status']) {
                'success' => '<fg=green>✓</>',
                'error'   => '<fg=red>✗</>',
                'warning' => '<fg=yellow>⚠</>',
                default   => '•',
            };

            $label = str_pad("[{$step['step']}] {$step['title']}", 28);
            $this->line("  {$icon} {$label}  {$step['detail']}");

            if ($step['status'] === 'success') {
                $successCount++;
            } elseif ($step['status'] === 'error') {
                $errorCount++;
            }
        }

        $this->info('');
        $this->info('──────────────────────────────────────────────────');

        if ($errorCount === 0) {
            $this->info("  Resultado: {$successCount}/" . count($result['steps']) . " etapas concluídas com sucesso.");
            $this->info('  Status: <fg=green>DEMO FLOW COMPLETO — OK</>');
        } else {
            $this->warn("  Resultado: {$errorCount} etapa(s) com erro. Verifique os logs.");
            $this->warn('  Status: <fg=yellow>DEMO FLOW COM AVISOS</>');
        }

        $this->info('──────────────────────────────────────────────────');
        $this->info('');

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
