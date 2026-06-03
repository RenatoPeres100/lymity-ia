@extends('components.layouts.app')
@section('title', 'Prospecção — Visão Geral')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Prospecção</h1>
        <p class="page-subtitle">Visão geral do pipeline comercial</p>
    </div>
    <div class="flex gap-2">
        @can('create', \App\Models\ProspectLead::class)
        <a href="{{ route('admin.prospecting.leads.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Novo Lead
        </a>
        @endcan
        <a href="{{ route('admin.prospecting.pipeline') }}" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="4" height="18" rx="1"/><rect x="10" y="6" width="4" height="15" rx="1"/><rect x="17" y="9" width="4" height="12" rx="1"/></svg>
            Ver Pipeline
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-label">Leads Abertos</div>
        <div class="stat-value">{{ $stats['open'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Leads Quentes</div>
        <div class="stat-value text-red-500">{{ $stats['hot'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Reuniões Agendadas</div>
        <div class="stat-value text-orange-500">{{ $stats['meeting_scheduled'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Propostas Enviadas</div>
        <div class="stat-value text-purple-500">{{ $stats['proposal_sent'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Fechados</div>
        <div class="stat-value text-green-500">{{ $stats['won'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Perdidos</div>
        <div class="stat-value text-slate-400">{{ $stats['lost'] }}</div>
    </div>
    <div class="stat-card border-red-200">
        <div class="stat-label text-red-600">Follow-ups Atrasados</div>
        <div class="stat-value text-red-600">{{ $stats['overdue_followups'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Próximos 7 dias</div>
        <div class="stat-value text-blue-500">{{ $stats['upcoming_followups'] }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Próximas atividades --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Próximas Atividades</h3>
        </div>
        <div class="card-body p-0">
            @forelse($upcomingActivities as $activity)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 last:border-0">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-semibold text-slate-500">
                    {{ strtoupper(substr($activity->type_label, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-800 truncate">{{ $activity->title }}</div>
                    <div class="text-xs text-slate-500">{{ $activity->lead?->name }} — {{ $activity->due_at?->format('d/m H:i') }}</div>
                </div>
                @if($activity->due_at?->isPast())
                <span class="badge badge-red text-xs">Atrasada</span>
                @endif
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-slate-400">Nenhuma atividade pendente</div>
            @endforelse
        </div>
    </div>

    {{-- Leads com maior fit --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Leads com Maior Fit Score</h3>
        </div>
        <div class="card-body p-0">
            @forelse($topLeads as $lead)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 last:border-0">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0 text-xs font-bold text-indigo-600">
                    {{ $lead->fit_score }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-800 truncate">{{ $lead->name }}</div>
                    <div class="text-xs text-slate-500">{{ $lead->company_name ?: '—' }} · {{ $lead->stage?->name }}</div>
                </div>
                <a href="{{ route('admin.prospecting.leads.show', $lead) }}" class="text-xs text-indigo-600 hover:underline">Ver</a>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-slate-400">Nenhum lead com fit score</div>
            @endforelse
        </div>
    </div>

    {{-- Últimas mensagens IA --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mensagens IA Recentes</h3>
        </div>
        <div class="card-body p-0">
            @forelse($recentMessages as $msg)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 last:border-0">
                <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-800 truncate">{{ $msg->lead?->name }}</div>
                    <div class="text-xs text-slate-500">{{ $msg->channel_label }} · {{ $msg->objective_label }}</div>
                </div>
                <span class="badge badge-yellow text-xs">Rascunho</span>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-slate-400">Nenhuma mensagem IA gerada</div>
            @endforelse
        </div>
    </div>

    {{-- Leads recentes --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Leads Atualizados Recentemente</h3>
            <a href="{{ route('admin.prospecting.leads.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todos</a>
        </div>
        <div class="card-body p-0">
            @forelse($recentLeads as $lead)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 last:border-0">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-bold text-slate-600">
                    {{ strtoupper(substr($lead->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-800 truncate">{{ $lead->name }}</div>
                    <div class="text-xs text-slate-500">{{ $lead->company_name ?: '—' }} · {{ $lead->stage?->name }}</div>
                </div>
                <a href="{{ route('admin.prospecting.leads.show', $lead) }}" class="text-xs text-indigo-600 hover:underline">Ver</a>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-slate-400">Nenhum lead cadastrado</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
