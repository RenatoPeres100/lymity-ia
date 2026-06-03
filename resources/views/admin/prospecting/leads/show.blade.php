@extends('components.layouts.app')
@section('title', $lead->name . ' — Lead')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $lead->name }}</h1>
        <p class="page-subtitle">
            {{ $lead->company_name ?: '' }}
            @if($lead->segment) · {{ $lead->segment }} @endif
        </p>
    </div>
    <div class="flex gap-2 flex-wrap">
        @can('update', $lead)
        <a href="{{ route('admin.prospecting.leads.edit', $lead) }}" class="btn btn-secondary">Editar</a>
        @endcan
        @can('archive', $lead)
        @if($lead->status === 'open')
        <form action="{{ route('admin.prospecting.leads.archive', $lead) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" onclick="return confirm('Arquivar este lead?')" class="btn btn-secondary">Arquivar</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('admin.prospecting.leads.index') }}" class="btn btn-ghost">← Leads</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error mb-4">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Coluna principal --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Dados principais --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do Lead</h3>
                <div class="flex items-center gap-2">
                    <span class="badge {{ $lead->status === 'open' ? 'badge-blue' : ($lead->status === 'won' ? 'badge-green' : 'badge-slate') }}">
                        {{ $lead->status_label }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    @if($lead->email)
                    <div><span class="text-slate-400 block text-xs">E-mail</span>{{ $lead->email }}</div>
                    @endif
                    @if($lead->whatsapp)
                    <div><span class="text-slate-400 block text-xs">WhatsApp</span>{{ $lead->whatsapp }}</div>
                    @endif
                    @if($lead->phone)
                    <div><span class="text-slate-400 block text-xs">Telefone</span>{{ $lead->phone }}</div>
                    @endif
                    @if($lead->website)
                    <div><span class="text-slate-400 block text-xs">Website</span>
                        <a href="{{ $lead->website }}" target="_blank" class="text-indigo-600 hover:underline">{{ $lead->website }}</a>
                    </div>
                    @endif
                    @if($lead->instagram)
                    <div><span class="text-slate-400 block text-xs">Instagram</span>{{ $lead->instagram }}</div>
                    @endif
                    @if($lead->linkedin)
                    <div><span class="text-slate-400 block text-xs">LinkedIn</span>{{ $lead->linkedin }}</div>
                    @endif
                    @if($lead->city || $lead->state)
                    <div><span class="text-slate-400 block text-xs">Cidade/Estado</span>{{ $lead->city }}{{ $lead->city && $lead->state ? '/' : '' }}{{ $lead->state }}</div>
                    @endif
                    @if($lead->source)
                    <div><span class="text-slate-400 block text-xs">Origem</span>{{ $lead->source }}</div>
                    @endif
                    @if($lead->estimated_budget)
                    <div><span class="text-slate-400 block text-xs">Orçamento estimado</span>{{ $lead->estimated_budget }}</div>
                    @endif
                    @if($lead->potential_value)
                    <div><span class="text-slate-400 block text-xs">Valor potencial</span>R$ {{ number_format($lead->potential_value, 2, ',', '.') }}</div>
                    @endif
                </div>

                @if($lead->pain_points)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Dores identificadas</span>
                    <p class="mt-1 text-sm text-slate-700">{{ $lead->pain_points }}</p>
                </div>
                @endif

                @if($lead->opportunity_summary)
                <div class="mt-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Resumo da oportunidade</span>
                    <p class="mt-1 text-sm text-slate-700">{{ $lead->opportunity_summary }}</p>
                </div>
                @endif

                @if($lead->notes)
                <div class="mt-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Observações</span>
                    <p class="mt-1 text-sm text-slate-700">{{ $lead->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Atividades --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Atividades & Follow-ups</h3>
            </div>
            <div class="card-body">
                @can('create', \App\Models\ProspectActivity::class)
                <form action="{{ route('admin.prospecting.activities.store', $lead) }}" method="POST" class="mb-4 p-4 bg-slate-50 rounded-xl">
                    @csrf
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                        <select name="activity_type" class="input-field" required>
                            <option value="">Tipo...</option>
                            <option value="call">Ligação</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">E-mail</option>
                            <option value="instagram">Instagram</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="meeting">Reunião</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="task">Tarefa</option>
                            <option value="other">Outro</option>
                        </select>
                        <input type="text" name="title" placeholder="Título da atividade" class="input-field md:col-span-2" required>
                        <input type="datetime-local" name="due_at" class="input-field">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm">Adicionar Atividade</button>
                    </div>
                </form>
                @endcan

                <div class="space-y-2">
                    @forelse($lead->activities as $activity)
                    <div class="flex items-start gap-3 p-3 rounded-xl {{ $activity->status === 'done' ? 'bg-green-50' : ($activity->status === 'canceled' ? 'bg-slate-50 opacity-60' : 'bg-white border border-slate-200') }}">
                        <div class="w-8 h-8 rounded-full {{ $activity->status === 'done' ? 'bg-green-100' : 'bg-slate-100' }} flex items-center justify-center shrink-0 text-xs font-semibold">
                            {{ strtoupper(substr($activity->type_label, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-800">{{ $activity->title }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                {{ $activity->type_label }}
                                @if($activity->due_at) · {{ $activity->due_at->format('d/m/Y H:i') }} @endif
                                @if($activity->user) · {{ $activity->user->name }} @endif
                            </div>
                            @if($activity->outcome)
                            <div class="text-xs text-slate-600 mt-1 italic">{{ $activity->outcome }}</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            @if($activity->status === 'pending')
                            <form action="{{ route('admin.prospecting.activities.complete', $activity) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-ghost text-green-600" title="Concluir">✓</button>
                            </form>
                            <form action="{{ route('admin.prospecting.activities.cancel', $activity) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-ghost text-red-400" title="Cancelar">✕</button>
                            </form>
                            @elseif($activity->status === 'done')
                            <span class="badge badge-green text-xs">Concluída</span>
                            @else
                            <span class="badge badge-slate text-xs">Cancelada</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400 text-center py-4">Nenhuma atividade registrada</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Notas --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notas</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.prospecting.notes.store', $lead) }}" method="POST" class="mb-4">
                    @csrf
                    <textarea name="note" rows="2" placeholder="Adicionar nota..." class="input-field w-full mb-2"></textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm">Adicionar Nota</button>
                    </div>
                </form>

                <div class="space-y-3">
                    @forelse($lead->noteEntries as $note)
                    <div class="p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                        <p class="text-sm text-slate-700">{{ $note->note }}</p>
                        <div class="text-xs text-slate-400 mt-1">
                            {{ $note->user?->name ?? '—' }} · {{ $note->created_at->format('d/m/Y H:i') }}
                            @if($note->visibility === 'internal')
                            <span class="ml-1 text-xs text-slate-400">· Interna</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400 text-center py-4">Nenhuma nota registrada</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Mensagens IA --}}
        @include('admin.prospecting.partials.message-suggestions', ['lead' => $lead])

    </div>

    {{-- Coluna lateral --}}
    <div class="space-y-6">

        {{-- Status & Pipeline --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pipeline & Status</h3>
            </div>
            <div class="card-body space-y-3">
                <div>
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Etapa atual</span>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $lead->stage?->color ?? '#64748b' }}"></span>
                        <span class="text-sm font-semibold text-slate-800">{{ $lead->stage?->name ?? '—' }}</span>
                    </div>
                </div>

                @can('moveStage', $lead)
                <form action="{{ route('admin.prospecting.leads.move-stage', $lead) }}" method="POST">
                    @csrf @method('PATCH')
                    <label class="form-label">Mover para etapa</label>
                    <select name="prospect_stage_id" class="input-field w-full mb-2">
                        <option value="">Selecionar...</option>
                        @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ $stage->id === $lead->prospect_stage_id ? 'disabled' : '' }}>
                            {{ $stage->name }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm w-full">Mover Etapa</button>
                </form>
                @endcan

                <div class="pt-2 border-t border-slate-100 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Interesse</span>
                        @if($lead->interest_level)
                        <span class="badge {{ $lead->interest_level === 'hot' ? 'badge-red' : ($lead->interest_level === 'warm' ? 'badge-yellow' : 'badge-blue') }}">
                            {{ $lead->interest_label }}
                        </span>
                        @else <span class="text-slate-400">—</span> @endif
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Fit Score</span>
                        <span class="font-semibold text-indigo-600">{{ $lead->fit_score !== null ? $lead->fit_score . '/100' : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Responsável</span>
                        <span>{{ $lead->owner?->name ?? '—' }}</span>
                    </div>
                    @if($lead->next_action)
                    <div>
                        <span class="text-slate-400">Próxima ação</span>
                        <p class="mt-0.5 text-slate-700">{{ $lead->next_action }}</p>
                    </div>
                    @endif
                    @if($lead->next_follow_up_at)
                    <div class="flex justify-between">
                        <span class="text-slate-400">Follow-up</span>
                        <span class="{{ $lead->isOverdue() ? 'text-red-500 font-semibold' : 'text-slate-600' }}">
                            {{ $lead->next_follow_up_at->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SDR IA --}}
        @include('admin.prospecting.partials.ai-panel', ['lead' => $lead, 'canGenerateAi' => $canGenerateAi])

    </div>
</div>
@endsection
