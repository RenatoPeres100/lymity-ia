<x-layouts.app title="Logs IA">
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;">Logs IA</h1>
    <p style="color:#64748b;font-size:.875rem;margin-top:4px;">Histórico de execuções dos agentes IA.</p>
</div>
@if($logs->isEmpty())
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:40px;text-align:center;color:#64748b;">
    <div style="font-size:1rem;font-weight:600;margin-bottom:8px;">Nenhum log encontrado</div>
    <p style="font-size:.875rem;">Os logs dos agentes IA aparecerão aqui.</p>
</div>
@else
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Mensagem</th>
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Nível</th>
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Data</th>
        </tr></thead>
        <tbody>
        @foreach($logs as $l)
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:12px 16px;font-size:.8rem;color:#0f172a;">{{ Str::limit($l->message ?? $l->output ?? '—', 80) }}</td>
            <td style="padding:12px 16px;font-size:.75rem;color:#64748b;">{{ $l->level ?? '—' }}</td>
            <td style="padding:12px 16px;font-size:.75rem;color:#94a3b8;">{{ $l->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
</x-layouts.app>
