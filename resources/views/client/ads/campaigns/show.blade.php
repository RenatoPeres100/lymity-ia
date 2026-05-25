<x-layouts.app title="{{ $adCampaign->name }}">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('client.ads.campaigns.index') }}" style="font-size:.875rem;color:#6366f1;">← Minhas Campanhas</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $adCampaign->name }}</h2>
    <div style="display:flex;gap:.5rem;margin-top:.5rem;">
        <span style="background:#dbeafe;color:#1e40af;padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $adCampaign->platform_label }}</span>
        <span style="background:#f3e8ff;color:#6b21a8;padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $adCampaign->objective_label }}</span>
        <span style="background:{{ match($adCampaign->status_color) { 'success'=>'#dcfce7', 'warning'=>'#fef9c3', 'danger'=>'#fee2e2', default=>'#f1f5f9' } }};color:{{ match($adCampaign->status_color) { 'success'=>'#166534', 'warning'=>'#92400e', 'danger'=>'#991b1b', default=>'#475569' } }};padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $adCampaign->status_label }}</span>
    </div>
</div>

<div style="background:#fef9c3;border:1px solid #fde047;color:#92400e;padding:.5rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.8125rem;">
    📊 Campanha em modo planejamento/sandbox. Nenhuma ação real foi executada.
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div>
        {{-- Estratégia --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Estratégia</h3>
            <p style="font-size:.875rem;color:#374151;line-height:1.6;">{{ $adCampaign->strategy_summary ?: 'Nenhuma estratégia definida.' }}</p>
        </div>

        {{-- Métricas --}}
        @if($summary['days_tracked'] > 0)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Performance Simulada ({{ $summary['days_tracked'] }} dias)</h3>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;">
                @foreach([
                    ['Impressões', number_format($summary['total_impressions'],0,',','.')],
                    ['Cliques', number_format($summary['total_clicks'],0,',','.')],
                    ['Leads', number_format($summary['total_leads'],0,',','.')],
                    ['Custo', 'R$ '.number_format($summary['total_cost'],2,',','.')],
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

        {{-- Criativos --}}
        @if($adCampaign->creatives->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Criativos ({{ $adCampaign->creatives->count() }})</h3>
            @foreach($adCampaign->creatives as $cr)
            <div style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.75rem;margin-bottom:.5rem;">
                <div style="font-weight:600;font-size:.875rem;color:#1e293b;">{{ $cr->title }}</div>
                @if($cr->headline)<div style="font-size:.8125rem;color:#6366f1;margin-top:.25rem;">{{ $cr->headline }}</div>@endif
                @if($cr->description)<div style="font-size:.8125rem;color:#64748b;margin-top:.25rem;">{{ $cr->description }}</div>@endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Keywords --}}
        @if($adCampaign->keywords->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">Palavras-chave ({{ $adCampaign->keywords->count() }})</h3>
            <div style="display:flex;flex-wrap:wrap;gap:.375rem;">
                @foreach($adCampaign->keywords as $kw)
                <span style="background:{{ $kw->match_type === 'negative' ? '#fee2e2' : '#dbeafe' }};color:{{ $kw->match_type === 'negative' ? '#991b1b' : '#1e40af' }};padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;">
                    {{ $kw->match_type === 'negative' ? '-' : '' }}{{ $kw->keyword }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Detalhes</h3>
            <dl style="font-size:.875rem;">
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Orçamento Diário</dt>
                <dd style="margin:0 0 .75rem;color:#374151;font-weight:600;">R$ {{ number_format($adCampaign->daily_budget,2,',','.') }}</dd>
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Período</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->start_date?->format('d/m/Y') ?? '—' }} → {{ $adCampaign->end_date?->format('d/m/Y') ?? '—' }}</dd>
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Plataforma</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->platform_label }}</dd>
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Grupos</dt>
                <dd style="margin:0 0 .75rem;color:#374151;">{{ $adCampaign->groups->count() }}</dd>
                <dt style="font-weight:500;color:#64748b;margin-bottom:.125rem;">Públicos</dt>
                <dd style="margin:0;color:#374151;">{{ $adCampaign->audiences->count() }}</dd>
            </dl>
        </div>
    </div>
</div>

</x-layouts.app>
