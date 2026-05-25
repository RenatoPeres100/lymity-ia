@extends('components.layouts.app')

@section('content')
<div class="space-y-5">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.reports.index') }}" class="text-slate-400 hover:text-white text-sm">← Relatórios</a>
        <div>
            <h1 class="text-2xl font-bold text-white">Relatório de Aprovações</h1>
            <p class="text-sm text-slate-400 mt-1">Todos os clientes</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Total',       'value'=>$data['total'],             'color'=>'#38bdf8'],
            ['label'=>'Pendentes',   'value'=>$data['pending'],           'color'=>'#fde047'],
            ['label'=>'Aprovadas',   'value'=>$data['approved'],          'color'=>'#4ade80'],
            ['label'=>'Reprovadas',  'value'=>$data['rejected'],          'color'=>'#f87171'],
        ] as $card)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:32px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
        @foreach([
            ['label'=>'Ajustes Pedidos', 'value'=>$data['changes_requested'], 'color'=>'#60a5fa'],
            ['label'=>'Críticas',        'value'=>$data['critical'],           'color'=>'#f87171'],
            ['label'=>'Vencidas',        'value'=>$data['overdue'],            'color'=>'#f97316'],
        ] as $card)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:28px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        {{-- By type --}}
        @if(count($data['by_type']))
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-white text-sm">Por Tipo</h3></div>
            <div class="card-body" style="padding:20px;">
                @php $max = max($data['by_type']) ?: 1; @endphp
                @foreach($data['by_type'] as $type => $count)
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:13px;color:#e2e8f0;">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                        <span style="font-size:13px;color:#94a3b8;">{{ $count }}</span>
                    </div>
                    <div style="height:8px;background:#1e293b;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:{{ round($count/$max*100) }}%;background:#38bdf8;border-radius:4px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- By level --}}
        @if(count($data['by_level']))
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-white text-sm">Por Nível de Sensibilidade</h3></div>
            <div class="card-body" style="padding:20px;">
                @php $max = max($data['by_level']) ?: 1; @endphp
                @php $colors = ['critical'=>'#f87171','high'=>'#fb923c','medium'=>'#fde047','low'=>'#94a3b8']; @endphp
                @foreach($data['by_level'] as $level => $count)
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:13px;color:#e2e8f0;">{{ ucfirst($level) }}</span>
                        <span style="font-size:13px;color:#94a3b8;">{{ $count }}</span>
                    </div>
                    <div style="height:8px;background:#1e293b;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:{{ round($count/$max*100) }}%;background:{{ $colors[$level] ?? '#94a3b8' }};border-radius:4px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Recent --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-white text-sm">Aprovações Recentes</h3>
            <a href="{{ route('admin.approvals.index') }}" class="text-xs text-sky-400">Ver todas →</a>
        </div>
        <div class="card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Título</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Tipo</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Status</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Nível</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Solicitante</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['recent'] as $a)
                    <tr style="border-bottom:1px solid #1e293b;">
                        <td style="padding:8px 12px;color:#e2e8f0;">{{ Str::limit($a->title, 40) }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ ucwords(str_replace('_',' ',$a->approval_type)) }}</td>
                        <td style="padding:8px 12px;"><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                        <td style="padding:8px 12px;"><span class="badge badge-{{ $a->sensitive_level }}">{{ ucfirst($a->sensitive_level) }}</span></td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ $a->requester?->name ?? '—' }}</td>
                        <td style="padding:8px 12px;color:#64748b;white-space:nowrap;">{{ $a->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:24px;text-align:center;color:#475569;">Nenhuma aprovação encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
