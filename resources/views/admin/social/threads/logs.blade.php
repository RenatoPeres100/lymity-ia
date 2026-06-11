<x-layouts.app title="Logs Threads">
<div style="max-width:1000px;margin:0 auto;padding:2rem 1rem;">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;">
        <span style="font-size:1.5rem;">🧵</span>
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0;">Logs Threads</h1>
            <p style="font-size:.85rem;color:#64748b;margin:.25rem 0 0;">Histórico de conexões e publicações Threads</p>
        </div>
    </div>

    @if($logs->isEmpty())
        <div style="text-align:center;padding:3rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;">
            <p style="color:#64748b;">Nenhum log Threads registrado ainda.</p>
        </div>
    @else
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            @foreach($logs as $log)
            @php
                $levelBg   = $log->level === 'error' ? '#fef2f2' : ($log->level === 'warning' ? '#fffbeb' : '#f0fdf4');
                $levelText = $log->level === 'error' ? '#dc2626' : ($log->level === 'warning' ? '#92400e' : '#166534');
            @endphp
            <div style="padding:.75rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;gap:1rem;align-items:flex-start;font-size:.82rem;">
                <span style="background:{{ $levelBg }};color:{{ $levelText }};padding:.15rem .4rem;border-radius:.25rem;font-weight:600;white-space:nowrap;font-size:.75rem;">
                    {{ $log->level }}
                </span>
                <span style="color:#94a3b8;white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                <div style="flex:1;min-width:0;">
                    <span style="font-weight:600;color:#0f172a;">{{ $log->action }}</span>
                    @if($log->metadata)
                    <div style="color:#64748b;margin-top:.2rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ json_encode(array_filter($log->metadata, fn($v, $k) => !in_array($k, ['access_token','token']), ARRAY_FILTER_USE_BOTH), JSON_UNESCAPED_UNICODE) }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:1rem;">{{ $logs->links() }}</div>
    @endif
</div>
</x-layouts.app>
