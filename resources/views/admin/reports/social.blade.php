@extends('components.layouts.app')

@section('content')
<div class="space-y-5">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.reports.index') }}" class="text-slate-400 hover:text-white text-sm">← Relatórios</a>
        <div>
            <h1 class="text-2xl font-bold text-white">Relatório Social Media</h1>
            <p class="text-sm text-slate-400 mt-1">Todos os clientes</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Total Posts',    'value'=>$data['total'],     'color'=>'#38bdf8'],
            ['label'=>'Publicados',     'value'=>$data['published'], 'color'=>'#4ade80'],
            ['label'=>'Agendados',      'value'=>$data['scheduled'], 'color'=>'#fde047'],
            ['label'=>'Pendentes',      'value'=>$data['pending'],   'color'=>'#fb923c'],
        ] as $card)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:32px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    {{-- By type bars --}}
    @if(count($data['by_type']))
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-white text-sm">Posts por Tipo</h3></div>
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

    {{-- Recent posts --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-white text-sm">Posts Recentes</h3></div>
        <div class="card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Título</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Tipo</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Status</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Agendado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['recent'] as $post)
                    <tr style="border-bottom:1px solid #1e293b;">
                        <td style="padding:8px 12px;color:#e2e8f0;">{{ Str::limit($post->title, 45) }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ ucfirst($post->content_type) }}</td>
                        <td style="padding:8px 12px;"><span class="badge badge-{{ $post->status }}">{{ ucfirst($post->status) }}</span></td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ $post->scheduled_at?->format('d/m H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="padding:24px;text-align:center;color:#475569;">Nenhum post encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
