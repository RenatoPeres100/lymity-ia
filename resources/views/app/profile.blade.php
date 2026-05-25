@extends('layouts.mobile-app')
@section('title', 'Perfil — Lymity App')
@section('header-title', 'Perfil')

@section('content')

{{-- Avatar --}}
<div style="text-align:center;padding:24px 0 16px;">
    <div style="width:72px;height:72px;border-radius:50%;background:#1e293b;border:2px solid #38bdf8;display:inline-flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#38bdf8;">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div style="font-size:18px;font-weight:700;color:#f1f5f9;margin-top:12px;">{{ $user->name }}</div>
    <div style="font-size:13px;color:#64748b;">{{ $user->email }}</div>
</div>

{{-- User info --}}
<div class="app-card">
    <div class="app-card-title">Dados da conta</div>
    <div class="detail-row"><span class="detail-key">Função</span><span class="detail-val">{{ ucwords(str_replace('_', ' ', $user->role ?? '—')) }}</span></div>
    <div class="detail-row"><span class="detail-key">Tipo</span><span class="detail-val">{{ ucfirst($user->user_type ?? '—') }}</span></div>
    @if($user->client)
    <div class="detail-row"><span class="detail-key">Cliente</span><span class="detail-val">{{ $user->client->name }}</span></div>
    @endif
    <div class="detail-row">
        <span class="detail-key">Status</span>
        <span class="detail-val">
            <span class="badge {{ $user->isActive() ? 'badge-approved' : 'badge-rejected' }}">
                {{ $user->isActive() ? 'Ativo' : 'Inativo' }}
            </span>
        </span>
    </div>
</div>

{{-- Links --}}
<div class="app-card">
    <div class="app-card-title">Navegação</div>
    <a href="{{ route('client.dashboard') }}" class="approval-item" style="padding:14px 0;">
        <div class="approval-dot" style="background:#38bdf8;"></div>
        <div class="approval-info">
            <div class="approval-name">Painel do Cliente</div>
            <div class="approval-meta">Visão completa da plataforma</div>
        </div>
        <span style="color:#475569;">→</span>
    </a>
</div>

{{-- Logout --}}
<form method="POST" action="{{ route('logout') }}" style="margin-top:8px;">
    @csrf
    <button type="submit" class="btn-app btn-reject btn-full">Sair da conta</button>
</form>

@endsection
