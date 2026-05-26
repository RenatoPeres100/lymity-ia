<x-layouts.app title="Logs de Segurança">
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Logs de Segurança</h1>
            <p class="text-sm text-slate-400 mt-1">Ações sensíveis, erros e eventos críticos</p>
        </div>
        <a href="{{ route('admin.activity-logs.export', array_merge(request()->query(), ['level'=>'critical'])) }}" class="btn btn-outline text-sm">⬇ Exportar CSV</a>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.security-logs.index') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nível</label>
                    <select name="level" class="input text-sm" style="min-width:130px;">
                        <option value="">Todos sensíveis</option>
                        @foreach(['warning','error','critical'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">De</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="input text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Até</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="input text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Busca</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input text-sm" placeholder="ação ou descrição" style="width:160px;">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary text-sm">Filtrar</button>
                    <a href="{{ route('admin.security-logs.index') }}" class="btn btn-outline text-sm">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Security Logs Table --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-slate-800 text-sm">Eventos Sensíveis ({{ number_format($logs->total()) }})</h3></div>
        <div class="card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;white-space:nowrap;">Data</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Nível</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Ação</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Descrição</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Usuário</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 12px;color:#94a3b8;white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td style="padding:8px 12px;">
                            @php $lvl = $log->level ?? 'info'; @endphp
                            <span class="badge badge-{{ $lvl }}">{{ ucfirst($lvl) }}</span>
                        </td>
                        <td style="padding:8px 12px;color:#334155;font-family:monospace;font-size:12px;">{{ $log->action }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;max-width:300px;">{{ Str::limit($log->description, 90) }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ $log->user?->name ?? '—' }}</td>
                        <td style="padding:8px 12px;color:#475569;font-size:11px;">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:32px;text-align:center;color:#475569;">Nenhum evento sensível encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Critical Approvals --}}
    @if($criticalApprovals->count())
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-slate-800 text-sm">Aprovações Críticas Recentes</h3></div>
        <div class="card-body">
            @foreach($criticalApprovals as $a)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div class="text-sm font-medium text-slate-700">{{ $a->title }}</div>
                    <div class="text-xs text-slate-500">{{ $a->client?->name ?? '—' }} · {{ $a->approval_type }} · {{ $a->created_at->diffForHumans() }}</div>
                </div>
                <span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div style="display:flex;gap:8px;align-items:center;">
        @if(!$logs->onFirstPage())<a href="{{ $logs->previousPageUrl() }}" class="btn btn-outline text-sm">← Anterior</a>@endif
        <span class="text-sm text-slate-400">{{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>
        @if($logs->hasMorePages())<a href="{{ $logs->nextPageUrl() }}" class="btn btn-outline text-sm">Próxima →</a>@endif
    </div>
    @endif

</div>
</x-layouts.app>
