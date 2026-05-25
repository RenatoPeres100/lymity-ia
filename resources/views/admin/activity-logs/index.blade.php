<x-layouts.app title="Logs de Atividade">
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Logs de Atividade</h1>
            <p class="text-sm text-slate-400 mt-1">Total: {{ number_format($logs->total()) }} registros</p>
        </div>
        <a href="{{ route('admin.activity-logs.export', request()->query()) }}" class="btn btn-outline text-sm">⬇ Exportar CSV</a>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Cliente</label>
                    <select name="client_id" class="input text-sm" style="min-width:140px;">
                        <option value="">Todos</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nível</label>
                    <select name="level" class="input text-sm" style="min-width:120px;">
                        <option value="">Todos</option>
                        @foreach(['info','success','warning','error','critical'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Módulo</label>
                    <input type="text" name="module" value="{{ request('module') }}" class="input text-sm" placeholder="ex: approvals" style="width:130px;">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Ação</label>
                    <input type="text" name="action" value="{{ request('action') }}" class="input text-sm" placeholder="buscar ação" style="width:130px;">
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
                    <input type="text" name="search" value="{{ request('search') }}" class="input text-sm" placeholder="ação ou descrição" style="width:150px;">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary text-sm">Filtrar</button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline text-sm">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;white-space:nowrap;">Data</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Nível</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Ação</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Módulo</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Descrição</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Cliente</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">Usuário</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;font-weight:600;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #1e293b;" class="hover:bg-slate-800/30">
                        <td style="padding:8px 12px;color:#94a3b8;white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td style="padding:8px 12px;">
                            @php $lvl = $log->level ?? 'info'; @endphp
                            <span class="badge badge-{{ $lvl }}">{{ ucfirst($lvl) }}</span>
                        </td>
                        <td style="padding:8px 12px;color:#e2e8f0;font-family:monospace;font-size:12px;">{{ $log->action }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ $log->module ?? '—' }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;max-width:280px;">{{ Str::limit($log->description, 80) }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ $log->client?->name ?? '—' }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ $log->user?->name ?? '—' }}</td>
                        <td style="padding:8px 12px;color:#475569;font-size:11px;">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="padding:32px;text-align:center;color:#475569;">Nenhum log encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div style="display:flex;gap:8px;align-items:center;">
        @if($logs->onFirstPage())
            <span class="btn btn-outline text-sm" style="opacity:.4;">← Anterior</span>
        @else
            <a href="{{ $logs->previousPageUrl() }}" class="btn btn-outline text-sm">← Anterior</a>
        @endif
        <span class="text-sm text-slate-400">Página {{ $logs->currentPage() }} de {{ $logs->lastPage() }}</span>
        @if($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="btn btn-outline text-sm">Próxima →</a>
        @else
            <span class="btn btn-outline text-sm" style="opacity:.4;">Próxima →</span>
        @endif
    </div>
    @endif

</div>
</x-layouts.app>
