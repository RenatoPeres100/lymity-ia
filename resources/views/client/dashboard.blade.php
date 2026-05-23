<x-layouts.app title="Painel do Cliente">

@if(auth()->user()->isAdminGeral())
<div class="alert alert-info mb-6 text-sm">
    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
    <span>Você está visualizando a <strong>área do cliente</strong> como Admin Geral.</span>
</div>
@endif

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">
        {{ $client ? 'Olá, ' . explode(' ', auth()->user()->name)[0] . '!' : 'Painel do Cliente' }}
    </h2>
    @if($client)
    <p class="text-sm text-slate-500 mt-1">
        Conta: <strong class="text-slate-700">{{ $client->name }}</strong>
        @if($client->segment) · <span class="text-slate-400">{{ $client->segment }}</span> @endif
    </p>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
        </div>
        <div class="stat-label">Aprovações pendentes</div>
        <div class="stat-value">{{ $stats['pending_approvals'] }}</div>
        @if($stats['pending_approvals'] > 0)
        <a href="{{ route('client.approvals.index') }}?status=pending" style="font-size:.7rem;color:#6b8fff;text-decoration:none;display:block;margin-top:4px;">ver →</a>
        @endif
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
            </svg>
        </div>
        <div class="stat-label">Alterações solicitadas</div>
        <div class="stat-value">{{ $stats['changes_requested'] }}</div>
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div class="stat-label">Histórico aprovado</div>
        <div class="stat-value">{{ $stats['approved_history'] }}</div>
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>
        <div class="stat-label">Conteúdos em criação</div>
        <div class="stat-value">{{ $stats['contents_in_creation'] }}</div>
    </div>

</div>

@if($pending_approvals->isNotEmpty())
<div style="background:#0f172a;border:1px solid #78350f;border-radius:14px;padding:24px;margin-bottom:28px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3 style="font-size:.9rem;font-weight:700;color:#fbbf24;">Conteúdos aguardando decisão</h3>
        <a href="{{ route('client.approvals.index') }}" style="font-size:.75rem;color:#6b8fff;text-decoration:none;">ver todos →</a>
    </div>
    @foreach($pending_approvals as $pa)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #1e293b;">
        <div>
            <span style="font-size:.875rem;font-weight:600;color:#e2e8f0;">{{ $pa->title }}</span>
            <div style="font-size:.72rem;color:#475569;">{{ $pa->approval_type_label }}</div>
        </div>
        <a href="{{ route('client.approvals.show', $pa->id) }}" style="background:#1e293b;color:#6b8fff;font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:6px;text-decoration:none;">Revisar</a>
    </div>
    @endforeach
</div>
@endif

{{-- Content area --}}
<div class="grid lg:grid-cols-2 gap-6">

    {{-- Client profile --}}
    @if($client)
    <div class="card">
        <div class="card-header">
            <span class="card-title">Perfil da conta</span>
        </div>
        <div class="card-body space-y-4">
            @foreach([
                ['Segmento', $client->segment],
                ['Website', $client->website],
                ['Instagram', $client->instagram],
                ['Voz da marca', $client->brand_voice ? mb_substr($client->brand_voice, 0, 80) . '...' : null],
            ] as [$label, $value])
            @if($value)
            <div class="flex items-start gap-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wide w-28 shrink-0 mt-0.5">{{ $label }}</span>
                <span class="text-sm text-slate-700">{{ $value }}</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Upcoming features --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">O que vem por aí</span>
        </div>
        <div class="card-body space-y-3">
            @foreach([
                ['📝', 'Criação de conteúdo', 'Blog, posts, anúncios gerados por IA', 'Fase 3'],
                ['📊', 'Relatórios de performance', 'Métricas de redes sociais e campanhas', 'Fase 3'],
                ['✅', 'Aprovações de conteúdo', 'Aprove ou rejeite antes de publicar', 'Fase 2'],
                ['🎯', 'Campanhas inteligentes', 'Google Ads e Meta com IA', 'Fase 4'],
            ] as [$icon, $title, $desc, $phase])
            <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 border border-slate-100">
                <span class="text-lg">{{ $icon }}</span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-700">{{ $title }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $desc }}</div>
                </div>
                <span class="badge badge-blue shrink-0 text-xs">{{ $phase }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

</x-layouts.app>
