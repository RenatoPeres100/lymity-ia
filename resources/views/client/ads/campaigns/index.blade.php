<x-layouts.app title="Minhas Campanhas">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Minhas Campanhas</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Suas campanhas de mídia paga em planejamento</p>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Campanha</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Plataforma</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Objetivo</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Budget/dia</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaigns as $c)
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;">
                    <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $c->name }}</div>
                    @if($c->start_date)<div style="font-size:.75rem;color:#94a3b8;">{{ $c->start_date->format('d/m/Y') }}@if($c->end_date) — {{ $c->end_date->format('d/m/Y') }}@endif</div>@endif
                </td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">{{ $c->platform_label }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">{{ $c->objective_label }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;text-align:right;">R$ {{ number_format($c->daily_budget,2,',','.') }}</td>
                <td style="padding:.75rem 1rem;">
                    <span style="background:{{ match($c->status_color) { 'success'=>'#dcfce7', 'warning'=>'#fef9c3', 'danger'=>'#fee2e2', 'info'=>'#dbeafe', default=>'#f1f5f9' } }};color:{{ match($c->status_color) { 'success'=>'#166534', 'warning'=>'#92400e', 'danger'=>'#991b1b', 'info'=>'#1e40af', default=>'#475569' } }};padding:.25rem .5rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $c->status_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;">
                    <a href="{{ route('client.ads.campaigns.show', $c) }}" style="color:#6366f1;font-size:.8125rem;font-weight:500;">Ver detalhes</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma campanha encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">{{ $campaigns->links() }}</div>
</div>

</x-layouts.app>
