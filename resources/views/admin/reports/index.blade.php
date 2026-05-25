@extends('components.layouts.app')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Central de Relatórios</h1>
        <p class="text-sm text-slate-400 mt-1">Visão consolidada da operação</p>
    </div>

    {{-- Summary Stats --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Clientes',          'value'=>$summary['clients'],              'color'=>'#38bdf8'],
            ['label'=>'Campanhas Ativas',  'value'=>$summary['campaigns_active'],     'color'=>'#4ade80'],
            ['label'=>'Aprovi. Pendentes', 'value'=>$summary['approvals_pending'],    'color'=>'#fde047'],
            ['label'=>'Logs Hoje',         'value'=>$summary['logs_today'],           'color'=>'#a78bfa'],
        ] as $card)
        <div class="card">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:32px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
                <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Report Navigation --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
        @foreach([
            ['icon'=>'◈', 'label'=>'Social Media',  'desc'=>'Posts, status, agendamentos', 'route'=>'admin.reports.social',    'color'=>'#38bdf8'],
            ['icon'=>'▲', 'label'=>'Campanhas',      'desc'=>'Performance, métricas, ROAS',  'route'=>'admin.reports.campaigns', 'color'=>'#a78bfa'],
            ['icon'=>'◎', 'label'=>'SEO',            'desc'=>'Keywords, clusters, auditorias','route'=>'admin.reports.seo',       'color'=>'#4ade80'],
            ['icon'=>'⚙', 'label'=>'Inteligência IA','desc'=>'Tarefas, agentes, memórias',   'route'=>'admin.reports.ai',        'color'=>'#fb923c'],
            ['icon'=>'✓', 'label'=>'Aprovações',     'desc'=>'Status, histórico, críticas',  'route'=>'admin.reports.approvals', 'color'=>'#fde047'],
            ['icon'=>'★', 'label'=>'Executivo',      'desc'=>'Resumo completo da operação',  'route'=>'admin.reports.executive', 'color'=>'#f472b6'],
        ] as $r)
        <a href="{{ route($r['route']) }}" class="card" style="text-decoration:none;display:block;transition:border-color .2s;" onmouseover="this.style.borderColor='{{ $r['color'] }}'" onmouseout="this.style.borderColor='#334155'">
            <div class="card-body" style="padding:20px;">
                <div style="font-size:28px;margin-bottom:10px;color:{{ $r['color'] }};">{{ $r['icon'] }}</div>
                <div class="font-semibold text-white text-sm">{{ $r['label'] }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $r['desc'] }}</div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Quick links to logs --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-white text-sm">Logs do Sistema</h3></div>
        <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline text-sm">Todos os Logs</a>
            <a href="{{ route('admin.security-logs.index') }}" class="btn btn-outline text-sm">Logs de Segurança</a>
            <a href="{{ route('admin.ai-logs.index') }}" class="btn btn-outline text-sm">Logs IA</a>
            <a href="{{ route('admin.activity-logs.export') }}" class="btn btn-outline text-sm">⬇ Exportar CSV</a>
        </div>
    </div>

</div>
@endsection
