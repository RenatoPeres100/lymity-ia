<x-layouts.app title="System Health">
<div style="max-width:1100px;margin:0 auto;padding:32px 16px;">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;color:#0f172a;margin:0 0 4px;">System Health</h1>
            <p style="font-size:.875rem;color:#64748b;margin:0;">Verificação em tempo real da saúde do sistema · {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
        @php $overall = $checks['overall'] ?? 'ok'; @endphp
        <div style="background:{{ $overall === 'ok' ? '#dcfce7' : '#fee2e2' }};color:{{ $overall === 'ok' ? '#166534' : '#dc2626' }};font-size:.875rem;font-weight:700;padding:8px 20px;border-radius:8px;letter-spacing:.05em;border:1px solid {{ $overall === 'ok' ? '#86efac' : '#fca5a5' }};">
            {{ $overall === 'ok' ? 'SISTEMA OK' : 'ERRO CRÍTICO' }}
        </div>
    </div>

    @php
    $labels = [
        'database'        => ['icon' => '🗄', 'label' => 'Banco de Dados'],
        'redis'           => ['icon' => '⚡', 'label' => 'Redis'],
        'storage'         => ['icon' => '📁', 'label' => 'Storage'],
        'bootstrap_cache' => ['icon' => '📂', 'label' => 'Bootstrap Cache'],
        'app_key'         => ['icon' => '🔑', 'label' => 'APP_KEY'],
        'app_env'         => ['icon' => '🌍', 'label' => 'APP_ENV'],
        'app_debug'       => ['icon' => '🐛', 'label' => 'APP_DEBUG'],
        'ai_provider'     => ['icon' => '🤖', 'label' => 'AI Provider'],
        'queue'           => ['icon' => '📋', 'label' => 'Queue Connection'],
        'jobs_table'      => ['icon' => '📬', 'label' => 'Jobs Pendentes'],
        'failed_jobs'     => ['icon' => '❌', 'label' => 'Failed Jobs'],
        'admin_user'      => ['icon' => '👤', 'label' => 'Admin User'],
        'last_ai_task'    => ['icon' => '🧠', 'label' => 'Último AiTask'],
        'last_ai_log'     => ['icon' => '📜', 'label' => 'Último AiTask Log'],
        'laravel_version' => ['icon' => '🛠', 'label' => 'Laravel Version'],
        'php_version'     => ['icon' => '🐘', 'label' => 'PHP Version'],
    ];

    function statusColor($s) {
        return match($s) {
            'ok'      => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#86efac', 'badge' => 'OK'],
            'warning' => ['bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa', 'badge' => 'WARN'],
            'error'   => ['bg' => '#fef2f2', 'text' => '#dc2626', 'border' => '#fca5a5', 'badge' => 'ERROR'],
            default   => ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0', 'badge' => 'INFO'],
        };
    }
    @endphp

    {{-- Cards grid --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:16px;margin-bottom:32px;">
        @foreach($labels as $key => $meta)
            @if(isset($checks[$key]))
                @php $item = $checks[$key]; $c = statusColor($item['status'] ?? 'ok'); @endphp
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:4px solid {{ $c['border'] }};border-radius:10px;padding:16px 20px;display:flex;align-items:flex-start;gap:14px;">
                    <div style="font-size:1.4rem;line-height:1;flex-shrink:0;">{{ $meta['icon'] }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.72rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2px;">{{ $meta['label'] }}</div>
                        <div style="font-size:.875rem;color:#334155;word-break:break-word;">{{ $item['message'] ?? '—' }}</div>
                    </div>
                    <div style="background:{{ $c['bg'] }};color:{{ $c['text'] }};border:1px solid {{ $c['border'] }};font-size:.68rem;font-weight:700;padding:3px 8px;border-radius:6px;flex-shrink:0;">
                        {{ $c['badge'] }}
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Recent errors log --}}
    @php $errorCheck = $checks['recent_errors'] ?? []; $errorLines = $errorCheck['lines'] ?? []; @endphp
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px 24px;margin-bottom:28px;">
        <h2 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px;">
            <span>⚠</span> Erros Recentes no laravel.log
            <span style="background:{{ count($errorLines) > 0 ? '#fff7ed' : '#dcfce7' }};color:{{ count($errorLines) > 0 ? '#9a3412' : '#166534' }};border:1px solid {{ count($errorLines) > 0 ? '#fed7aa' : '#86efac' }};font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:6px;margin-left:4px;">
                {{ count($errorLines) }}
            </span>
        </h2>
        @if(empty($errorLines))
            <p style="font-size:.875rem;color:#64748b;margin:0;">Nenhum erro recente encontrado.</p>
        @else
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;overflow-x:auto;">
                @foreach($errorLines as $line)
                    <div style="font-family:monospace;font-size:.75rem;color:#dc2626;margin-bottom:4px;white-space:pre-wrap;word-break:break-all;">{{ substr($line, 0, 220) }}</div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Commands section --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(480px,1fr));gap:20px;margin-bottom:28px;">

        {{-- Useful commands --}}
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px 24px;">
            <h2 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 14px;">Comandos Úteis</h2>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach([
                    ['php artisan system:health-check', 'Verificar saúde completa'],
                    ['php artisan queue:test', 'Disparar job de teste'],
                    ['php artisan queue:work --stop-when-empty', 'Processar fila e parar'],
                    ['php artisan schedule:run', 'Executar tarefas agendadas'],
                    ['php artisan queue:failed', 'Ver jobs com falha'],
                    ['php artisan queue:retry all', 'Reprocessar todos os falhos'],
                    ['php artisan optimize:clear', 'Limpar todos os caches'],
                ] as [$cmd, $desc])
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;">
                        <code style="font-family:monospace;font-size:.78rem;color:#4f46e5;display:block;margin-bottom:2px;">{{ $cmd }}</code>
                        <span style="font-size:.72rem;color:#64748b;">{{ $desc }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Worker / Scheduler instructions --}}
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px 24px;">
            <h2 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 14px;">Produção: Worker & Scheduler</h2>

            <p style="font-size:.8rem;color:#475569;margin:0 0 10px;font-weight:600;">Supervisor (workers persistentes):</p>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:14px;">
                @foreach([
                    'sudo cp supervisor/laravel-worker.conf.example /etc/supervisor/conf.d/laravel-worker.conf',
                    'sudo supervisorctl reread && sudo supervisorctl update',
                    'sudo supervisorctl start laravel-worker:*',
                    'sudo supervisorctl status',
                ] as $cmd)
                    <code style="font-family:monospace;font-size:.72rem;color:#4f46e5;display:block;margin-bottom:3px;">{{ $cmd }}</code>
                @endforeach
            </div>

            <p style="font-size:.8rem;color:#475569;margin:0 0 10px;font-weight:600;">Cron (scheduler automático):</p>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;">
                <code style="font-family:monospace;font-size:.72rem;color:#4f46e5;">* * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1</code>
                <div style="font-size:.7rem;color:#64748b;margin-top:4px;">Adicionar via: <code style="color:#475569;">crontab -e</code></div>
            </div>
        </div>
    </div>

    {{-- Queue last test --}}
    @php $lastQueueTest = cache('lymity_queue_test_last_run'); @endphp
    @if($lastQueueTest)
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:16px 24px;margin-bottom:28px;display:flex;align-items:center;gap:12px;">
        <span style="font-size:1.1rem;">✅</span>
        <div>
            <div style="font-size:.8rem;color:#166534;font-weight:600;">Último TestQueueJob processado</div>
            <div style="font-size:.875rem;color:#15803d;">{{ $lastQueueTest }}</div>
        </div>
    </div>
    @endif

</div>
</x-layouts.app>
