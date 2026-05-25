<x-layouts.app title="Mídia Paga — Visão Geral">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Campanhas de Mídia Paga</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Acompanhe o planejamento de suas campanhas publicitárias</p>
</div>

<div style="background:#fef9c3;border:1px solid #fde047;color:#92400e;padding:.625rem 1rem;border-radius:.5rem;margin-bottom:1.5rem;font-size:.8125rem;">
    📊 As campanhas estão em modo planejamento/sandbox. Nenhuma ação real foi executada nas plataformas de anúncio.
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    @foreach([
        ['Total de Campanhas', $stats['total'], '#6366f1'],
        ['Aprovadas', $stats['approved'], '#16a34a'],
        ['Aguardando Aprovação', $stats['pending'], '#ca8a04'],
        ['Ativas', $stats['active'], '#0ea5e9'],
    ] as [$label, $val, $color])
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;">{{ $label }}</div>
        <div style="font-size:2rem;font-weight:700;color:{{ $color }};margin-top:.5rem;">{{ $val }}</div>
    </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    @foreach([
        ['Impressões', number_format($totals->total_impressions ?? 0,0,',','.')],
        ['Cliques', number_format($totals->total_clicks ?? 0,0,',','.')],
        ['Leads Simulados', number_format($totals->total_leads ?? 0,0,',','.')],
        ['Custo Simulado', 'R$ '.number_format($totals->total_cost ?? 0,2,',','.')],
    ] as [$label, $val])
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;">{{ $label }}</div>
        <div style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $val }}</div>
    </div>
    @endforeach
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;">Campanhas Recentes</h3>
        <a href="{{ route('client.ads.campaigns.index') }}" style="font-size:.8125rem;color:#6366f1;">Ver todas →</a>
    </div>
    @forelse($recentCampaigns as $c)
    <div style="padding:.75rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <a href="{{ route('client.ads.campaigns.show', $c) }}" style="font-size:.875rem;font-weight:500;color:#6366f1;">{{ $c->name }}</a>
            <div style="font-size:.75rem;color:#94a3b8;">{{ $c->platform_label }} · {{ $c->objective_label }}</div>
        </div>
        <span style="background:{{ match($c->status_color) { 'success'=>'#dcfce7', 'warning'=>'#fef9c3', 'danger'=>'#fee2e2', default=>'#f1f5f9' } }};color:{{ match($c->status_color) { 'success'=>'#166534', 'warning'=>'#92400e', 'danger'=>'#991b1b', default=>'#475569' } }};padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $c->status_label }}</span>
    </div>
    @empty
    <div style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma campanha criada ainda.</div>
    @endforelse
</div>

</x-layouts.app>
