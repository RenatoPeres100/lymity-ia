@extends('layouts.mobile-app')
@section('title', 'Início — Lymity App')
@section('header-title', 'Início')

@section('content')

{{-- Stats Grid --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-number" style="color:#fde047">{{ $summary['pending_approvals'] }}</div>
        <div class="stat-label">Aprovações Pendentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color:#f87171">{{ $summary['urgent_approvals'] }}</div>
        <div class="stat-label">Urgentes</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color:#38bdf8">{{ $summary['social_posts_count'] }}</div>
        <div class="stat-label">Posts Sociais</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color:#a78bfa">{{ $summary['campaigns_count'] }}</div>
        <div class="stat-label">Campanhas</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color:#4ade80">{{ $summary['proposals_count'] }}</div>
        <div class="stat-label">Propostas</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color:#fb923c">{{ $summary['budgets_count'] }}</div>
        <div class="stat-label">Orçamentos</div>
    </div>
</div>

{{-- Pending Approvals --}}
@if($pendingApprovals->count())
<div class="app-card">
    <div class="section-header">
        <span class="section-title">Aprovações Pendentes</span>
        <a href="{{ route('app.approvals.index') }}" class="section-link">Ver todas →</a>
    </div>
    @foreach($pendingApprovals as $approval)
    <a href="{{ route('app.approvals.show', $approval) }}" class="approval-item">
        <div class="approval-dot" style="background:{{ $approval->sensitive_level === 'critical' ? '#f87171' : ($approval->sensitive_level === 'high' ? '#fb923c' : '#fde047') }}"></div>
        <div class="approval-info">
            <div class="approval-name">{{ $approval->title }}</div>
            <div class="approval-meta">{{ ucfirst($approval->approval_type) }} · {{ $approval->created_at->diffForHumans() }}</div>
        </div>
        <span class="badge badge-{{ $approval->sensitive_level }}">{{ ucfirst($approval->sensitive_level) }}</span>
    </a>
    @endforeach
</div>
@endif

{{-- Recent Social Posts --}}
@if($recentPosts->count())
<div class="app-card">
    <div class="section-header">
        <span class="section-title">Posts Recentes</span>
    </div>
    @foreach($recentPosts as $post)
    <div class="approval-item">
        <div class="approval-dot" style="background:#38bdf8"></div>
        <div class="approval-info">
            <div class="approval-name">{{ $post->title }}</div>
            <div class="approval-meta">{{ ucfirst($post->content_type) }} · {{ $post->created_at->diffForHumans() }}</div>
        </div>
        <span class="badge badge-{{ $post->status }}">{{ ucfirst($post->status) }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Proposals --}}
@if($proposals->count())
<div class="app-card">
    <div class="section-header">
        <span class="section-title">Propostas</span>
    </div>
    @foreach($proposals as $proposal)
    <div class="approval-item">
        <div class="approval-dot" style="background:#4ade80"></div>
        <div class="approval-info">
            <div class="approval-name">{{ $proposal->title }}</div>
            <div class="approval-meta">R$ {{ number_format($proposal->total_amount, 2, ',', '.') }}</div>
        </div>
        <span class="badge badge-{{ $proposal->status }}">{{ ucfirst($proposal->status) }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Notifications --}}
@if($notifications->count())
<div class="app-card">
    <div class="section-header">
        <span class="section-title">Notificações</span>
    </div>
    @foreach($notifications as $notif)
    <div class="approval-item">
        <div class="approval-dot" style="background:{{ $notif->status === 'unread' ? '#38bdf8' : '#334155' }}"></div>
        <div class="approval-info">
            <div class="approval-name">{{ $notif->title }}</div>
            <div class="approval-meta">{{ Str::limit($notif->message, 60) }}</div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(!$pendingApprovals->count() && !$recentPosts->count())
<div class="empty-state">
    <div class="empty-icon">✦</div>
    <div class="empty-text">Tudo em dia. Nenhum item pendente.</div>
</div>
@endif

@endsection
