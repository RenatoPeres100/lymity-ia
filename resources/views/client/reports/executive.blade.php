<x-layouts.app title="Relatório Executivo">
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Relatório Executivo</h1>
            <p class="text-sm text-slate-400 mt-1">{{ $client?->name ?? 'Visão geral' }} — {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('client.reports.index') }}" class="btn btn-outline text-sm">← Relatórios</a>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-slate-800">Resumo</h3></div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:16px;">
            @php
            $cards = $client ? [
                ['label'=>'Aprovações Pendentes','value'=>$summary['approvals_pending'],'color'=>'#fde047'],
                ['label'=>'Posts Sociais','value'=>$summary['social_posts'],'color'=>'#38bdf8'],
                ['label'=>'Campanhas Ativas','value'=>$summary['campaigns_active'],'color'=>'#4ade80'],
                ['label'=>'Propostas Aceitas','value'=>$summary['proposals_accepted'],'color'=>'#a78bfa'],
            ] : [
                ['label'=>'Clientes','value'=>$summary['clients'],'color'=>'#38bdf8'],
                ['label'=>'Campanhas Ativas','value'=>$summary['campaigns_active'],'color'=>'#4ade80'],
                ['label'=>'Aprovações Pendentes','value'=>$summary['approvals_pending'],'color'=>'#fde047'],
                ['label'=>'Logs Hoje','value'=>$summary['logs_today'],'color'=>'#fb923c'],
            ];
            @endphp
            @foreach($cards as $c)
            <div style="text-align:center;padding:14px;background:#0f172a;border-radius:10px;">
                <div style="font-size:28px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-top:4px;">{{ $c['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Social Media</h3>
                <a href="{{ route('client.reports.social') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Ver →</a>
            </div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px;">
                @foreach([['label'=>'Total','value'=>$social['total'],'color'=>'#38bdf8'],['label'=>'Publicados','value'=>$social['published'],'color'=>'#4ade80']] as $c)
                <div style="text-align:center;padding:10px;background:#0f172a;border-radius:8px;">
                    <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                    <div style="font-size:10px;color:#64748b;text-transform:uppercase;">{{ $c['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Campanhas</h3>
                <a href="{{ route('client.reports.campaigns') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Ver →</a>
            </div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px;">
                @foreach([['label'=>'Total','value'=>$campaigns['total'],'color'=>'#a78bfa'],['label'=>'Ativas','value'=>$campaigns['active'],'color'=>'#4ade80']] as $c)
                <div style="text-align:center;padding:10px;background:#0f172a;border-radius:8px;">
                    <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                    <div style="font-size:10px;color:#64748b;text-transform:uppercase;">{{ $c['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800 text-sm">Aprovações</h3>
                <a href="{{ route('client.reports.approvals') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Ver →</a>
            </div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px;">
                @foreach([['label'=>'Pendentes','value'=>$approvals['pending'],'color'=>'#fde047'],['label'=>'Aprovadas','value'=>$approvals['approved'],'color'=>'#4ade80']] as $c)
                <div style="text-align:center;padding:10px;background:#0f172a;border-radius:8px;">
                    <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                    <div style="font-size:10px;color:#64748b;text-transform:uppercase;">{{ $c['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
