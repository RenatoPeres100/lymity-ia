<x-layouts.app title="Relatório — IA">
<div class="space-y-5">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.reports.index') }}" class="text-slate-400 hover:text-white text-sm">← Relatórios</a>
        <div>
            <h1 class="text-2xl font-bold text-white">Relatório Inteligência IA</h1>
            <p class="text-sm text-slate-400 mt-1">Funcionários, tarefas, memórias e agendas</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Funcionários IA',  'value'=>$data['employees_total'],  'color'=>'#a78bfa'],
            ['label'=>'Ativos',           'value'=>$data['employees_active'], 'color'=>'#4ade80'],
            ['label'=>'Tarefas Total',    'value'=>$data['tasks_total'],      'color'=>'#38bdf8'],
            ['label'=>'Tarefas Hoje',     'value'=>$data['tasks_today'],      'color'=>'#fb923c'],
        ] as $card)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:32px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        @foreach([
            ['label'=>'Na Fila',      'value'=>$data['tasks_queued'],  'color'=>'#fde047'],
            ['label'=>'Executando',   'value'=>$data['tasks_running'], 'color'=>'#38bdf8'],
            ['label'=>'Concluídas',   'value'=>$data['tasks_done'],    'color'=>'#4ade80'],
            ['label'=>'Falhas',       'value'=>$data['tasks_failed'],  'color'=>'#f87171'],
        ] as $card)
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:32px;font-weight:800;color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">{{ $card['label'] }}</div>
        </div></div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:28px;font-weight:800;color:#22d3ee;">{{ $data['memories'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">Memórias IA</div>
        </div></div>
        <div class="card"><div class="card-body" style="padding:16px;">
            <div style="font-size:28px;font-weight:800;color:#f472b6;">{{ $data['schedules_active'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;">Agendas Ativas</div>
        </div></div>
    </div>

    {{-- Tasks by type --}}
    @if(count($data['by_type']))
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-white text-sm">Tarefas por Tipo</h3></div>
        <div class="card-body" style="padding:20px;">
            @php $max = max($data['by_type']) ?: 1; @endphp
            @foreach($data['by_type'] as $type => $count)
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span style="font-size:13px;color:#e2e8f0;">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                    <span style="font-size:13px;color:#94a3b8;">{{ $count }}</span>
                </div>
                <div style="height:8px;background:#1e293b;border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:{{ round($count/$max*100) }}%;background:#a78bfa;border-radius:4px;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        {{-- Recent tasks --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-white text-sm">Tarefas Recentes</h3></div>
            <div class="card-body">
                @forelse($data['recent_tasks'] as $task)
                <div style="padding:8px 0;border-bottom:1px solid #1e293b;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div class="text-sm text-slate-200">{{ Str::limit($task->title, 36) }}</div>
                            <div class="text-xs text-slate-500">{{ $task->aiEmployee?->name ?? '—' }}</div>
                        </div>
                        <span class="badge badge-{{ $task->status }}">{{ ucfirst($task->status) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500">Nenhuma tarefa recente.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent errors --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-white text-sm">Falhas Recentes</h3></div>
            <div class="card-body">
                @forelse($data['recent_errors'] as $task)
                <div style="padding:8px 0;border-bottom:1px solid #1e293b;">
                    <div class="text-sm text-slate-200">{{ Str::limit($task->title, 36) }}</div>
                    <div class="text-xs text-red-400 mt-1">{{ Str::limit($task->error_message ?? 'Erro desconhecido', 60) }}</div>
                </div>
                @empty
                <p class="text-sm text-slate-500 py-4">Nenhuma falha registrada.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
</x-layouts.app>
