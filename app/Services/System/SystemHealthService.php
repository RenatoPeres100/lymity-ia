<?php

namespace App\Services\System;

use App\Models\ActivityLog;
use App\Models\AiTask;
use App\Models\AiTaskLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemHealthService
{
    public function check(): array
    {
        $checks = [];

        $checks['database']        = $this->checkDatabase();
        $checks['redis']           = $this->checkRedis();
        $checks['storage']         = $this->checkStorage();
        $checks['bootstrap_cache'] = $this->checkBootstrapCache();
        $checks['app_key']         = $this->checkAppKey();
        $checks['app_env']         = $this->checkAppEnv();
        $checks['app_debug']       = $this->checkAppDebug();
        $checks['ai_provider']     = $this->checkAiProvider();
        $checks['queue']           = $this->checkQueue();
        $checks['jobs_table']      = $this->checkJobsTable();
        $checks['failed_jobs']     = $this->checkFailedJobs();
        $checks['admin_user']      = $this->checkAdminUser();
        $checks['last_ai_task']    = $this->checkLastAiTask();
        $checks['last_ai_log']     = $this->checkLastAiTaskLog();
        $checks['recent_errors']   = $this->getRecentErrors();
        $checks['laravel_version'] = $this->getLaravelVersion();
        $checks['php_version']     = $this->getPhpVersion();

        $critical = collect($checks)->filter(fn($c) => ($c['status'] ?? '') === 'error')->count();
        $checks['overall'] = $critical === 0 ? 'ok' : 'error';

        return $checks;
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok', 'message' => 'Conectado'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Falha: ' . $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        if (config('cache.default') !== 'redis' && config('queue.default') !== 'redis') {
            return ['status' => 'ok', 'message' => 'Não configurado (usando ' . config('cache.default') . ')'];
        }
        try {
            Cache::store('redis')->put('health_ping', 1, 5);
            return ['status' => 'ok', 'message' => 'Conectado'];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'message' => 'Redis configurado mas inacessível: ' . $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path('logs');
        if (is_writable($path)) {
            return ['status' => 'ok', 'message' => 'Gravável'];
        }
        return ['status' => 'error', 'message' => 'Não gravável — execute: chmod -R 775 storage/'];
    }

    private function checkBootstrapCache(): array
    {
        $path = base_path('bootstrap/cache');
        if (is_writable($path)) {
            return ['status' => 'ok', 'message' => 'Gravável'];
        }
        return ['status' => 'error', 'message' => 'Não gravável — execute: chmod -R 775 bootstrap/cache/'];
    }

    private function checkAppKey(): array
    {
        $key = config('app.key');
        if ($key && str_starts_with($key, 'base64:')) {
            return ['status' => 'ok', 'message' => 'Definida'];
        }
        if ($key) {
            return ['status' => 'warning', 'message' => 'Definida (formato não-base64)'];
        }
        return ['status' => 'error', 'message' => 'Vazia — execute: php artisan key:generate'];
    }

    private function checkAppEnv(): array
    {
        $env = config('app.env', 'unknown');
        return ['status' => 'ok', 'message' => $env];
    }

    private function checkAppDebug(): array
    {
        $debug = config('app.debug');
        if ($debug && config('app.env') === 'production') {
            return ['status' => 'warning', 'message' => 'true em produção — risco de segurança'];
        }
        return ['status' => $debug ? 'warning' : 'ok', 'message' => $debug ? 'true' : 'false'];
    }

    private function checkAiProvider(): array
    {
        $provider = config('ai.provider', env('AI_PROVIDER', 'não definido'));
        return ['status' => 'ok', 'message' => $provider];
    }

    private function checkQueue(): array
    {
        $conn = config('queue.default', 'sync');
        if ($conn === 'sync') {
            return ['status' => 'warning', 'message' => 'sync — jobs executam de forma síncrona (sem worker)'];
        }
        return ['status' => 'ok', 'message' => $conn];
    }

    private function checkJobsTable(): array
    {
        try {
            if (!Schema::hasTable('jobs')) {
                return ['status' => 'warning', 'message' => 'Tabela jobs não existe — execute: php artisan migrate'];
            }
            $count = DB::table('jobs')->count();
            return ['status' => 'ok', 'message' => "$count jobs pendentes"];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'message' => $e->getMessage()];
        }
    }

    private function checkFailedJobs(): array
    {
        try {
            if (!Schema::hasTable('failed_jobs')) {
                return ['status' => 'warning', 'message' => 'Tabela failed_jobs não existe'];
            }
            $count = DB::table('failed_jobs')->count();
            if ($count > 0) {
                return ['status' => 'warning', 'message' => "$count jobs com falha — execute: php artisan queue:retry all"];
            }
            return ['status' => 'ok', 'message' => 'Nenhum job com falha'];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'message' => $e->getMessage()];
        }
    }

    private function checkAdminUser(): array
    {
        try {
            $exists = User::where('email', 'admin@lymity.local')->exists();
            if ($exists) {
                return ['status' => 'ok', 'message' => 'admin@lymity.local existe'];
            }
            return ['status' => 'warning', 'message' => 'admin@lymity.local não encontrado'];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'message' => $e->getMessage()];
        }
    }

    private function checkLastAiTask(): array
    {
        try {
            $task = AiTask::latest()->first();
            if ($task) {
                return ['status' => 'ok', 'message' => "#{$task->id} — {$task->status} — {$task->created_at->diffForHumans()}"];
            }
            return ['status' => 'ok', 'message' => 'Nenhuma task registrada'];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'message' => $e->getMessage()];
        }
    }

    private function checkLastAiTaskLog(): array
    {
        try {
            if (!class_exists(AiTaskLog::class)) {
                return ['status' => 'ok', 'message' => 'AiTaskLog não disponível'];
            }
            $log = AiTaskLog::latest()->first();
            if ($log) {
                return ['status' => 'ok', 'message' => "#{$log->id} — {$log->created_at->diffForHumans()}"];
            }
            return ['status' => 'ok', 'message' => 'Nenhum log de task'];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'message' => $e->getMessage()];
        }
    }

    public function getRecentErrors(): array
    {
        $logFile = storage_path('logs/laravel.log');
        $lines   = [];

        if (!file_exists($logFile)) {
            return ['status' => 'ok', 'message' => 'Log não encontrado', 'lines' => []];
        }

        try {
            $content = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $errors  = array_filter($content, fn($l) => str_contains($l, '.ERROR') || str_contains($l, '.CRITICAL'));
            $lines   = array_slice(array_values($errors), -10);
        } catch (Throwable) {
        }

        return [
            'status'  => count($lines) > 0 ? 'warning' : 'ok',
            'message' => count($lines) . ' erros/críticos recentes no log',
            'lines'   => $lines,
        ];
    }

    private function getLaravelVersion(): array
    {
        return ['status' => 'ok', 'message' => app()->version()];
    }

    private function getPhpVersion(): array
    {
        return ['status' => 'ok', 'message' => PHP_VERSION];
    }
}
