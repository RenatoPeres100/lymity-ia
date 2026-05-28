<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class SystemOptimizeSafeCommand extends Command
{
    protected $signature   = 'system:optimize-safe';
    protected $description = 'Safely apply production optimizations (no migrate, no route cache if closures present)';

    public function handle(): int
    {
        $this->line('');
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  LYMITY IA — SAFE OPTIMIZE');
        $this->info('═══════════════════════════════════════════════════');

        $this->step('Clearing all caches...', fn () => $this->callSilently('optimize:clear'));
        $this->step('Caching config...', fn () => $this->callSilently('config:cache'));
        $this->step('Caching views...', fn () => $this->callSilently('view:cache'));

        // Route cache — skip if closures detected
        try {
            $this->call('route:cache');
            $this->result('OK', 'Route cache applied');
        } catch (\Throwable $e) {
            $this->result('WARNING', 'Route cache skipped: ' . $e->getMessage());
        }

        // PHP OPcache reset (FPM)
        $this->resetOpcache();

        $this->line('');
        $this->info('Optimization complete.');
        $this->line('');

        return 0;
    }

    private function step(string $label, callable $fn): void
    {
        $this->line("  → {$label}");
        try {
            $fn();
            $this->result('OK', $label);
        } catch (\Throwable $e) {
            $this->result('WARNING', "{$label} — {$e->getMessage()}");
        }
    }

    private function resetOpcache(): void
    {
        // Try to reset OPcache via PHP-FPM reload (safest on production)
        $process = Process::fromShellCommandline('which php-fpm8.3 php-fpm8.2 php-fpm 2>/dev/null | head -1');
        $process->run();
        $fpm = trim($process->getOutput());

        if ($fpm) {
            $reload = Process::fromShellCommandline("kill -USR2 $(cat /run/php/php8.3-fpm.pid 2>/dev/null || cat /run/php-fpm.pid 2>/dev/null) 2>/dev/null");
            $reload->run();
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->result('OK', 'OPcache reset (CLI)');
        } else {
            $this->result('WARNING', 'OPcache reset skipped (CLI SAPI does not share FPM OPcache — restart PHP-FPM if needed)');
        }
    }

    private function result(string $status, string $msg): void
    {
        $tag = match ($status) {
            'OK'      => '<fg=green>[OK]</>',
            'WARNING' => '<fg=yellow>[WARNING]</>',
            'ERROR'   => '<fg=red>[ERROR]</>',
            default   => "[{$status}]",
        };
        $this->line("  {$tag} {$msg}");
    }
}
