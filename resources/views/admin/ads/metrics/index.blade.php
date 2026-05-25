<x-layouts.app title="Métricas de Campanhas">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Métricas de Campanhas</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Dados simulados — modo sandbox</p>
    </div>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

{{-- Totals --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:.75rem;margin-bottom:1.5rem;">
    @foreach([
        ['Impressões', number_format($totals->total_impressions ?? 0,0,',','.')],
        ['Cliques', number_format($totals->total_clicks ?? 0,0,',','.')],
        ['Custo', 'R$ '.number_format($totals->total_cost ?? 0,2,',','.')],
        ['Leads', number_format($totals->total_leads ?? 0,0,',','.')],
        ['Conversões', number_format($totals->total_conversions ?? 0,0,',','.')],
        ['Receita', 'R$ '.number_format($totals->total_revenue ?? 0,2,',','.')],
    ] as [$label, $val])
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;text-align:center;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;">{{ $label }}</div>
        <div style="font-size:1.125rem;font-weight:700;color:#1e293b;margin-top:.375rem;">{{ $val }}</div>
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
            <option value="{{ $c->id }}" {{ request('campaign_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} {{ $c->client ? '('.$c->client->name.')' : '' }}</option>
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
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">CTR</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Custo</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">CPC</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Leads</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">CPA</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">ROAS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($metrics as $m)
            <tr style="border-top:1px solid #f1f5f9;@if($loop->even)background:#fafafa;@endif">
                <td style="padding:.625rem 1rem;font-size:.8125rem;font-weight:500;color:#374151;">{{ Str::limit($m->campaign?->name ?? '—', 25) }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;color:#374151;">{{ $m->date->format('d/m/Y') }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">{{ number_format($m->impressions,0,',','.') }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">{{ number_format($m->clicks,0,',','.') }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">{{ $m->ctr }}%</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">R$ {{ number_format($m->cost,2,',','.') }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">R$ {{ number_format($m->cpc ?? 0,2,',','.') }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">{{ number_format($m->leads,0,',','.') }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">R$ {{ number_format($m->cpa ?? 0,2,',','.') }}</td>
                <td style="padding:.625rem 1rem;font-size:.8125rem;text-align:right;">{{ $m->roas ? $m->roas.'x' : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma métrica registrada ainda.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">{{ $metrics->links() }}</div>
</div>

</x-layouts.app>
