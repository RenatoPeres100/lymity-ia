@extends('layouts.mobile-app')
@section('title', $approvalRequest->title . ' — Lymity App')
@section('header-title', 'Detalhe')

@section('content')

{{-- Back button --}}
<a href="{{ route('app.approvals.index') }}" class="btn-app btn-outline" style="margin-bottom:16px;display:inline-flex;">
    ← Voltar
</a>

{{-- Title & badges --}}
<div class="app-card">
    <div style="margin-bottom:12px;">
        <div style="font-size:18px;font-weight:800;color:#f1f5f9;margin-bottom:8px;">{{ $approvalRequest->title }}</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span class="badge badge-{{ $approvalRequest->status }}">
                {{ match($approvalRequest->status) {
                    'pending'           => 'Pendente',
                    'approved'          => 'Aprovado',
                    'rejected'          => 'Reprovado',
                    'changes_requested' => 'Ajuste',
                    default             => ucfirst($approvalRequest->status),
                } }}
            </span>
            <span class="badge badge-{{ $approvalRequest->sensitive_level }}">{{ ucfirst($approvalRequest->sensitive_level ?? 'low') }}</span>
            <span class="badge badge-draft">{{ ucfirst(str_replace('_', ' ', $approvalRequest->approval_type)) }}</span>
        </div>
    </div>

    @if($approvalRequest->description)
    <div style="font-size:14px;color:#94a3b8;line-height:1.6;border-top:1px solid #334155;padding-top:12px;">
        {{ $approvalRequest->description }}
    </div>
    @endif

    <div class="detail-row"><span class="detail-key">Solicitado por</span><span class="detail-val">{{ $approvalRequest->requester?->name ?? '—' }}</span></div>
    <div class="detail-row"><span class="detail-key">Criado em</span><span class="detail-val">{{ $approvalRequest->created_at->format('d/m/Y H:i') }}</span></div>
    @if($approvalRequest->due_at)
    <div class="detail-row"><span class="detail-key">Prazo</span><span class="detail-val" style="color:#f87171;">{{ $approvalRequest->due_at->format('d/m/Y') }}</span></div>
    @endif
</div>

{{-- Payload --}}
@if($approvalRequest->payload)
<div class="app-card">
    <div class="app-card-title">Dados do item</div>
    @foreach((array)$approvalRequest->payload as $key => $val)
    <div class="detail-row">
        <span class="detail-key">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
        <span class="detail-val">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Action buttons (only if pending) --}}
@if($approvalRequest->status === 'pending')
<div class="app-card">
    <div class="app-card-title">Ação</div>

    {{-- Approve --}}
    <form method="POST" action="{{ route('app.approvals.approve', $approvalRequest) }}" style="margin-bottom:12px;">
        @csrf
        <div class="app-field">
            <label class="app-label">Observação (opcional)</label>
            <textarea name="notes" class="app-input" rows="2" placeholder="Adicione uma observação..."></textarea>
        </div>
        <button type="submit" class="btn-app btn-approve btn-full">✓ Aprovar</button>
    </form>

    {{-- Reject --}}
    <form method="POST" action="{{ route('app.approvals.reject', $approvalRequest) }}" style="margin-bottom:12px;">
        @csrf
        <div class="app-field">
            <label class="app-label">Motivo da reprovação *</label>
            <textarea name="reason" class="app-input" rows="2" placeholder="Informe o motivo..." required></textarea>
        </div>
        <button type="submit" class="btn-app btn-reject btn-full">✕ Reprovar</button>
    </form>

    {{-- Request changes --}}
    <form method="POST" action="{{ route('app.approvals.request-changes', $approvalRequest->id) }}">
        @csrf
        <div class="app-field">
            <label class="app-label">Descreva os ajustes necessários *</label>
            <textarea name="notes" class="app-input" rows="2" placeholder="O que precisa ser ajustado?" required></textarea>
        </div>
        <button type="submit" class="btn-app btn-changes btn-full">↺ Pedir Ajuste</button>
    </form>
</div>
@endif

{{-- History --}}
@if($approvalRequest->actions->count())
<div class="app-card">
    <div class="app-card-title">Histórico</div>
    @foreach($approvalRequest->actions as $action)
    <div class="approval-item" style="display:block;padding:10px 0;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
            <span style="font-size:13px;font-weight:600;color:#f1f5f9;">{{ $action->user?->name ?? 'Sistema' }}</span>
            <span style="font-size:11px;color:#475569;">{{ $action->created_at->format('d/m H:i') }}</span>
        </div>
        <span class="badge badge-{{ $action->action }}">{{ ucfirst(str_replace('_', ' ', $action->action)) }}</span>
        @if($action->notes)
        <div style="font-size:12px;color:#94a3b8;margin-top:6px;">{{ $action->notes }}</div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- Comments --}}
<div class="app-card">
    <div class="app-card-title">Comentários ({{ $approvalRequest->comments->count() }})</div>

    @foreach($approvalRequest->comments as $comment)
    <div style="padding:10px 0;border-bottom:1px solid #1e293b;">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="font-size:13px;font-weight:600;color:#f1f5f9;">{{ $comment->user?->name ?? '—' }}</span>
            <span style="font-size:11px;color:#475569;">{{ $comment->created_at->format('d/m H:i') }}</span>
        </div>
        <div style="font-size:13px;color:#94a3b8;">{{ $comment->comment }}</div>
    </div>
    @endforeach

    {{-- Add comment --}}
    <form method="POST" action="{{ route('app.approvals.comment', $approvalRequest) }}" style="margin-top:12px;">
        @csrf
        <div class="app-field">
            <label class="app-label">Adicionar comentário</label>
            <textarea name="comment" class="app-input" rows="2" placeholder="Escreva aqui..." required></textarea>
        </div>
        <button type="submit" class="btn-app btn-outline btn-full">Enviar comentário</button>
    </form>
</div>

@endsection
