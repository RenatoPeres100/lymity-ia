<x-layouts.app title="Tarefas IA">
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;">Tarefas IA</h1>
    <p style="color:#64748b;font-size:.875rem;margin-top:4px;">Tarefas executadas pelos agentes IA para o seu negócio.</p>
</div>
@if($tasks->isEmpty())
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:40px;text-align:center;color:#64748b;">
    <div style="font-size:1rem;font-weight:600;margin-bottom:8px;">Nenhuma tarefa IA encontrada</div>
    <p style="font-size:.875rem;">As tarefas dos agentes IA aparecerão aqui.</p>
</div>
@else
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Tarefa</th>
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Status</th>
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Data</th>
        </tr></thead>
        <tbody>
        @foreach($tasks as $t)
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:12px 16px;font-size:.875rem;color:#0f172a;">{{ Str::limit($t->title ?? $t->task_type ?? '—', 60) }}</td>
            <td style="padding:12px 16px;font-size:.8rem;color:#64748b;">{{ $t->status ?? '—' }}</td>
            <td style="padding:12px 16px;font-size:.75rem;color:#94a3b8;">{{ $t->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
</x-layouts.app>
