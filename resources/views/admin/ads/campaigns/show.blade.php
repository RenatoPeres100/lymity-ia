<x-layouts.app title="{{ $adCampaign->name }}">

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <a href="{{ route('admin.ads.campaigns.index') }}" style="font-size:.875rem;color:#6366f1;">← Campanhas</a>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $adCampaign->name }}</h2>
        <div style="display:flex;gap:.5rem;margin-top:.5rem;flex-wrap:wrap;">
            <span style="background:#dbeafe;color:#1e40af;padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $adCampaign->platform_label }}</span>
            <span style="background:#f3e8ff;color:#6b21a8;padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $adCampaign->objective_label }}</span>
            <span style="background:{{ match($adCampaign->status_color) { 'success'=>'#dcfce7', 'warning'=>'#fef9c3', 'danger'=>'#fee2e2', 'info'=>'#dbeafe', 'primary'=>'#ede9fe', default=>'#f1f5f9' } }};color:{{ match($adCampaign->status_color) { 'success'=>'#166534', 'warning'=>'#92400e', 'danger'=>'#991b1b', 'info'=>'#1e40af', 'primary'=>'#5b21b6', default=>'#475569' } }};padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $adCampaign->status_label }}</span>
        </div>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <a href="{{ route('admin.ads.campaigns.edit', $adCampaign) }}" style="background:#f1f5f9;color:#374151;padding:.5rem 1rem;border-radius:.5rem;font-size:.8125rem;font-weight:500;text-decoration:none;">Editar</a>
        @if(in_array($adCampaign->status, ['draft']))
        <form method="POST" action="{{ route('admin.ads.campaigns.send-approval', $adCampaign) }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:#f59e0b;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.8125rem;font-weight:500;border:none;cursor:pointer;">Enviar Aprovação</button>
        </form>
        @endif
        @if(in_array($adCampaign->status, ['approved','active']))
        <form method="POST" action="{{ route('admin.ads.campaigns.pause-sandbox', $adCampaign) }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:#ef4444;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.8125rem;font-weight:500;border:none;cursor:pointer;">Pausar Sandbox</button>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.ads.campaigns.generate-mock-metrics', $adCampaign) }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.8125rem;font-weight:500;border:none;cursor:pointer;">Gerar Métricas Mock</button>
        </form>
    </div>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="background:#fef9c3;border:1px solid #fde047;color:#92400e;padding:.5rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.8125rem;">
    ⚠️ <strong>Modo Sandbox</strong> — Nenhuma ação real está sendo executada nesta campanha.
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div>
        {{-- Resumo --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Resumo da Estratégia</h3>
            <p style="font-size:.875rem;color:#374151;line-height:1.6;">{{ $adCampaign->strategy_summary ?: 'Nenhuma estratégia definida.' }}</p>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:1rem;">
                <div style="background:#f8fafc;border-radius:.5rem;padding:.75rem;">
                    <div style="font-size:.75rem;color:#64748b;">Orçamento Diário</div>
                    <div style="font-size:1rem;font-weight:700;color:#1e293b;">R$ {{ number_format($adCampaign->daily_budget,2,',','.') }}</div>
                </div>
                <div style="background:#f8fafc;border-radius:.5rem;padding:.75rem;">
                    <div style="font-size:.75rem;color:#64748b;">Orçamento Total</div>
                    <div style="font-size:1rem;font-weight:700;color:#1e293b;">R$ {{ number_format($adCampaign->total_budget ?? 0,2,',','.') }}</div>
                </div>
                <div style="background:#f8fafc;border-radius:.5rem;padding:.75rem;">
                    <div style="font-size:.75rem;color:#64748b;">Período</div>
                    <div style="font-size:.875rem;font-weight:600;color:#1e293b;">
                        {{ $adCampaign->start_date?->format('d/m/Y') ?? '—' }} → {{ $adCampaign->end_date?->format('d/m/Y') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Métricas --}}
        @if($summary['days_tracked'] > 0)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Métricas Simuladas ({{ $summary['days_tracked'] }} dias)</h3>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;">
                @foreach([
                    ['Impressões', number_format($summary['total_impressions'],0,',','.')],
                    ['Cliques', number_format($summary['total_clicks'],0,',','.')],
                    ['Custo', 'R$ '.number_format($summary['total_cost'],2,',','.')],
                    ['Leads', number_format($summary['total_leads'],0,',','.')],
                    ['CTR', $summary['avg_ctr'].'%'],
                    ['CPC', 'R$ '.number_format($summary['avg_cpc'],2,',','.')],
                    ['CPA', 'R$ '.number_format($summary['avg_cpa'],2,',','.')],
                    ['ROAS', $summary['avg_roas'].'x'],
                ] as [$label, $val])
                <div style="background:#f8fafc;border-radius:.5rem;padding:.75rem;text-align:center;">
                    <div style="font-size:.75rem;color:#64748b;">{{ $label }}</div>
                    <div style="font-size:1rem;font-weight:700;color:#1e293b;">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Groups --}}
        @if($adCampaign->groups->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Grupos de Anúncio ({{ $adCampaign->groups->count() }})</h3>
            @foreach($adCampaign->groups as $g)
            <div style="background:#f8fafc;border-radius:.5rem;padding:.75rem;margin-bottom:.5rem;">
                <div style="font-weight:600;font-size:.875rem;color:#374151;">{{ $g->name }}</div>
                @if($g->targeting_summary)<div style="font-size:.8125rem;color:#64748b;margin-top:.25rem;">{{ $g->targeting_summary }}</div>@endif
                <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">{{ $g->creatives->count() }} criativos · {{ $g->keywords->count() }} keywords</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Keywords --}}
        @if($adCampaign->keywords->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Keywords ({{ $adCampaign->keywords->count() }})</h3>
            <div style="display:flex;flex-wrap:wrap;gap:.375rem;">
                @foreach($adCampaign->keywords as $kw)
                <span style="background:{{ $kw->match_type === 'negative' ? '#fee2e2' : ($kw->match_type === 'exact' ? '#dcfce7' : ($kw->match_type === 'phrase' ? '#dbeafe' : '#f1f5f9')) }};color:{{ $kw->match_type === 'negative' ? '#991b1b' : ($kw->match_type === 'exact' ? '#166534' : ($kw->match_type === 'phrase' ? '#1e40af' : '#374151')) }};padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;">
                    {{ $kw->match_type === 'negative' ? '-' : '' }}{{ $kw->keyword }} <span style="opacity:.6;">[{{ $kw->match_type_label }}]</span>
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Creatives --}}
        @if($adCampaign->creatives->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Criativos ({{ $adCampaign->creatives->count() }})</h3>
            @foreach($adCampaign->creatives as $cr)
            <div style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.75rem;margin-bottom:.5rem;">
                <div style="font-weight:600;font-size:.875rem;color:#1e293b;">{{ $cr->title }}</div>
                @if($cr->headline)<div style="font-size:.8125rem;color:#6366f1;margin-top:.25rem;">{{ $cr->headline }}</div>@endif
                @if($cr->description)<div style="font-size:.8125rem;color:#64748b;margin-top:.25rem;">{{ $cr->description }}</div>@endif
                @if($cr->cta)<span style="background:#ede9fe;color:#5b21b6;padding:.125rem .5rem;border-radius:.25rem;font-size:.75rem;margin-top:.25rem;display:inline-block;">{{ $cr->cta }}</span>@endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Audiences --}}
        @if($adCampaign->audiences->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Públicos ({{ $adCampaign->audiences->count() }})</h3>
            @foreach($adCampaign->audiences as $aud)
            <div style="background:#f8fafc;border-radius:.5rem;padding:.75rem;margin-bottom:.5rem;">
                <div style="font-weight:600;font-size:.875rem;">{{ $aud->name }}</div>
                @if($aud->description)<div style="font-size:.8125rem;color:#64748b;margin-top:.25rem;">{{ $aud->description }}</div>@endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div>
        {{-- Info --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Informações</h3>
            <dl style="font-size:.875rem;">
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Conta</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->account?->account_name ?? '—' }}</dd>
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Cliente</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->client?->name ?? '—' }}</dd>
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Criado por</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->creator?->name ?? '—' }}</dd>
                @if($adCampaign->aiEmployee)
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Funcionário IA</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->aiEmployee->name }}</dd>
                @endif
                @if($adCampaign->approved_at)
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Aprovado por</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->approver?->name }} em {{ $adCampaign->approved_at->format('d/m/Y') }}</dd>
                @endif
            </dl>
        </div>

        {{-- Approvals --}}
        @if($adCampaign->approvalRequests->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Aprovações</h3>
            @foreach($adCampaign->approvalRequests as $ap)
            <div style="border:1px solid #f1f5f9;border-radius:.375rem;padding:.75rem;margin-bottom:.5rem;">
                <div style="font-size:.8125rem;font-weight:500;color:#374151;">{{ $ap->title }}</div>
                <span style="background:{{ $ap->status === 'approved' ? '#dcfce7' : ($ap->status === 'pending' ? '#fef9c3' : '#fee2e2') }};color:{{ $ap->status === 'approved' ? '#166534' : ($ap->status === 'pending' ? '#92400e' : '#991b1b') }};padding:.125rem .5rem;border-radius:.25rem;font-size:.75rem;">{{ ucfirst($ap->status) }}</span>
                <a href="{{ route('admin.approvals.show', $ap) }}" style="font-size:.75rem;color:#6366f1;margin-left:.5rem;">Ver →</a>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Budget Changes --}}
        @if($adCampaign->budgetChanges->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Alterações de Orçamento</h3>
            @foreach($adCampaign->budgetChanges as $bc)
            <div style="border:1px solid #f1f5f9;border-radius:.375rem;padding:.75rem;margin-bottom:.5rem;">
                <div style="font-size:.8125rem;font-weight:500;">R$ {{ number_format($bc->old_budget,2,',','.') }} → R$ {{ number_format($bc->new_budget,2,',','.') }}</div>
                <div style="font-size:.75rem;color:#64748b;margin-top:.25rem;">{{ Str::limit($bc->reason, 50) }}</div>
                <span style="background:{{ $bc->status === 'applied' ? '#dcfce7' : ($bc->status === 'pending_approval' ? '#fef9c3' : '#fee2e2') }};color:{{ $bc->status === 'applied' ? '#166534' : ($bc->status === 'pending_approval' ? '#92400e' : '#991b1b') }};padding:.125rem .5rem;border-radius:.25rem;font-size:.75rem;">{{ $bc->status_label }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Agendar --}}
        @if(in_array($adCampaign->status, ['approved']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Agendar Campanha</h3>
            <form method="POST" action="{{ route('admin.ads.campaigns.schedule', $adCampaign) }}">
                @csrf
                <div style="margin-bottom:.75rem;">
                    <label style="font-size:.8125rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Início</label>
                    <input type="date" name="start_date" style="width:100%;padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.8125rem;">
                </div>
                <div style="margin-bottom:.75rem;">
                    <label style="font-size:.8125rem;font-weight:500;color:#374151;display:block;margin-bottom:.25rem;">Fim</label>
                    <input type="date" name="end_date" style="width:100%;padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.8125rem;">
                </div>
                <button type="submit" style="width:100%;background:#0ea5e9;color:#fff;padding:.5rem;border-radius:.375rem;font-size:.8125rem;font-weight:500;border:none;cursor:pointer;">Agendar (Sandbox)</button>
            </form>
        </div>
        @endif
    </div>
</div>

</x-layouts.app>
