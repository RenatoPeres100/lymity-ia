<x-layouts.app title="Logs IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Logs IA</h1>
        <p style="font-size:.85rem;color:#64748b;">Histórico de execução dos funcionários IA.</p>
    </div>
    <div style="font-size:.8rem;color:#64748b;background:#f1f5f9;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;">
        Total: <strong style="color:#334155;">{{ $logs->total() }}</strong>
    </div>
</div>

<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <form method="GET" style="display:flex;gap:10px;align-items:center;">
        <select name="level" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os níveis</option>
            @foreach(['info'=>'Info','success'=>'Sucesso','warning'=>'Aviso','error'=>'Erro'] as $val=>$label)
            <option value="{{ $val }}" {{ request('level')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="employee" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os funcionários</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('employee')==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
            @endforeach
        </select>
        <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">Filtrar</button>
        @if(request()->hasAny(['level','employee']))
        <a href="{{ route('admin.ai-logs.index') }}" style="color:#6b8fff;font-size:.8rem;text-decoration:none;">Limpar</a>
        @endif
    </form>
</div>

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Nível</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Mensagem</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Tarefa</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Modelo</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Tokens</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Custo est.</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Funcionário</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Data/Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            @php
                $colors = ['success'=>['#052e16','#4ade80'],'warning'=>['#451a03','#fb923c'],'error'=>['#2d0a0a','#f87171'],'info'=>['#0c1a2e','#60a5fa']];
                $lc = $colors[$log->level] ?? ['#0c1a2e','#60a5fa'];
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 16px;">
                    <span style="background:{{ $lc[0] }};color:{{ $lc[1] }};font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:10px;text-transform:uppercase;">{{ $log->level }}</span>
                </td>
                <td style="padding:10px 16px;font-size:.8rem;color:#334155;max-width:400px;">{{ $log->message }}</td>
                <td style="padding:10px 16px;font-size:.78rem;color:#6b8fff;">
                    @if($log->task)
                        <a href="{{ route('admin.ai-tasks.show', $log->task) }}" style="color:#6b8fff;text-decoration:none;">{{ mb_substr($log->task->title, 0, 40) }}</a>
                    @elseif($log->task_type)
                        @php
                            $taskLabels = [
                                'generate_brief' => 'Gerar briefing',
                                'generate_draft' => 'Gerar artigo',
                            ];
                            $taskLabel = $taskLabels[$log->task_type] ?? $log->task_type;
                            $relatedUrl = null;
                            if ($log->related_type === 'App\Models\BlogPost' && $log->related_id) {
                                $relatedUrl = route('admin.blog.posts.show', $log->related_id);
                            } elseif ($log->related_type === 'App\Models\ContentBrief' && $log->related_id) {
                                $relatedUrl = route('admin.blog.briefs.show', $log->related_id);
                            }
                        @endphp
                        @if($relatedUrl)
                            <a href="{{ $relatedUrl }}" style="color:#6b8fff;text-decoration:none;">{{ $taskLabel }} #{{ $log->related_id }}</a>
                        @else
                            <span style="color:#64748b;">{{ $taskLabel }}</span>
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td style="padding:10px 16px;font-size:.72rem;color:#94a3b8;font-family:monospace;">{{ $log->model ?? '—' }}</td>
                <td style="padding:10px 16px;font-size:.75rem;color:#475569;text-align:right;">
                    @php $total = ($log->input_tokens ?? 0) + ($log->output_tokens ?? 0); @endphp
                    {{ $total > 0 ? number_format($total) : '—' }}
                </td>
                <td style="padding:10px 16px;font-size:.75rem;color:#475569;text-align:right;">
                    {{ $log->estimated_cost_usd ? '$'.number_format($log->estimated_cost_usd, 5) : '—' }}
                </td>
                <td style="padding:10px 16px;font-size:.78rem;color:#94a3b8;">{{ $log->employee?->name ?? '—' }}</td>
                <td style="padding:10px 16px;font-size:.75rem;color:#475569;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="padding:48px;text-align:center;color:#475569;font-size:.875rem;">Nenhum log registrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($logs->hasPages())
<div style="margin-top:20px;">{{ $logs->links() }}</div>
@endif

</x-layouts.app>
