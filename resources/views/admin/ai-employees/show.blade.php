<x-layouts.app title="{{ $aiEmployee->name }}">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $aiEmployee->name }}</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.ai-employees.index') }}" style="color:#6b8fff;text-decoration:none;">Funcionários IA</a> / Detalhes</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="background:{{ $aiEmployee->status === 'active' ? '#052e16' : '#1e293b' }};color:{{ $aiEmployee->status === 'active' ? '#4ade80' : '#94a3b8' }};font-size:.78rem;font-weight:600;padding:6px 14px;border-radius:20px;">{{ $aiEmployee->getStatusLabelAttribute() }}</span>
        <a href="{{ route('admin.ai-employees.edit', $aiEmployee) }}" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:.8rem;font-weight:600;padding:7px 16px;border-radius:8px;text-decoration:none;">Editar</a>
        @if($aiEmployee->status !== 'active')
        <form method="POST" action="{{ route('admin.ai-employees.activate', $aiEmployee) }}" style="display:inline;">@csrf
            <button type="submit" style="background:#052e16;color:#4ade80;font-size:.8rem;font-weight:600;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;">Ativar</button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.ai-employees.pause', $aiEmployee) }}" style="display:inline;">@csrf
            <button type="submit" style="background:#451a03;color:#fb923c;font-size:.8rem;font-weight:600;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;">Pausar</button>
        </form>
        @endif
    </div>
</div>

@php $employee = $aiEmployee; @endphp

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:28px;">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
                <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#1e3a5f,#1a1a40);display:flex;align-items:center;justify-content:center;font-size:1.8rem;">{{ $employee->avatar_emoji }}</div>
                <div>
                    <div style="font-size:1.1rem;font-weight:700;color:#0f172a;">{{ $employee->name }}</div>
                    <div style="font-size:.8rem;color:#6b8fff;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">{{ $employee->role }}</div>
                </div>
            </div>
            <p style="font-size:.9rem;color:#94a3b8;line-height:1.8;margin-bottom:20px;">{{ $employee->description }}</p>
            @if($employee->routines)
            <div>
                <div style="font-size:.72rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Rotinas</div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach($employee->routines as $routine)
                    <div style="display:flex;gap:10px;align-items:flex-start;font-size:.8rem;">
                        <span style="color:#4a6cf7;flex-shrink:0;">›</span>
                        <span style="color:#94a3b8;">{{ $routine }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if($employee->tasks->isNotEmpty())
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">Tarefas Recentes</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <th style="text-align:left;padding:8px 0;font-size:.72rem;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Tarefa</th>
                        <th style="text-align:left;padding:8px 0;font-size:.72rem;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                        <th style="text-align:left;padding:8px 0;font-size:.72rem;color:#475569;text-transform:uppercase;letter-spacing:.06em;">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->tasks->take(10) as $task)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:10px 0;font-size:.8rem;color:#334155;">{{ $task->title }}</td>
                        <td style="padding:10px 0;">
                            @php $tc = ['pending'=>['#1e293b','#94a3b8'],'in_progress'=>['#0c2a3b','#22d3ee'],'completed'=>['#052e16','#4ade80'],'failed'=>['#2d0a0a','#f87171'],'awaiting_approval'=>['#451a03','#fb923c']]; $ts = $tc[$task->status] ?? $tc['pending']; @endphp
                            <span style="background:{{ $ts[0] }};color:{{ $ts[1] }};font-size:.68rem;font-weight:600;padding:2px 8px;border-radius:10px;">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span>
                        </td>
                        <td style="padding:10px 0;font-size:.75rem;color:#475569;">{{ $task->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Habilidades</div>
            @if($employee->skills->isNotEmpty())
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach($employee->skills as $skill)
                <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                    @if($skill->icon)<span style="font-size:1rem;">{{ $skill->icon }}</span>@endif
                    <div>
                        <div style="font-size:.8rem;font-weight:600;color:#334155;">{{ $skill->name }}</div>
                        @if($skill->category)<div style="font-size:.7rem;color:#64748b;">{{ $skill->category }}</div>@endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p style="font-size:.8rem;color:#475569;">Nenhuma habilidade mapeada.</p>
            @endif
        </div>

        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Permissões</div>
            @foreach([
                ['Pode publicar', $employee->can_publish],
                ['Pode enviar mensagens', $employee->can_send_messages],
                ['Pode gerenciar orçamento', $employee->can_manage_ads_budget],
                ['Requer aprovação', $employee->approval_required],
            ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:.8rem;color:#94a3b8;">{{ $label }}</span>
                <span style="font-size:.75rem;font-weight:600;color:{{ $value ? '#4ade80' : '#f87171' }};">{{ $value ? 'Sim' : 'Não' }}</span>
            </div>
            @endforeach
        </div>

        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Estatísticas</div>
            @foreach([
                ['Total de tarefas', $employee->tasks->count()],
                ['Concluídas', $employee->tasks->where('status','completed')->count()],
                ['Pendentes', $employee->tasks->whereIn('status',['pending','in_progress'])->count()],
                ['Aguardando aprovação', $employee->tasks->where('status','awaiting_approval')->count()],
            ] as [$label, $count])
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:.8rem;color:#94a3b8;">{{ $label }}</span>
                <span style="font-size:.875rem;font-weight:700;color:#334155;">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

</x-layouts.app>
