@extends('components.layouts.app')
@section('title', 'Leads')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Leads</h1>
        <p class="page-subtitle">{{ $leads->total() }} lead(s) encontrado(s)</p>
    </div>
    <div class="flex gap-2">
        @can('create', \App\Models\ProspectLead::class)
        <a href="{{ route('admin.prospecting.leads.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Novo Lead
        </a>
        @endcan
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="card mb-4">
    <div class="card-body">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div class="col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar nome, empresa, segmento..."
                    class="input-field w-full">
            </div>
            <select name="stage_id" class="input-field">
                <option value="">Etapa</option>
                @foreach($stages as $stage)
                <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                @endforeach
            </select>
            <select name="status" class="input-field">
                <option value="">Status</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Aberto</option>
                <option value="won" {{ request('status') === 'won' ? 'selected' : '' }}>Fechado</option>
                <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Perdido</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Arquivado</option>
            </select>
            <select name="interest_level" class="input-field">
                <option value="">Interesse</option>
                <option value="hot" {{ request('interest_level') === 'hot' ? 'selected' : '' }}>Quente</option>
                <option value="warm" {{ request('interest_level') === 'warm' ? 'selected' : '' }}>Morno</option>
                <option value="cold" {{ request('interest_level') === 'cold' ? 'selected' : '' }}>Frio</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Filtrar</button>
                <a href="{{ route('admin.prospecting.leads.index') }}" class="btn btn-secondary">Limpar</a>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <label class="flex items-center gap-1.5 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded">
                Follow-ups atrasados
            </label>
        </div>
    </div>
</form>

<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Lead</th>
                    <th>Empresa</th>
                    <th>Segmento</th>
                    <th>Etapa</th>
                    <th>Fit</th>
                    <th>Interesse</th>
                    <th>Follow-up</th>
                    <th>Responsável</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td class="font-medium">{{ $lead->name }}</td>
                    <td class="text-slate-500">{{ $lead->company_name ?: '—' }}</td>
                    <td class="text-slate-500">{{ $lead->segment ?: '—' }}</td>
                    <td>
                        @if($lead->stage)
                        <span class="inline-flex items-center gap-1 text-xs">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $lead->stage->color ?? '#64748b' }}"></span>
                            {{ $lead->stage->name }}
                        </span>
                        @else —
                        @endif
                    </td>
                    <td>
                        @if($lead->fit_score !== null)
                        <span class="font-semibold text-indigo-600">{{ $lead->fit_score }}%</span>
                        @else —
                        @endif
                    </td>
                    <td>
                        @if($lead->interest_level)
                        <span class="badge {{ $lead->interest_level === 'hot' ? 'badge-red' : ($lead->interest_level === 'warm' ? 'badge-yellow' : 'badge-blue') }}">
                            {{ $lead->interest_label }}
                        </span>
                        @else —
                        @endif
                    </td>
                    <td class="{{ $lead->isOverdue() ? 'text-red-500 font-semibold' : 'text-slate-500' }}">
                        {{ $lead->next_follow_up_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-slate-500">{{ $lead->owner?->name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $lead->status === 'open' ? 'badge-blue' : ($lead->status === 'won' ? 'badge-green' : ($lead->status === 'lost' ? 'badge-slate' : 'badge-slate')) }}">
                            {{ $lead->status_label }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.prospecting.leads.show', $lead) }}" class="text-indigo-600 hover:underline text-sm">Ver</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-8 text-slate-400">Nenhum lead encontrado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())
    <div class="p-4 border-t border-slate-100">
        {{ $leads->links() }}
    </div>
    @endif
</div>
@endsection
