@extends('components.layouts.app')
@section('title', 'Pipeline de Prospecção')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Pipeline Kanban</h1>
        <p class="page-subtitle">Visualização das etapas de prospecção</p>
    </div>
    <div class="flex gap-2">
        @can('create', \App\Models\ProspectLead::class)
        <a href="{{ route('admin.prospecting.leads.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Novo Lead
        </a>
        @endcan
        <a href="{{ route('admin.prospecting.leads.index') }}" class="btn btn-secondary">Ver Lista</a>
    </div>
</div>

@if(empty($kanban))
<div class="card">
    <div class="card-body text-center py-12">
        <p class="text-slate-500 mb-4">Nenhum pipeline configurado.</p>
        <p class="text-sm text-slate-400">Execute: <code class="bg-slate-100 px-2 py-0.5 rounded">php artisan prospecting:install-default-pipeline</code></p>
    </div>
</div>
@else
<div class="overflow-x-auto pb-4">
    <div class="flex gap-4" style="min-width: max-content;">
        @foreach($kanban as $col)
        @php $stage = $col['stage']; $leads = $col['leads']; @endphp
        <div class="w-64 shrink-0">
            <div class="flex items-center gap-2 mb-3 px-1">
                <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color ?? '#64748b' }}"></div>
                <span class="text-sm font-semibold text-slate-700">{{ $stage->name }}</span>
                <span class="ml-auto text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full">{{ $col['count'] }}</span>
            </div>
            <div class="space-y-2">
                @forelse($leads as $lead)
                <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm hover:shadow-md transition-shadow">
                    <div class="font-semibold text-sm text-slate-800 truncate">{{ $lead->name }}</div>
                    @if($lead->company_name)
                    <div class="text-xs text-slate-500 mt-0.5 truncate">{{ $lead->company_name }}</div>
                    @endif
                    @if($lead->segment)
                    <div class="text-xs text-slate-400 truncate">{{ $lead->segment }}</div>
                    @endif

                    <div class="flex items-center gap-2 mt-2">
                        @if($lead->fit_score !== null)
                        <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">{{ $lead->fit_score }}%</span>
                        @endif
                        @if($lead->interest_level)
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $lead->interest_level === 'hot' ? 'bg-red-50 text-red-600' : ($lead->interest_level === 'warm' ? 'bg-yellow-50 text-yellow-600' : 'bg-blue-50 text-blue-600') }}">
                            {{ $lead->interest_label }}
                        </span>
                        @endif
                    </div>

                    @if($lead->next_follow_up_at)
                    <div class="text-xs mt-2 {{ $lead->isOverdue() ? 'text-red-500' : 'text-slate-400' }}">
                        <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        {{ $lead->next_follow_up_at->format('d/m') }}
                        @if($lead->isOverdue()) (atrasado) @endif
                    </div>
                    @endif

                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-100">
                        @if($lead->owner)
                        <span class="text-xs text-slate-400 truncate">{{ $lead->owner->name }}</span>
                        @else
                        <span></span>
                        @endif
                        <a href="{{ route('admin.prospecting.leads.show', $lead) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium shrink-0">Ver →</a>
                    </div>

                    @can('moveStage', $lead)
                    <form action="{{ route('admin.prospecting.leads.move-stage', $lead) }}" method="POST" class="mt-2">
                        @csrf @method('PATCH')
                        <select name="prospect_stage_id" onchange="this.form.submit()"
                                class="w-full text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 text-slate-600 focus:outline-none">
                            <option value="">Mover para...</option>
                            @foreach($col['stage']->pipeline->stages as $s)
                            <option value="{{ $s->id }}" {{ $s->id === $lead->prospect_stage_id ? 'disabled' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endcan
                </div>
                @empty
                <div class="bg-slate-50 rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-400">
                    Sem leads nesta etapa
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
