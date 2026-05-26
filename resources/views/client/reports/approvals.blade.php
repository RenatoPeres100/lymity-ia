<x-layouts.app title="Relatório — Aprovações">
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('client.reports.index') }}" class="text-slate-500 hover:text-slate-800 text-sm">← Relatórios</a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Aprovações</h1>
            <p class="text-sm text-slate-400 mt-1">{{ $client?->name ?? 'Todos' }}</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Total',      'value'=>$data['total'],    'color'=>'#38bdf8'],
            ['label'=>'Pendentes',  'value'=>$data['pending'],  'color'=>'#fde047'],
            ['label'=>'Aprovadas',  'value'=>$data['approved'], 'color'=>'#4ade80'],
            ['label'=>'Reprovadas', 'value'=>$data['rejected'], 'color'=>'#f87171'],
        ] as $c)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:28px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $c['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-slate-800 text-sm">Histórico Recente</h3></div>
        <div class="card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Título</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Tipo</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Status</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['recent'] as $a)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 12px;color:#334155;">{{ Str::limit($a->title, 42) }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ ucwords(str_replace('_',' ',$a->approval_type)) }}</td>
                        <td style="padding:8px 12px;"><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                        <td style="padding:8px 12px;color:#64748b;white-space:nowrap;">{{ $a->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="padding:24px;text-align:center;color:#475569;">Nenhuma aprovação.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.app>
