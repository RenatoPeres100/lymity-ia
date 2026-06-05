<x-layouts.app title="Tarefas IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Tarefas IA</h1>
        <p style="font-size:.85rem;color:#64748b;">Histórico e gestão de tarefas dos funcionários IA.</p>
    </div>
    <a href="{{ route('admin.ai-tasks.create') }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;text-decoration:none;">+ Nova Tarefa</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#991b1b;font-size:.875rem;">✗ {{ session('error') }}</div>
@endif

<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <form method="GET" style="display:flex;gap:10px;align-items:center;">
        <select name="status" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os status</option>
            @foreach(['queued'=>'Na fila','running'=>'Executando','waiting_approval'=>'Aguardando aprovação','completed'=>'Concluído','failed'=>'Falhou','canceled'=>'Cancelado','rejected'=>'Rejeitado'] as $val=>$label)
            <option value="{{ $val }}" {{ request('status')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="employee" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os funcionários</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('employee')==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
            @endforeach
        </select>
        <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">Filtrar</button>
        @if(request()->hasAny(['status','employee']))
        <a href="{{ route('admin.ai-tasks.index') }}" style="color:#6b8fff;font-size:.8rem;text-decoration:none;">Limpar</a>
        @endif
    </form>
</div>

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <th style="padding:12px 16px;width:40px;">
                    <input type="checkbox" id="bulk-select-all" title="Selecionar todos">
                </th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Tarefa</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Funcionário</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Prioridade</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Criada em</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr data-bulk-row style="border-bottom:1px solid #f1f5f9;transition:background .15s;cursor:pointer;">
                <td style="padding:12px 16px;">
                    <input type="checkbox" class="bulk-cb" value="{{ $task->id }}">
                </td>
                <td style="padding:12px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#334155;">{{ $task->title }}</div>
                    @if($task->task_type)<div style="font-size:.72rem;color:#475569;">{{ $task->task_type }}</div>@endif
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:#94a3b8;">{{ $task->employee?->name ?? '—' }}</td>
                <td style="padding:12px 16px;">
                    <span style="background:{{ $task->status_badge }};color:#fff;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:12px;">{{ $task->status_label }}</span>
                </td>
                <td style="padding:12px 16px;">
                    <span style="background:{{ $task->priority_badge }};color:#fff;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:12px;">{{ $task->priority_label }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.78rem;color:#475569;">{{ $task->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:12px 16px;text-align:right;">
                    <div style="display:flex;gap:10px;justify-content:flex-end;align-items:center;">
                        <a href="{{ route('admin.ai-tasks.show', $task) }}" style="color:#6b8fff;font-size:.78rem;font-weight:600;text-decoration:none;">Ver</a>
                        <form action="{{ route('admin.ai-tasks.destroy', $task) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir esta tarefa?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="color:#f87171;font-size:.78rem;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:48px;text-align:center;color:#475569;font-size:.875rem;">Nenhuma tarefa encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tasks->hasPages())
<div style="margin-top:20px;">{{ $tasks->links() }}</div>
@endif

<x-bulk-actions :action="route('admin.ai-tasks.bulk-delete')" />

</x-layouts.app>
