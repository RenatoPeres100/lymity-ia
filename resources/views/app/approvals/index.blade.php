@extends('layouts.mobile-app')
@section('title', 'Aprovações — Lymity App')
@section('header-title', 'Aprovações')

@section('content')

@if($approvals->count())
    @foreach($approvals as $approval)
    <a href="{{ route('app.approvals.show', $approval) }}" class="app-card" style="display:block;text-decoration:none;color:inherit;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
            <div style="flex:1;min-width:0;margin-right:10px;">
                <div style="font-size:15px;font-weight:700;color:#f1f5f9;margin-bottom:4px;">{{ $approval->title }}</div>
                <div style="font-size:12px;color:#64748b;">{{ ucfirst(str_replace('_', ' ', $approval->approval_type)) }} · {{ $approval->created_at->diffForHumans() }}</div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
                <span class="badge badge-{{ $approval->status }}">
                    {{ match($approval->status) {
                        'pending'           => 'Pendente',
                        'approved'          => 'Aprovado',
                        'rejected'          => 'Reprovado',
                        'changes_requested' => 'Ajuste',
                        default             => ucfirst($approval->status),
                    } }}
                </span>
                <span class="badge badge-{{ $approval->sensitive_level }}">{{ ucfirst($approval->sensitive_level ?? 'low') }}</span>
            </div>
        </div>
        @if($approval->description)
        <div style="font-size:13px;color:#94a3b8;margin-top:8px;">{{ Str::limit($approval->description, 100) }}</div>
        @endif
        @if($approval->due_at)
        <div style="font-size:11px;color:#f87171;margin-top:8px;">⏰ Prazo: {{ $approval->due_at->format('d/m/Y') }}</div>
        @endif
    </a>
    @endforeach

    {{-- Pagination --}}
    @if($approvals->hasPages())
    <div style="display:flex;gap:8px;justify-content:center;margin-top:8px;">
        @if($approvals->onFirstPage())
            <span class="btn-app btn-outline" style="opacity:0.4;">← Anterior</span>
        @else
            <a href="{{ $approvals->previousPageUrl() }}" class="btn-app btn-outline">← Anterior</a>
        @endif
        @if($approvals->hasMorePages())
            <a href="{{ $approvals->nextPageUrl() }}" class="btn-app btn-outline">Próxima →</a>
        @else
            <span class="btn-app btn-outline" style="opacity:0.4;">Próxima →</span>
        @endif
    </div>
    @endif

@else
    <div class="empty-state">
        <div class="empty-icon">✓</div>
        <div class="empty-text">Nenhuma aprovação encontrada.</div>
    </div>
@endif

@endsection
