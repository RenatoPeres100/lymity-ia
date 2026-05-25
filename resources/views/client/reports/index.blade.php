@extends('components.layouts.app')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Relatórios</h1>
        <p class="text-sm text-slate-400 mt-1">{{ $client?->name ?? 'Visão geral' }} — {{ now()->format('d/m/Y') }}</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @php
        $cards = $client ? [
            ['label'=>'Aprovações Pendentes', 'value'=>$summary['approvals_pending'], 'color'=>'#fde047'],
            ['label'=>'Posts Sociais',         'value'=>$summary['social_posts'],      'color'=>'#38bdf8'],
            ['label'=>'Campanhas',             'value'=>$summary['campaigns'],         'color'=>'#a78bfa'],
            ['label'=>'Keywords SEO',          'value'=>$summary['seo_keywords'],      'color'=>'#4ade80'],
        ] : [
            ['label'=>'Clientes',     'value'=>$summary['clients'],           'color'=>'#38bdf8'],
            ['label'=>'Campanhas',    'value'=>$summary['campaigns'],         'color'=>'#a78bfa'],
            ['label'=>'Posts Sociais','value'=>$summary['social_posts'],      'color'=>'#4ade80'],
            ['label'=>'Logs Hoje',    'value'=>$summary['logs_today'],        'color'=>'#fb923c'],
        ];
        @endphp
        @foreach($cards as $c)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:32px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $c['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
        @foreach([
            ['icon'=>'◈', 'label'=>'Social Media',  'desc'=>'Posts, status, agendamentos', 'route'=>'client.reports.social',    'color'=>'#38bdf8'],
            ['icon'=>'▲', 'label'=>'Campanhas',      'desc'=>'Performance e métricas',      'route'=>'client.reports.campaigns', 'color'=>'#a78bfa'],
            ['icon'=>'◎', 'label'=>'SEO',            'desc'=>'Keywords, clusters, blog',    'route'=>'client.reports.seo',       'color'=>'#4ade80'],
            ['icon'=>'✓', 'label'=>'Aprovações',     'desc'=>'Status e histórico',          'route'=>'client.reports.approvals', 'color'=>'#fde047'],
        ] as $r)
        <a href="{{ route($r['route']) }}" class="card" style="text-decoration:none;display:block;" onmouseover="this.style.borderColor='{{ $r['color'] }}'" onmouseout="this.style.borderColor='#334155'">
            <div class="card-body" style="padding:20px;">
                <div style="font-size:28px;color:{{ $r['color'] }};margin-bottom:8px;">{{ $r['icon'] }}</div>
                <div class="font-semibold text-white text-sm">{{ $r['label'] }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $r['desc'] }}</div>
            </div>
        </a>
        @endforeach
    </div>

</div>
@endsection
