<x-layouts.app title="Relatórios de Ads">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Relatórios de Mídia Paga</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Métricas simuladas das suas campanhas</p>
</div>

<div style="background:#fef9c3;border:1px solid #fde047;color:#92400e;padding:.625rem 1rem;border-radius:.5rem;margin-bottom:1.5rem;font-size:.8125rem;">
    📊 Dados simulados em modo sandbox. Integração real com plataformas de anúncio será habilitada em fase futura.
</div>

{{-- Totals --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    @foreach([
        ['Impressões Totais', number_format($totals->total_impressions ?? 0,0,',','.')],
        ['Cliques Totais', number_format($totals->total_clicks ?? 0,0,',','.')],
        ['Leads Gerados', number_format($totals->total_leads ?? 0,0,',','.')],
        ['Custo Total', 'R$ '.number_format($totals->total_cost ?? 0,2,',','.')],
        ['Conversões', number_format($totals->total_conversions ?? 0,0,',','.')],
        ['Receita Estimada', 'R$ '.number_format($totals->total_revenue ?? 0,2,',','.')],
    ] as [$label, $val])
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;">{{ $label }}</div>
        <div style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<form method="GET" style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;margin-bottom:1rem;display:flex;gap:.75rem;align-items:end;">
    <div>
        <label style="font-size:.75rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Campanha</label>
        <select name="campaign_id" style="padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.8125rem;">
            <option value="">Todas as campanhas</option>
            @foreach($campaigns as $c)
            <option value="{{ $c->id }}" {{ request('campaign_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" style="background:#0ea5e9;color:#fff;padding:.4rem 1rem;border-radius:.375rem;font-size:.8125rem;font-weight:500;border:none;cursor:pointer;">Filtrar</button>
</form>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Campanha</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Data</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Impressões</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Cliques</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Leads</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Custo</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">CPA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($metrics as $m)
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;font-size:.8125rem;font-weight:500;color:#374151;">{{ Str::limit($m->campaign?->name ?? '—', 30) }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">{{ $m->date->format('d/m/Y') }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;text-align:right;">{{ number_format($m->impressions,0,',','.') }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;text-align:right;">{{ number_format($m->clicks,0,',','.') }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;text-align:right;">{{ number_format($m->leads,0,',','.') }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;text-align:right;">R$ {{ number_format($m->cost,2,',','.') }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;text-align:right;">R$ {{ number_format($m->cpa ?? 0,2,',','.') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma métrica disponível.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($metrics->hasPages())
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">{{ $metrics->links() }}</div>
    @endif
</div>

</x-layouts.app>
