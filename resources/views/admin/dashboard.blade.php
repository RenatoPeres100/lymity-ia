<x-layouts.app title="Dashboard">
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <p class="text-sm text-slate-400 mt-1">{{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.operation') }}" class="btn btn-primary text-sm">Central de Operação</a>
    </div>

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;" class="md:grid-cols-6">
        @php
        $cards = [
            ['label'=>'Aprovi. Pendentes',   'value'=>$summary['approvals_pending'],    'color'=>'#fde047'],
            ['label'=>'Aprovi. Críticas',    'value'=>$summary['approvals_critical'],   'color'=>'#f87171'],
            ['label'=>'Funcionários IA',     'value'=>$summary['ai_employees_active'],  'color'=>'#a78bfa'],
            ['label'=>'Tarefas IA Hoje',     'value'=>$summary['ai_tasks_today'],       'color'=>'#fb923c'],
            ['label'=>'Posts Sociais',       'value'=>$summary['social_posts'],         'color'=>'#34d399'],
            ['label'=>'Blogs Publicados',    'value'=>$summary['blog_posts_published'], 'color'=>'#a3e635'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="card">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:28px;font-weight:800;color:{{ $card['color'] }};line-height:1;">{{ $card['value'] }}</div>
                <div style="font-size:11px;color:#64748b;margin-top:4px;text-transform:uppercase;letter-spacing:.05em;">{{ $card['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Row 2: Recent Approvals + AI Tasks --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-white text-sm">Últimas Aprovações</h3>
                <a href="{{ route('admin.approvals.index') }}" class="text-xs text-sky-400">Ver todas →</a>
            </div>
            <div class="card-body">
                @forelse($recentApprovals as $a)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1e293b;">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ Str::limit($a->title, 40) }}</div>
                        <div class="text-xs text-slate-500">{{ $a->client?->name ?? '—' }} · {{ $a->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500 py-4">Nenhuma aprovação recente.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-white text-sm">Últimas Tarefas IA</h3>
                <a href="{{ route('admin.ai-tasks.index') }}" class="text-xs text-sky-400">Ver todas →</a>
            </div>
            <div class="card-body">
                @forelse($recentTasks as $task)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1e293b;">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ Str::limit($task->title, 38) }}</div>
                        <div class="text-xs text-slate-500">{{ $task->aiEmployee?->name ?? '—' }} · {{ $task->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge badge-{{ $task->status }}">{{ ucfirst($task->status) }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500">Nenhuma tarefa recente.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Row 3: Scheduled Posts + Critical Logs --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-white text-sm">Próximas Publicações</h3>
                <a href="{{ route('admin.publishing-queue') }}" class="text-xs text-sky-400">Fila completa →</a>
            </div>
            <div class="card-body">
                @forelse($scheduledPosts as $post)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1e293b;">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ Str::limit(($post->main_caption ?? $post->title ?? $post->content) ?? '(post)', 38) }}</div>
                        <div class="text-xs text-slate-500">{{ $post->scheduled_at?->format('d/m H:i') }}</div>
                    </div>
                    <span class="badge badge-{{ $post->status }}">{{ ucfirst($post->status) }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500">Nenhum post agendado.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-white text-sm">Logs Críticos</h3>
                <a href="{{ route('admin.security-logs.index') }}" class="text-xs text-sky-400">Ver segurança →</a>
            </div>
            <div class="card-body">
                @forelse($criticalLogs as $log)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1e293b;">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ $log->action }}</div>
                        <div class="text-xs text-slate-500">{{ Str::limit($log->description, 50) }}</div>
                    </div>
                    <span class="badge badge-{{ $log->level ?? 'info' }}">{{ ucfirst($log->level ?? 'info') }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500 py-4">Nenhum log crítico.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
</x-layouts.app>
