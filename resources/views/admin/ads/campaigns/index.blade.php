<x-layouts.app title="Campanhas de Anúncio">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Campanhas de Anúncio</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Planejamento sandbox de campanhas de mídia paga</p>
    </div>
    <a href="{{ route('admin.ads.campaigns.create') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Nova Campanha</a>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

{{-- Filters --}}
<form method="GET" style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;margin-bottom:1rem;display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end;">
    <div>
        <label style="font-size:.75rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Cliente</label>
        <select name="client_id" style="width:100%;padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.8125rem;">
            <option value="">Todos os clientes</option>
            @foreach($clients as $cl)
            <option value="{{ $cl->id }}" {{ request('client_id') == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:.75rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Plataforma</label>
        <select name="platform" style="width:100%;padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.8125rem;">
            <option value="">Todas</option>
            <option value="google_ads" {{ request('platform') === 'google_ads' ? 'selected' : '' }}>Google Ads</option>
            <option value="meta_ads" {{ request('platform') === 'meta_ads' ? 'selected' : '' }}>Meta Ads</option>
            <option value="linkedin_ads" {{ request('platform') === 'linkedin_ads' ? 'selected' : '' }}>LinkedIn Ads</option>
            <option value="tiktok_ads" {{ request('platform') === 'tiktok_ads' ? 'selected' : '' }}>TikTok Ads</option>
        </select>
    </div>
    <div>
        <label style="font-size:.75rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Objetivo</label>
        <select name="objective" style="width:100%;padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.8125rem;">
            <option value="">Todos</option>
            @foreach(['leads'=>'Leads','sales'=>'Vendas','traffic'=>'Tráfego','awareness'=>'Awareness','engagement'=>'Engajamento','app'=>'App','remarketing'=>'Remarketing'] as $v=>$l)
            <option value="{{ $v }}" {{ request('objective') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:.75rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Status</label>
        <select name="status" style="width:100%;padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.8125rem;">
            <option value="">Todos</option>
            @foreach(['draft'=>'Rascunho','pending_approval'=>'Aguardando Aprovação','approved'=>'Aprovada','scheduled'=>'Agendada','active'=>'Ativa','paused'=>'Pausada','completed'=>'Concluída','rejected'=>'Rejeitada'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
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
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Plataforma</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Objetivo</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Budget/dia</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Período</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaigns as $c)
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;">
                    <a href="{{ route('admin.ads.campaigns.show', $c) }}" style="color:#6366f1;font-weight:500;font-size:.875rem;">{{ Str::limit($c->name, 35) }}</a>
                    @if($c->client)<div style="font-size:.75rem;color:#94a3b8;">{{ $c->client->name }}</div>@endif
                </td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">{{ $c->platform_label }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">{{ $c->objective_label }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#374151;">R$ {{ number_format($c->daily_budget,2,',','.') }}</td>
                <td style="padding:.75rem 1rem;">
                    <span style="background:{{ match($c->status_color) { 'success'=>'#dcfce7', 'warning'=>'#fef9c3', 'danger'=>'#fee2e2', 'info'=>'#dbeafe', 'primary'=>'#ede9fe', default=>'#f1f5f9' } }};color:{{ match($c->status_color) { 'success'=>'#166534', 'warning'=>'#92400e', 'danger'=>'#991b1b', 'info'=>'#1e40af', 'primary'=>'#5b21b6', default=>'#475569' } }};padding:.25rem .5rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $c->status_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;font-size:.75rem;color:#64748b;">
                    @if($c->start_date){{ $c->start_date->format('d/m') }}@if($c->end_date) — {{ $c->end_date->format('d/m') }}@endif
                    @else —
                    @endif
                </td>
                <td style="padding:.75rem 1rem;">
                    <a href="{{ route('admin.ads.campaigns.show', $c) }}" style="color:#6366f1;font-size:.8125rem;font-weight:500;">Ver</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma campanha encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">{{ $campaigns->links() }}</div>
</div>

</x-layouts.app>
