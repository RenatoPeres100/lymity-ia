@extends('components.layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Relatório Executivo</h1>
            <p class="text-sm text-slate-400 mt-1">Visão completa da operação — gerado em {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline text-sm">← Central de Relatórios</a>
    </div>

    {{-- Operação geral --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-white">Operação Geral</h3></div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:16px;">
            @foreach([
                ['label'=>'Clientes',           'value'=>$summary['clients'],              'color'=>'#38bdf8'],
                ['label'=>'Usuários Ativos',     'value'=>$summary['users_active'],         'color'=>'#4ade80'],
                ['label'=>'Funcionários IA',     'value'=>$summary['ai_employees_active'],  'color'=>'#a78bfa'],
                ['label'=>'Logs Hoje',           'value'=>$summary['logs_today'],           'color'=>'#fb923c'],
            ] as $c)
            <div style="text-align:center;padding:12px;background:#0f172a;border-radius:10px;">
                <div style="font-size:28px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-top:4px;">{{ $c['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Social --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-white">Social Media</h3>
            <a href="{{ route('admin.reports.social') }}" class="text-xs text-sky-400">Relatório completo →</a>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px;">
            @foreach([
                ['label'=>'Total','value'=>$social['total'],'color'=>'#38bdf8'],
                ['label'=>'Publicados','value'=>$social['published'],'color'=>'#4ade80'],
                ['label'=>'Agendados','value'=>$social['scheduled'],'color'=>'#fde047'],
                ['label'=>'Pendentes','value'=>$social['pending'],'color'=>'#fb923c'],
            ] as $c)
            <div style="text-align:center;padding:12px;background:#0f172a;border-radius:10px;">
                <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-top:4px;">{{ $c['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Campaigns --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-white">Campanhas</h3>
            <a href="{{ route('admin.reports.campaigns') }}" class="text-xs text-sky-400">Relatório completo →</a>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px;">
            @foreach([
                ['label'=>'Total','value'=>$campaigns['total'],'color'=>'#a78bfa'],
                ['label'=>'Ativas','value'=>$campaigns['active'],'color'=>'#4ade80'],
                ['label'=>'Impressões','value'=>number_format($campaigns['total_impressions']),'color'=>'#38bdf8'],
                ['label'=>'Leads','value'=>number_format($campaigns['total_leads']),'color'=>'#22d3ee'],
            ] as $c)
            <div style="text-align:center;padding:12px;background:#0f172a;border-radius:10px;">
                <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-top:4px;">{{ $c['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- SEO --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-white">SEO</h3>
            <a href="{{ route('admin.reports.seo') }}" class="text-xs text-sky-400">Relatório completo →</a>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px;">
            @foreach([
                ['label'=>'Keywords','value'=>$seo['keywords_total'],'color'=>'#38bdf8'],
                ['label'=>'Clusters','value'=>$seo['clusters_total'],'color'=>'#a78bfa'],
                ['label'=>'Blog Publicados','value'=>$seo['blog_published'],'color'=>'#4ade80'],
                ['label'=>'Score Médio','value'=>$seo['avg_score'],'color'=>'#fde047'],
            ] as $c)
            <div style="text-align:center;padding:12px;background:#0f172a;border-radius:10px;">
                <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-top:4px;">{{ $c['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Approvals --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-white">Aprovações</h3>
            <a href="{{ route('admin.reports.approvals') }}" class="text-xs text-sky-400">Relatório completo →</a>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px;">
            @foreach([
                ['label'=>'Total','value'=>$approvals['total'],'color'=>'#38bdf8'],
                ['label'=>'Pendentes','value'=>$approvals['pending'],'color'=>'#fde047'],
                ['label'=>'Aprovadas','value'=>$approvals['approved'],'color'=>'#4ade80'],
                ['label'=>'Críticas','value'=>$approvals['critical'],'color'=>'#f87171'],
            ] as $c)
            <div style="text-align:center;padding:12px;background:#0f172a;border-radius:10px;">
                <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-top:4px;">{{ $c['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- IA --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-white">Inteligência IA</h3>
            <a href="{{ route('admin.reports.ai') }}" class="text-xs text-sky-400">Relatório completo →</a>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px;">
            @foreach([
                ['label'=>'Funcionários','value'=>$ai['employees_total'],'color'=>'#a78bfa'],
                ['label'=>'Tarefas Total','value'=>$ai['tasks_total'],'color'=>'#38bdf8'],
                ['label'=>'Concluídas','value'=>$ai['tasks_done'],'color'=>'#4ade80'],
                ['label'=>'Falhas','value'=>$ai['tasks_failed'],'color'=>'#f87171'],
            ] as $c)
            <div style="text-align:center;padding:12px;background:#0f172a;border-radius:10px;">
                <div style="font-size:22px;font-weight:800;color:{{ $c['color'] }};">{{ $c['value'] }}</div>
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-top:4px;">{{ $c['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
