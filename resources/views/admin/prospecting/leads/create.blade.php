@extends('components.layouts.app')
@section('title', 'Novo Lead')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Novo Lead</h1>
        <p class="page-subtitle">Cadastrar novo lead no pipeline de prospecção</p>
    </div>
    <a href="{{ route('admin.prospecting.leads.index') }}" class="btn btn-secondary">Cancelar</a>
</div>

<form action="{{ route('admin.prospecting.leads.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dados do Lead</h3>
        </div>
        <div class="card-body">
            @include('admin.prospecting.partials.lead-form', ['lead' => new \App\Models\ProspectLead(), 'owners' => $owners ?? []])
        </div>
        <div class="card-footer flex justify-end gap-3">
            <a href="{{ route('admin.prospecting.leads.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Criar Lead</button>
        </div>
    </div>
</form>
@endsection
