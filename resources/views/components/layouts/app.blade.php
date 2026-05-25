<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — Lymity AI Agency</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">

<div class="flex min-h-screen">

    {{-- Sidebar Overlay (mobile) --}}
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar" class="sidebar">

        {{-- Logo --}}
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div>
                <div class="sidebar-logo-text">Lymity AI</div>
                <div class="sidebar-logo-sub">Agency Platform</div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="sidebar-nav">

            <span class="nav-section-label">Principal</span>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            @if(auth()->user()->isAgencyUser() || auth()->user()->isAdminGeral())
            <span class="nav-section-label">Agência</span>

            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Usuários
            </a>

            <a href="{{ route('admin.approvals.index') }}" class="nav-item {{ request()->routeIs('admin.approvals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Aprovações
                @php $pendingCount = \App\Models\ApprovalRequest::where('status','pending')->count(); @endphp
                @if($pendingCount > 0)
                <span class="ml-auto text-xs bg-amber-900 text-amber-300 px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.clients') }}" class="nav-item {{ request()->routeIs('admin.clients') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Clientes
            </a>

            <a href="{{ route('admin.ai-employees.index') }}" class="nav-item {{ request()->routeIs('admin.ai-employees*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2z"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
                Funcionários IA
            </a>

            <a href="{{ route('admin.ai-tasks.index') }}" class="nav-item {{ request()->routeIs('admin.ai-tasks*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
                Tarefas IA
            </a>

            <a href="{{ route('admin.ai-logs.index') }}" class="nav-item {{ request()->routeIs('admin.ai-logs*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6M8 13h8M8 17h5"/>
                </svg>
                Logs IA
            </a>

            <a href="{{ route('admin.ai-schedules.index') }}" class="nav-item {{ request()->routeIs('admin.ai-schedules*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Agendamentos IA
            </a>

            <a href="{{ route('admin.ai-memories.index') }}" class="nav-item {{ request()->routeIs('admin.ai-memories*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <ellipse cx="12" cy="5" rx="9" ry="3"/>
                    <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
                    <path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/>
                </svg>
                Memórias IA
            </a>

            <a href="{{ route('admin.leads.index') }}" class="nav-item {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Leads
            </a>

            <span class="nav-section-label">Social Media</span>

            <a href="{{ route('admin.social.index') }}" class="nav-item {{ request()->routeIs('admin.social.index') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
                </svg>
                Social Media
            </a>

            <a href="{{ route('admin.social.posts.index') }}" class="nav-item {{ request()->routeIs('admin.social.posts*') || request()->routeIs('admin.social.ai*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M9 21V9"/>
                </svg>
                Posts Sociais
            </a>

            <a href="{{ route('admin.social.calendar.index') }}" class="nav-item {{ request()->routeIs('admin.social.calendar*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Calendário
            </a>

            <a href="{{ route('admin.social.channels.index') }}" class="nav-item {{ request()->routeIs('admin.social.channels*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
                </svg>
                Canais
            </a>

            <span class="nav-section-label">SEO</span>

            <a href="{{ route('admin.seo.index') }}" class="nav-item {{ request()->routeIs('admin.seo.index') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                SEO
            </a>

            <a href="{{ route('admin.seo.blog.index') }}" class="nav-item {{ request()->routeIs('admin.seo.blog*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Blog SEO
            </a>

            <span class="nav-section-label">Ads</span>

            <a href="{{ route('admin.ads.index') }}" class="nav-item {{ request()->routeIs('admin.ads.index') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 3h18v4H3z"/><path d="M3 10h10v11H3z"/><path d="M15 10h6v11h-6z"/>
                </svg>
                Visão Geral
            </a>

            <a href="{{ route('admin.ads.accounts.index') }}" class="nav-item {{ request()->routeIs('admin.ads.accounts*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                Contas
            </a>

            <a href="{{ route('admin.ads.campaigns.index') }}" class="nav-item {{ request()->routeIs('admin.ads.campaigns*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                Campanhas
            </a>

            <a href="{{ route('admin.ads.metrics.index') }}" class="nav-item {{ request()->routeIs('admin.ads.metrics*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Métricas
            </a>

            <a href="{{ route('admin.ads.budget-approvals.index') }}" class="nav-item {{ request()->routeIs('admin.ads.budget-approvals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
                </svg>
                Aprov. Orçamento
            </a>

            <span class="nav-section-label">Comercial</span>

            <a href="{{ route('admin.proposals.index') }}" class="nav-item {{ request()->routeIs('admin.proposals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                </svg>
                Propostas
            </a>

            <a href="{{ route('admin.budgets.index') }}" class="nav-item {{ request()->routeIs('admin.budgets*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                </svg>
                Orçamentos
            </a>

            <a href="{{ route('admin.contracts.index') }}" class="nav-item {{ request()->routeIs('admin.contracts*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="9" y1="17" x2="8" y2="17"/>
                </svg>
                Contratos
            </a>

            <a href="{{ route('app.dashboard') }}" class="nav-item {{ request()->routeIs('app.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="5" y="2" width="14" height="20" rx="2"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/><line x1="9" y1="15" x2="12" y2="15"/>
                </svg>
                App Mobile Preview
            </a>

            <span class="nav-section-label">Conteúdo</span>

            <a href="{{ route('admin.blog-posts.index') }}" class="nav-item {{ request()->routeIs('admin.blog-posts*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Blog Posts
            </a>

            <a href="{{ route('admin.blog-categories.index') }}" class="nav-item {{ request()->routeIs('admin.blog-categories*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
                Categorias
            </a>

            <a href="{{ route('admin.cases.index') }}" class="nav-item {{ request()->routeIs('admin.cases*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Cases
            </a>
            @endif

            @if(auth()->user()->isAdminGeral())
            <span class="nav-section-label">Sistema</span>

            <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.07 4.93A10 10 0 0 1 21.5 12a10 10 0 0 1-2.43 7.07M4.93 4.93A10 10 0 0 0 2.5 12a10 10 0 0 0 2.43 7.07"/>
                </svg>
                Configurações
            </a>

            <a href="#" class="nav-item opacity-50 cursor-not-allowed" title="Disponível na Fase 2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Logs de Auditoria
                <span class="ml-auto text-xs bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Em breve</span>
            </a>
            @endif

            @if(auth()->user()->isClientUser())
            <span class="nav-section-label">Minha Conta</span>
            <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Meu Painel
            </a>
            <a href="{{ route('client.brand') }}" class="nav-item {{ request()->routeIs('client.brand') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688z"/></svg>
                Minha Marca
            </a>
            <a href="{{ route('client.pages.index') }}" class="nav-item {{ request()->routeIs('client.pages*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Páginas
            </a>
            <a href="{{ route('client.blog.index') }}" class="nav-item {{ request()->routeIs('client.blog*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Blog
            </a>
            <a href="{{ route('client.approvals.index') }}" class="nav-item {{ request()->routeIs('client.approvals*') && !request()->routeIs('client.social*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Aprovações
                @php $clientPending = auth()->user()->client_id ? \App\Models\ApprovalRequest::where('client_id', auth()->user()->client_id)->where('status','pending')->count() : 0; @endphp
                @if($clientPending > 0)
                <span class="ml-auto text-xs bg-amber-900 text-amber-300 px-2 py-0.5 rounded-full">{{ $clientPending }}</span>
                @endif
            </a>

            <span class="nav-section-label">SEO</span>
            <a href="{{ route('client.seo.index') }}" class="nav-item {{ request()->routeIs('client.seo.index') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                SEO
            </a>
            <a href="{{ route('client.seo.blog.index') }}" class="nav-item {{ request()->routeIs('client.seo.blog*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Blog
            </a>

            <span class="nav-section-label">Social Media</span>
            <a href="{{ route('client.social.posts.index') }}" class="nav-item {{ request()->routeIs('client.social.posts*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                </svg>
                Meus Posts
            </a>
            <a href="{{ route('client.social.calendar.index') }}" class="nav-item {{ request()->routeIs('client.social.calendar*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Calendário
            </a>
            <a href="{{ route('client.social.approvals.index') }}" class="nav-item {{ request()->routeIs('client.social.approvals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Aprovar Posts
            </a>

            <span class="nav-section-label">Ads</span>
            <a href="{{ route('client.ads.campaigns.index') }}" class="nav-item {{ request()->routeIs('client.ads.campaigns*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                Campanhas
            </a>
            <a href="{{ route('client.ads.approvals.index') }}" class="nav-item {{ request()->routeIs('client.ads.approvals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Aprovações
            </a>
            <a href="{{ route('client.ads.reports.index') }}" class="nav-item {{ request()->routeIs('client.ads.reports*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Relatórios
            </a>

            <span class="nav-section-label">Comercial</span>
            <a href="{{ route('client.proposals.index') }}" class="nav-item {{ request()->routeIs('client.proposals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                </svg>
                Propostas
            </a>
            <a href="{{ route('client.budgets.index') }}" class="nav-item {{ request()->routeIs('client.budgets*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                </svg>
                Orçamentos
            </a>
            <a href="{{ route('client.contracts.index') }}" class="nav-item {{ request()->routeIs('client.contracts*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="9" y1="17" x2="8" y2="17"/>
                </svg>
                Contratos
            </a>

            <a href="{{ route('app.dashboard') }}" class="nav-item {{ request()->routeIs('app.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="5" y="2" width="14" height="20" rx="2"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/><line x1="9" y1="15" x2="12" y2="15"/>
                </svg>
                App Mobile
            </a>
            @endif

        </nav>

        {{-- User Footer --}}
        <div class="sidebar-footer">
            <div class="flex items-center gap-2.5 px-2 py-2">
                <div class="sidebar-avatar text-xs">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-200 truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()->role_label }}</div>
                </div>
            </div>
        </div>

    </aside>

    {{-- Main --}}
    <div class="flex flex-col flex-1 ml-60 main-content">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="flex items-center gap-3">
                <button class="hamburger p-1.5 rounded-lg hover:bg-slate-100" onclick="toggleSidebar()">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <h1 class="text-sm font-semibold text-slate-800">{{ $title ?? 'Dashboard' }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 bg-slate-100 border border-slate-200 rounded-full px-3 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                    Fase 8 — Ads
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout text-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                        Sair
                    </button>
                </form>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-7">
            {{ $slot }}
        </main>

    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
</script>

</body>
</html>
