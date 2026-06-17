<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class LymityRegressionCheckCommand extends Command
{
    protected $signature   = 'lymity:regression-check';
    protected $description = 'Executa checklist de regressão de todos os módulos do Lymity. Usar antes de finalizar qualquer nova feature.';

    private array $results = [];

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║     Lymity IA — Regression Check                ║');
        $this->info('║     ' . now()->format('Y-m-d H:i:s') . '                       ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');
        $this->info('Executando verificações de todos os módulos...');
        $this->info('');

        $this->runCheck('system:health-check',              'Sistema Geral');
        $this->runCheck('agents:diagnose-execution-engine', 'Motor de Execução de Agentes');
        $this->runCheck('lymity:cron-health',               'Cron & Scheduler');
        $this->runCheck('lymity:stability-check',           'Estabilidade do Sistema');
        $this->runCheck('agents:run-due-tasks --dry-run',   'AgentTasks (dry-run)');
        $this->runOptionalCheck('instagram:diagnose-publishing', 'Instagram Publishing');
        $this->runOptionalCheck('threads:diagnose',              'Threads');
        $this->runOptionalCheck('approvals:diagnose-email',      'Aprovações / E-mail');
        $this->runOptionalCheck('prospecting:diagnose',          'Prospecção CRM');

        $this->printSummary();

        $hasFail = collect($this->results)->contains(fn($r) => $r['status'] === 'FAIL');
        return $hasFail ? self::FAILURE : self::SUCCESS;
    }

    private function runCheck(string $commandLine, string $label): void
    {
        [$command, $args] = $this->parseCommandLine($commandLine);

        $this->line("▶ [{$label}] {$commandLine}");

        try {
            $exitCode = Artisan::call($command, $args);
            $output   = Artisan::output();

            if ($exitCode === 0) {
                $this->line("  <fg=green>✓ OK</> ({$command})");
                $this->results[] = ['label' => $label, 'status' => 'OK', 'command' => $commandLine];
            } else {
                $this->line("  <fg=yellow>⚠ WARN</> exit={$exitCode} ({$command})");
                $this->results[] = ['label' => $label, 'status' => 'WARN', 'command' => $commandLine, 'exit' => $exitCode];
            }
        } catch (\Throwable $e) {
            $this->line("  <fg=red>✗ FAIL</> " . mb_substr($e->getMessage(), 0, 100));
            $this->results[] = ['label' => $label, 'status' => 'FAIL', 'command' => $commandLine, 'error' => $e->getMessage()];
        }

        $this->info('');
    }

    private function runOptionalCheck(string $commandLine, string $label): void
    {
        [$command] = $this->parseCommandLine($commandLine);

        $exists = collect(Artisan::all())->has($command);
        if (!$exists) {
            $this->line("  <fg=gray>○ SKIP</> {$label} ({$command} não registrado)");
            $this->results[] = ['label' => $label, 'status' => 'SKIP', 'command' => $commandLine];
            $this->info('');
            return;
        }

        $this->runCheck($commandLine, $label);
    }

    private function parseCommandLine(string $line): array
    {
        $parts = explode(' ', trim($line));
        $command = array_shift($parts);

        $args = [];
        foreach ($parts as $part) {
            if (str_starts_with($part, '--')) {
                $key = ltrim($part, '-');
                if (str_contains($key, '=')) {
                    [$k, $v] = explode('=', $key, 2);
                    $args["--{$k}"] = $v;
                } else {
                    $args["--{$key}"] = true;
                }
            }
        }

        return [$command, $args];
    }

    private function printSummary(): void
    {
        $this->info('══════════════════════════════════════════════════');
        $this->info('  RESUMO — REGRESSION CHECK');
        $this->info('══════════════════════════════════════════════════');
        $this->info('');

        $statusColors = [
            'OK'   => 'green',
            'WARN' => 'yellow',
            'FAIL' => 'red',
            'SKIP' => 'gray',
        ];

        foreach ($this->results as $r) {
            $color  = $statusColors[$r['status']] ?? 'white';
            $status = str_pad($r['status'], 4);
            $this->line("  <fg={$color}>[{$status}]</> {$r['label']}");
        }

        $ok   = collect($this->results)->where('status', 'OK')->count();
        $warn = collect($this->results)->where('status', 'WARN')->count();
        $fail = collect($this->results)->where('status', 'FAIL')->count();
        $skip = collect($this->results)->where('status', 'SKIP')->count();

        $this->info('');
        $this->info("  Total: {$ok} OK | {$warn} WARN | {$fail} FAIL | {$skip} SKIP");
        $this->info('');

        if ($fail > 0) {
            $this->error('  RESULTADO: FALHAS DETECTADAS — revisar antes de finalizar feature.');
        } elseif ($warn > 0) {
            $this->warn('  RESULTADO: AVISOS — verificar e decidir se é aceitável.');
        } else {
            $this->info('  <fg=green>RESULTADO: TODOS OS MÓDULOS OK</>');
        }

        $this->info('══════════════════════════════════════════════════');
        $this->info('');
    }
}
