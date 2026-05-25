<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Services\System\SystemHealthService;
use Illuminate\Console\Command;

class SystemHealthCheckCommand extends Command
{
    protected $signature   = 'system:health-check';
    protected $description = 'Verifica a saúde geral do sistema Lymity IA';

    public function handle(SystemHealthService $health): int
    {
        $this->info('');
        $this->info('========================================');
        $this->info('  Lymity IA — System Health Check');
        $this->info('  ' . now()->format('Y-m-d H:i:s'));
        $this->info('========================================');
        $this->info('');

        $checks = $health->check();
        $overall = $checks['overall'] ?? 'ok';

        $labels = [
            'database'        => 'Banco de Dados',
            'redis'           => 'Redis',
            'storage'         => 'Storage (gravável)',
            'bootstrap_cache' => 'Bootstrap Cache',
            'app_key'         => 'APP_KEY',
            'app_env'         => 'APP_ENV',
            'app_debug'       => 'APP_DEBUG',
            'ai_provider'     => 'AI_PROVIDER',
            'queue'           => 'Queue Connection',
            'jobs_table'      => 'Tabela jobs',
            'failed_jobs'     => 'Failed Jobs',
            'admin_user'      => 'Admin User',
            'last_ai_task'    => 'Último AiTask',
            'last_ai_log'     => 'Último AiTaskLog',
            'laravel_version' => 'Laravel Version',
            'php_version'     => 'PHP Version',
        ];

        $hasError = false;

        foreach ($labels as $key => $label) {
            if (!isset($checks[$key])) {
                continue;
            }

            $item    = $checks[$key];
            $status  = $item['status']  ?? 'ok';
            $message = $item['message'] ?? '';

            $prefix = match ($status) {
                'ok'      => '<fg=green>  [OK]     </>',
                'warning' => '<fg=yellow>  [WARN]   </>',
                'error'   => '<fg=red>  [ERROR]  </>',
                default   => '  [INFO]   ',
            };

            $this->line("{$prefix} {$label}: {$message}");

            if ($status === 'error') {
                $hasError = true;
            }
        }

        // Recent errors
        $errorCheck = $checks['recent_errors'] ?? [];
        $errorLines = $errorCheck['lines'] ?? [];
        if (!empty($errorLines)) {
            $this->info('');
            $this->warn('  Últimos erros no laravel.log:');
            foreach (array_slice($errorLines, -5) as $line) {
                $this->line('  ' . substr($line, 0, 200));
            }
        }

        $this->info('');
        $this->info('========================================');

        if ($hasError) {
            $this->error('  RESULTADO: ERRO CRÍTICO DETECTADO');
        } else {
            $this->info('<fg=green>  RESULTADO: SISTEMA OK</>');
        }

        $this->info('========================================');
        $this->info('');

        // ActivityLog
        try {
            ActivityLog::create([
                'action'      => 'system_health_checked',
                'level'       => $hasError ? 'error' : 'info',
                'description' => 'Health check executado via command. Status: ' . ($hasError ? 'error' : 'ok'),
                'metadata'    => json_encode(['overall' => $overall]),
            ]);
        } catch (\Throwable) {
        }

        return $hasError ? self::FAILURE : self::SUCCESS;
    }
}
