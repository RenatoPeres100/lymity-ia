<x-layouts.app title="Ads — Dashboard">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Ads — Campanhas de Mídia Paga</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Planejamento sandbox de Google Ads, Meta Ads e mais</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.ads.campaigns.create') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Nova Campanha</a>
        <a href="{{ route('admin.ads.accounts.create') }}" style="background:#0ea5e9;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Conta</a>
    </div>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="background:#fef9c3;border:1px solid #fde047;color:#92400e;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1.5rem;font-size:.875rem;">
    ⚠️ <strong>Modo Sandbox:</strong> Todas as campanhas estão em modo planejamento. Nenhuma ação real é executada em Google Ads, Meta Ads ou outras plataformas. Publicação real exige integração futura.
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Rascunhos</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['draft'] }}</div>
        <a href="{{ route('admin.ads.campaigns.index', ['status'=>'draft']) }}" style="font-size:.75rem;color:#6366f1;">Ver →</a>
    </div>
    <div style="background:#fff;border:1px solid #fef9c3;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#ca8a04;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Aguardando Aprovação</div>
        <div style="font-size:2rem;font-weight:700;color:#a16207;margin-top:.5rem;">{{ $stats['pending_approval'] }}</div>
        <a href="{{ route('admin.ads.campaigns.index', ['status'=>'pending_approval']) }}" style="font-size:.75rem;color:#6366f1;">Ver →</a>
    </div>
    <div style="background:#fff;border:1px solid #dcfce7;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#16a34a;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Aprovadas</div>
        <div style="font-size:2rem;font-weight:700;color:#15803d;margin-top:.5rem;">{{ $stats['approved'] }}</div>
        <a href="{{ route('admin.ads.campaigns.index', ['status'=>'approved']) }}" style="font-size:.75rem;color:#6366f1;">Ver →</a>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Orçamento Diário Total</div>
        <div style="font-size:1.5rem;font-weight:700;color:#1e293b;margin-top:.5rem;">R$ {{ number_format($stats['total_budget'],2,',','.') }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Leads Simulados</div>
        <div style="font-size:2rem;font-weight:700;color:#6366f1;margin-top:.5rem;">{{ number_format($stats['total_leads'],0,',','.') }}</div>
        <a href="{{ route('admin.ads.metrics.index') }}" style="font-size:.75rem;color:#6366f1;">Ver métricas →</a>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">CPA Médio Simulado</div>
        <div style="font-size:1.5rem;font-weight:700;color:#0ea5e9;margin-top:.5rem;">R$ {{ number_format($stats['avg_cpa'],2,',','.') }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Ativas (Sandbox)</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['active'] }}</div>
    </div>
</div>

{{-- Gerar com IA --}}
<div style="background:#f0f4ff;border:1px solid #c7d2fe;border-radius:.75rem;padding:1.25rem;margin-bottom:1.5rem;">
    <h3 style="font-size:.9375rem;font-weight:600;color:#3730a3;margin-bottom:1rem;">Gerar Campanha com Gestor de Tráfego IA</h3>
    <form method="POST" action="{{ route('admin.ads.campaigns.generate-ai') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end;">
        @csrf
        <div>
            <label style="font-size:.8125rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Plataforma</label>
            <select name="platform" style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                <option value="google_ads">Google Ads</option>
                <option value="meta_ads">Meta Ads</option>
            </select>
        </div>
        <div>
            <label style="font-size:.8125rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Objetivo</label>
            <select name="objective" style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                <option value="leads">Leads</option>
                <option value="sales">Vendas</option>
                <option value="traffic">Tráfego</option>
                <option value="awareness">Awareness</option>
                <option value="remarketing">Remarketing</option>
            </select>
        </div>
        <div>
            <label style="font-size:.8125rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Orçamento Diário (R$)</label>
            <input type="number" name="daily_budget" value="50" min="1" style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
        </div>
        <div>
            <label style="font-size:.8125rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Prompt (opcional)</label>
            <input type="text" name="prompt" placeholder="Ex: campanha de verão..." style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
        </div>
        <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;white-space:nowrap;">Gerar Campanha IA</button>
    </form>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    {{-- Recent Campaigns --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;">Campanhas Recentes</h3>
            <a href="{{ route('admin.ads.campaigns.index') }}" style="font-size:.8125rem;color:#6366f1;">Ver todas →</a>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:.625rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Campanha</th>
                    <th style="padding:.625rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Plataforma</th>
                    <th style="padding:.625rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Status</th>
                    <th style="padding:.625rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Budget/dia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentCampaigns as $c)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:.625rem 1rem;font-size:.875rem;">
                        <a href="{{ route('admin.ads.campaigns.show', $c) }}" style="color:#6366f1;font-weight:500;">{{ Str::limit($c->name, 30) }}</a>
                        @if($c->client)<div style="font-size:.75rem;color:#94a3b8;">{{ $c->client->name }}</div>@endif
                    </td>
                    <td style="padding:.625rem 1rem;font-size:.8125rem;color:#374151;">{{ $c->platform_label }}</td>
                    <td style="padding:.625rem 1rem;">
                        <span style="background:{{ match($c->status_color) { 'success'=>'#dcfce7', 'warning'=>'#fef9c3', 'danger'=>'#fee2e2', 'info'=>'#dbeafe', default=>'#f1f5f9' } }};color:{{ match($c->status_color) { 'success'=>'#166534', 'warning'=>'#92400e', 'danger'=>'#991b1b', 'info'=>'#1e40af', default=>'#475569' } }};padding:.25rem .5rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $c->status_label }}</span>
                    </td>
                    <td style="padding:.625rem 1rem;font-size:.875rem;">R$ {{ number_format($c->daily_budget,2,',','.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:1.5rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma campanha criada ainda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Budget Changes --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;">Aprovações de Orçamento</h3>
            <a href="{{ route('admin.ads.budget-approvals.index') }}" style="font-size:.8125rem;color:#6366f1;">Ver todas →</a>
        </div>
        @forelse($pendingBudgetChanges as $bc)
        <div style="padding:.75rem 1.25rem;border-top:1px solid #f1f5f9;">
            <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ Str::limit($bc->campaign?->name ?? '-', 25) }}</div>
            <div style="font-size:.75rem;color:#64748b;">R$ {{ number_format($bc->old_budget,2,',','.') }} → R$ {{ number_format($bc->new_budget,2,',','.') }}</div>
            <span style="background:#fef9c3;color:#92400e;padding:.125rem .5rem;border-radius:.25rem;font-size:.75rem;">Pendente</span>
        </div>
        @empty
        <div style="padding:1.5rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma aprovação pendente.</div>
        @endforelse
    </div>
</div>

</x-layouts.app>
