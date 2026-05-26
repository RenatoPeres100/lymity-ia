<x-layouts.app title="Relatório — SEO">
<div class="space-y-5">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.reports.index') }}" class="text-slate-500 hover:text-slate-800 text-sm">← Relatórios</a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Relatório SEO</h1>
            <p class="text-sm text-slate-400 mt-1">Keywords, clusters, auditorias e blog</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Keywords Total',   'value'=>$data['keywords_total'],  'color'=>'#38bdf8'],
            ['label'=>'Keywords Ativas',  'value'=>$data['keywords_active'], 'color'=>'#4ade80'],
            ['label'=>'Clusters',         'value'=>$data['clusters_total'],  'color'=>'#a78bfa'],
            ['label'=>'Blog Publicados',  'value'=>$data['blog_published'],  'color'=>'#fb923c'],
        ] as $card)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:32px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
        @foreach([
            ['label'=>'Auditorias',     'value'=>$data['audits_total'],  'color'=>'#22d3ee'],
            ['label'=>'Score Médio',    'value'=>$data['avg_score'],     'color'=>'#fde047'],
            ['label'=>'Blog Total',     'value'=>$data['blog_total'],    'color'=>'#94a3b8'],
        ] as $card)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:28px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    {{-- By priority --}}
    @if(count($data['by_priority']))
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-slate-800 text-sm">Keywords por Prioridade</h3></div>
        <div class="card-body" style="padding:20px;">
            @php $max = max($data['by_priority']) ?: 1; @endphp
            @foreach($data['by_priority'] as $priority => $count)
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span style="font-size:13px;color:#334155;">{{ ucfirst($priority) }}</span>
                    <span style="font-size:13px;color:#94a3b8;">{{ $count }}</span>
                </div>
                <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:{{ round($count/$max*100) }}%;background:#38bdf8;border-radius:4px;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent keywords --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-slate-800 text-sm">Keywords Recentes</h3></div>
        <div class="card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Keyword</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Intenção</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Prioridade</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Volume</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Dificuldade</th>
                        <th style="text-align:left;padding:8px 12px;color:#64748b;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['recent_keywords'] as $kw)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 12px;color:#334155;font-weight:500;">{{ $kw->keyword }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ ucfirst($kw->search_intent) }}</td>
                        <td style="padding:8px 12px;"><span class="badge badge-{{ $kw->priority }}">{{ ucfirst($kw->priority) }}</span></td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ number_format($kw->volume ?? 0) }}</td>
                        <td style="padding:8px 12px;color:#94a3b8;">{{ $kw->difficulty ?? '—' }}</td>
                        <td style="padding:8px 12px;"><span class="badge badge-{{ $kw->status }}">{{ ucfirst($kw->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:24px;text-align:center;color:#475569;">Nenhuma keyword encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</x-layouts.app>
