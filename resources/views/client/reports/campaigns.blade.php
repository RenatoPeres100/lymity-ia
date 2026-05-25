<x-layouts.app title="Relatório — Campanhas">
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('client.reports.index') }}" class="text-slate-400 hover:text-white text-sm">← Relatórios</a>
        <div>
            <h1 class="text-2xl font-bold text-white">Campanhas</h1>
            <p class="text-sm text-slate-400 mt-1">{{ $client?->name ?? 'Todos' }}</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Total',       'value'=>$data['total'],                          'color'=>'#a78bfa'],
            ['label'=>'Ativas',      'value'=>$data['active'],                         'color'=>'#4ade80'],
            ['label'=>'Impressões',  'value'=>number_format($data['total_impressions']),'color'=>'#38bdf8'],
            ['label'=>'Leads',       'value'=>number_format($data['total_leads']),      'color'=>'#22d3ee'],
        ] as $c)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:26px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $c['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
        @foreach([
            ['label'=>'Custo Total','value'=>'R$ '.number_format($data['total_cost'],2,',','.'),'color'=>'#f87171'],
            ['label'=>'CTR Médio',  'value'=>$data['avg_ctr'].'%',                              'color'=>'#fb923c'],
            ['label'=>'ROAS Médio', 'value'=>$data['avg_roas'].'x',                             'color'=>'#4ade80'],
        ] as $c)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:24px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $c['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-white text-sm">Campanhas Recentes</h3></div>
        <div class="card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Nome</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Plataforma</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Status</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Budget/dia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['recent'] as $c)
                    <tr style="border-bottom:1px solid #1e293b;">
                        <td style="padding:8px 12px;color:#e2e8f0;">{{ Str::limit($c->name, 40) }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ ucwords(str_replace('_',' ',$c->platform)) }}</td>
                        <td style="padding:8px 12px;"><span class="badge badge-{{ $c->status }}">{{ ucfirst($c->status) }}</span></td>
                        <td style="padding:8px 12px;color:#4ade80;">R$ {{ number_format($c->daily_budget,2,',','.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="padding:24px;text-align:center;color:#475569;">Nenhuma campanha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.app>
