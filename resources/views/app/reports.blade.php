@extends('layouts.mobile-app')
@section('title', 'Relatórios — Lymity App')
@section('header-title', 'Relatórios')

@section('content')

{{-- Approvals --}}
<div class="app-card">
    <div class="app-card-title">Aprovações</div>
    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-number" style="color:#fde047">{{ $reports['approvals']['pending'] }}</div>
            <div class="stat-label">Pendentes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#4ade80">{{ $reports['approvals']['approved'] }}</div>
            <div class="stat-label">Aprovadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#f87171">{{ $reports['approvals']['rejected'] }}</div>
            <div class="stat-label">Reprovadas</div>
        </div>
    </div>
</div>

{{-- Campaigns --}}
<div class="app-card">
    <div class="app-card-title">Campanhas</div>
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-number" style="color:#a78bfa">{{ $reports['campaigns']['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#4ade80">{{ $reports['campaigns']['active'] }}</div>
            <div class="stat-label">Ativas</div>
        </div>
    </div>
</div>

{{-- Social --}}
<div class="app-card">
    <div class="app-card-title">Social Media</div>
    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-number" style="color:#38bdf8">{{ $reports['social']['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#4ade80">{{ $reports['social']['published'] }}</div>
            <div class="stat-label">Publicados</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#94a3b8">{{ $reports['social']['pending'] }}</div>
            <div class="stat-label">Rascunho</div>
        </div>
    </div>
</div>

{{-- Proposals --}}
<div class="app-card">
    <div class="app-card-title">Propostas</div>
    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-number" style="color:#4ade80">{{ $reports['proposals']['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#22d3ee">{{ $reports['proposals']['sent'] }}</div>
            <div class="stat-label">Enviadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#4ade80">{{ $reports['proposals']['accepted'] }}</div>
            <div class="stat-label">Aceitas</div>
        </div>
    </div>
</div>

{{-- Budgets --}}
<div class="app-card">
    <div class="app-card-title">Orçamentos</div>
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-number" style="color:#fb923c">{{ $reports['budgets']['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#4ade80">{{ $reports['budgets']['active'] }}</div>
            <div class="stat-label">Ativos</div>
        </div>
    </div>
</div>

@endsection
