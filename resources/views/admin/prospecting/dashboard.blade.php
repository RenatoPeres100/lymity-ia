<x-layouts.app title="Prospecção — Visão Geral">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
@endif

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#0f172a;margin:0 0 .25rem;">Prospecção</h1>
        <p style="color:#64748b;font-size:.9rem;margin:0;">Visão geral do pipeline comercial</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @can('create', \App\Models\ProspectLead::class)
        <a href="{{ route('admin.prospecting.leads.create') }}"
           style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;display:flex;align-items:center;gap:.4rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Novo Lead
        </a>
        @endcan
        <a href="{{ route('admin.prospecting.pipeline') }}"
           style="background:#f1f5f9;color:#374151;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;border:1px solid #e2e8f0;">
            Ver Pipeline
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;margin-bottom:1.5rem;">
    @php
    $cards = [
        ['label'=>'Leads Abertos',       'value'=>$stats['open'],               'color'=>'#6366f1'],
        ['label'=>'Leads Quentes 🔥',    'value'=>$stats['hot'],                'color'=>'#ef4444'],
        ['label'=>'Reuniões Agendadas',  'value'=>$stats['meeting_scheduled'],  'color'=>'#f97316'],
        ['label'=>'Propostas Enviadas',  'value'=>$stats['proposal_sent'],      'color'=>'#8b5cf6'],
        ['label'=>'Fechados ✓',          'value'=>$stats['won'],                'color'=>'#22c55e'],
        ['label'=>'Perdidos',            'value'=>$stats['lost'],               'color'=>'#94a3b8'],
        ['label'=>'Follow-ups Atrasados','value'=>$stats['overdue_followups'],  'color'=>'#dc2626'],
        ['label'=>'Próximos 7 dias',     'value'=>$stats['upcoming_followups'], 'color'=>'#0ea5e9'],
    ];
    @endphp
    @foreach($cards as $card)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <p style="color:#64748b;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .4rem;">{{ $card['label'] }}</p>
        <p style="font-size:2rem;font-weight:700;color:{{ $card['color'] }};margin:0;">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

    {{-- Próximas atividades --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Próximas Atividades</h3>
        </div>
        @forelse($upcomingActivities as $activity)
        <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid #f8fafc;">
            <div style="width:2rem;height:2rem;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#64748b;flex-shrink:0;">
                {{ strtoupper(substr($activity->type_label, 0, 2)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.875rem;font-weight:500;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $activity->title }}</div>
                <div style="font-size:.75rem;color:#94a3b8;">{{ $activity->lead?->name }} · {{ $activity->due_at?->format('d/m H:i') }}</div>
            </div>
            @if($activity->due_at?->isPast())
            <span style="font-size:.7rem;background:#fef2f2;color:#dc2626;padding:.2rem .5rem;border-radius:999px;font-weight:600;">Atrasada</span>
            @endif
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">Nenhuma atividade pendente</div>
        @endforelse
    </div>

    {{-- Leads com maior fit --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Leads com Maior Fit Score</h3>
        </div>
        @forelse($topLeads as $lead)
        <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid #f8fafc;">
            <div style="width:2rem;height:2rem;background:#eef2ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#6366f1;flex-shrink:0;">
                {{ $lead->fit_score }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.875rem;font-weight:500;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->name }}</div>
                <div style="font-size:.75rem;color:#94a3b8;">{{ $lead->company_name ?: '—' }} · {{ $lead->stage?->name }}</div>
            </div>
            <a href="{{ route('admin.prospecting.leads.show', $lead) }}" style="font-size:.75rem;color:#6366f1;text-decoration:none;">Ver →</a>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">Nenhum lead com fit score</div>
        @endforelse
    </div>

    {{-- Últimas mensagens IA --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Mensagens IA Recentes</h3>
        </div>
        @forelse($recentMessages as $msg)
        <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid #f8fafc;">
            <div style="width:2rem;height:2rem;background:#f5f3ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.875rem;font-weight:500;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $msg->lead?->name }}</div>
                <div style="font-size:.75rem;color:#94a3b8;">{{ $msg->channel_label }} · {{ $msg->objective_label }}</div>
            </div>
            <span style="font-size:.7rem;background:#fefce8;color:#a16207;padding:.2rem .5rem;border-radius:999px;font-weight:600;">Rascunho</span>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">Nenhuma mensagem gerada</div>
        @endforelse
    </div>

    {{-- Leads recentes --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Leads Atualizados Recentemente</h3>
            <a href="{{ route('admin.prospecting.leads.index') }}" style="font-size:.75rem;color:#6366f1;text-decoration:none;">Ver todos</a>
        </div>
        @forelse($recentLeads as $lead)
        <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid #f8fafc;">
            <div style="width:2rem;height:2rem;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#475569;flex-shrink:0;">
                {{ strtoupper(substr($lead->name, 0, 2)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.875rem;font-weight:500;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->name }}</div>
                <div style="font-size:.75rem;color:#94a3b8;">{{ $lead->company_name ?: '—' }} · {{ $lead->stage?->name }}</div>
            </div>
            <a href="{{ route('admin.prospecting.leads.show', $lead) }}" style="font-size:.75rem;color:#6366f1;text-decoration:none;">Ver →</a>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">Nenhum lead cadastrado</div>
        @endforelse
    </div>
</div>

</x-layouts.app>
