<x-layouts.app title="Rotinas IA">
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;">Rotinas dos Agentes IA</h1>
    <p style="color:#64748b;font-size:.875rem;margin-top:4px;">Automações programadas para o seu negócio.</p>
</div>
@if($routines->isEmpty())
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:40px;text-align:center;color:#64748b;">
    <div style="font-size:1rem;font-weight:600;margin-bottom:8px;">Nenhuma rotina configurada</div>
    <p style="font-size:.875rem;">Entre em contato com a agência para configurar rotinas automatizadas.</p>
</div>
@else
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Nome</th>
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Status</th>
            <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Agendamento</th>
        </tr></thead>
        <tbody>
        @foreach($routines as $r)
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:12px 16px;font-size:.875rem;color:#0f172a;font-weight:500;">{{ $r->name }}</td>
            <td style="padding:12px 16px;font-size:.8rem;">
                <span style="background:{{ $r->status==='active'?'#f0fdf4':'#f1f5f9' }};color:{{ $r->status==='active'?'#16a34a':'#64748b' }};padding:3px 10px;border-radius:5px;font-weight:600;">{{ $r->status }}</span>
            </td>
            <td style="padding:12px 16px;font-size:.8rem;color:#64748b;">{{ $r->cron_expression ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
</x-layouts.app>
