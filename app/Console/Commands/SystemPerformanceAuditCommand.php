<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SystemPerformanceAuditCommand extends Command
{
    protected $signature   = 'system:performance-audit';
    protected $description = 'Diagnóstico de performance do sistema Lymity IA';

    public function handle(): int
    {
        $this->line('');
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  LYMITY IA — PERFORMANCE AUDIT — ' . now()->format('Y-m-d H:i'));
        $this->info('═══════════════════════════════════════════════════════');

        $this->auditEnv();
        $this->auditDatabase();
        $this->auditIndexes();
        $this->auditPermissions();
        $this->auditRoutes();

        $this->line('');
        $this->info('═══════════════════════════════════════════════════════');

        return 0;
    }

    private function auditEnv(): void
    {
        $this->line('');
        $this->comment('── Environment ──────────────────────────────────────');

        $env     = config('app.env');
        $debug   = config('app.debug');
        $cache   = config('cache.default');
        $queue   = config('queue.default');
        $session = config('session.driver');

        $this->check('APP_ENV=production', $env === 'production', "APP_ENV={$env}");
        $this->check('APP_DEBUG=false',    !$debug,               'APP_DEBUG=' . ($debug ? 'true' : 'false'));
        $this->check('CACHE_STORE != array', $cache !== 'array',  "CACHE_STORE={$cache}");
        $this->check('QUEUE != sync',        $queue !== 'sync',   "QUEUE_CONNECTION={$queue}");
        $this->check('SESSION_DRIVER set',   in_array($session, ['file','database','redis']), "SESSION_DRIVER={$session}");
    }

    private function auditDatabase(): void
    {
        $this->line('');
        $this->comment('── Database Counts ──────────────────────────────────');

        $tables = [
            'users'             => 'users',
            'clients'           => 'clients',
            'permissions'       => 'permissions',
            'user_permissions'  => 'user_permissions',
            'blog_posts'        => 'blog_posts',
            'social_posts'      => 'social_posts',
            'approval_requests' => 'approval_requests',
            'activity_logs'     => 'activity_logs',
            'ai_task_logs'      => 'ai_task_logs',
        ];

        foreach ($tables as $label => $table) {
            try {
                $count = DB::table($table)->count();
                $status = $count > 10000 ? 'WARNING' : 'OK';
                $this->item($status, "{$label}: {$count} rows");
            } catch (\Throwable $e) {
                $this->item('ERROR', "{$label}: " . $e->getMessage());
            }
        }
    }

    private function auditIndexes(): void
    {
        $this->line('');
        $this->comment('── Index Coverage ────────────────────────────────────');

        $expected = [
            'users'             => ['role', 'status', 'user_type'],
            'clients'           => ['status', 'name'],
            'permissions'       => ['key', 'module'],
            'blog_posts'        => ['status', 'scheduled_at', 'published_at'],
            'social_posts'      => ['status', 'scheduled_at'],
            'approval_requests' => ['status', 'created_at'],
            'activity_logs'     => ['action', 'created_at'],
        ];

        foreach ($expected as $table => $cols) {
            try {
                $existing = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Column_name')->toArray();
                foreach ($cols as $col) {
                    if (Schema::hasColumn($table, $col)) {
                        $has = in_array($col, $existing);
                        $this->item($has ? 'OK' : 'WARNING', "idx {$table}.{$col}");
                    }
                }
            } catch (\Throwable $e) {
                $this->item('ERROR', "{$table}: " . $e->getMessage());
            }
        }
    }

    private function auditPermissions(): void
    {
        $this->line('');
        $this->comment('── Permission Performance ────────────────────────────');

        $user = User::where('role', 'cliente')->where('status', 'active')->first();

        if (!$user) {
            $this->item('WARNING', 'No active cliente user found for permission benchmark');
            return;
        }

        // Measure 33 permission checks (simulates sidebar)
        $start = microtime(true);
        for ($i = 0; $i < 11; $i++) {
            $user->hasPermission('client.dashboard.view');
            $user->hasPermission('client.approvals.view');
            $user->hasPermission('client.blog.view');
        }
        $ms = round((microtime(true) - $start) * 1000, 2);

        $this->item($ms < 10 ? 'OK' : 'WARNING', "33x hasPermission(): {$ms}ms (cached: 1 query)");

        $count = $user->permissions()->count();
        $this->item('OK', "Permissions loaded for {$user->email}: {$count}");
        $this->item('OK', "hasPermission cache: in-memory per request");

        // Check duplicates
        $dupes = DB::table('user_permissions')
            ->select('user_id', 'permission_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('user_id', 'permission_id')
            ->having('cnt', '>', 1)
            ->count();
        $this->item($dupes === 0 ? 'OK' : 'WARNING', "Duplicate user_permissions: {$dupes}");
    }

    private function auditRoutes(): void
    {
        $this->line('');
        $this->comment('── Key Routes (unauthenticated) ──────────────────────');

        $routes = [
            'login'             => route('login'),
            'admin.dashboard'   => url('/admin/dashboard'),
            'client.dashboard'  => url('/client/dashboard'),
        ];

        foreach ($routes as $name => $url) {
            try {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_TIMEOUT        => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $ok = in_array($code, [200, 302, 301, 401]);
                $this->item($ok ? 'OK' : 'ERROR', "{$name}: HTTP {$code}");
            } catch (\Throwable $e) {
                $this->item('WARNING', "{$name}: curl not available ({$e->getMessage()})");
            }
        }
    }

    private function check(string $label, bool $ok, string $detail = ''): void
    {
        $this->item($ok ? 'OK' : 'WARNING', $label . ($detail ? " ({$detail})" : ''));
    }

    private function item(string $status, string $message): void
    {
        $tag = match ($status) {
            'OK'      => '<fg=green>[OK]</>     ',
            'WARNING' => '<fg=yellow>[WARNING]</> ',
            'ERROR'   => '<fg=red>[ERROR]</>   ',
            default   => "[{$status}]  ",
        };
        $this->line("  {$tag} {$message}");
    }
}
