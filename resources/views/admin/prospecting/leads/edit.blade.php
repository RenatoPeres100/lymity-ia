@extends('components.layouts.app')
@section('title', 'Editar Lead')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Editar Lead</h1>
        <p class="page-subtitle">{{ $lead->name }}</p>
    </div>
    <a href="{{ route('admin.prospecting.leads.show', $lead) }}" class="btn btn-secondary">Cancelar</a>
</div>

<form action="{{ route('admin.prospecting.leads.update', $lead) }}" method="POST">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dados do Lead</h3>
        </div>
        <div class="card-body">
            @include('admin.prospecting.partials.lead-form', ['owners' => $owners ?? []])

            {{-- Lost reason --}}
            @if($lead->status === 'lost')
            <div class="mt-4">
                <label class="form-label">Motivo da perda</label>
                <input type="text" name="lost_reason" value="{{ old('lost_reason', $lead->lost_reason) }}"
                    class="input-field w-full">
            </div>
            @endif
        </div>
        <div class="card-footer flex justify-end gap-3">
            <a href="{{ route('admin.prospecting.leads.show', $lead) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
        </div>
    </div>
</form>
@endsection
