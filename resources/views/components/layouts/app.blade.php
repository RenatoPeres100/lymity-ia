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

    <div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebar()"></div>

    <aside id="sidebar" class="sidebar">

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

        <nav class="sidebar-nav">

            {{-- ===================== AGÊNCIA ===================== --}}
            @if(auth()->user()->isAgencyUser() || auth()->user()->isAdminGeral())

            <span class="nav-section-label">Principal</span>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.operation') }}" class="nav-item {{ request()->routeIs('admin.operation') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Operação
            </a>

            @php $pendingApprovals = \App\Models\ApprovalRequest::where('status','pending')->count(); @endphp
            <a href="{{ route('admin.approvals.index') }}" class="nav-item {{ request()->routeIs('admin.approvals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Aprovações
                @if($pendingApprovals > 0)
                <span class="ml-auto text-xs bg-amber-900 text-amber-300 px-2 py-0.5 rounded-full">{{ $pendingApprovals }}</span>
                @endif
            </a>

            {{-- CONTEÚDO --}}
            <span class="nav-section-label">Conteúdo</span>

            @if(config('features.blog_pipeline'))
            <a href="{{ route('admin.blog.pipeline.index') }}" class="nav-item {{ request()->routeIs('admin.blog.pipeline*','admin.blog.posts*','admin.blog-posts*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Blog da Agência
            </a>
            @endif

            @if(config('features.instagram_connection'))
            <a href="{{ route('admin.social.instagram.index') }}" class="nav-item {{ request()->routeIs('admin.social.instagram*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="2" width="20" height="20" rx="5"/>
                    <circle cx="12" cy="12" r="4"/>
                    <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                </svg>
                Conexão Instagram
            </a>
            @endif

            @if(config('features.instagram_pipeline'))
            <a href="{{ route('admin.social.posts.index') }}" class="nav-item {{ request()->routeIs('admin.social.posts*','admin.social.ai*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="2" width="20" height="20" rx="5"/>
                    <circle cx="12" cy="12" r="4"/>
                    <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                </svg>
                Posts Instagram
            </a>

            <a href="{{ route('admin.publishing-queue') }}" class="nav-item {{ request()->routeIs('admin.publishing-queue*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                Fila de Publicação
            </a>

            <a href="{{ route('admin.social.calendar.index') }}" class="nav-item {{ request()->routeIs('admin.social.calendar*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Calendário
            </a>
            @endif

            {{-- AGENTES IA --}}
            @if(config('features.agent_routines'))
            <span class="nav-section-label">Agentes IA</span>

            @if(config('features.agency_brand_context'))
            <a href="{{ route('admin.agency.brand-context.index') }}" class="nav-item {{ request()->routeIs('admin.agency.brand-context*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                Brand Context
            </a>
            @endif

            <a href="{{ route('admin.agents.routines.index') }}" class="nav-item {{ request()->routeIs('admin.agents.routines*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Rotinas dos Agentes
            </a>
            @endif

            {{-- FUNCIONÁRIOS IA --}}
            <span class="nav-section-label">Funcionários IA</span>

            <a href="{{ route('admin.ai-employees.index') }}" class="nav-item {{ request()->routeIs('admin.ai-employees*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/>
                </svg>
                Funcionários IA
            </a>

            <a href="{{ route('admin.ai-tasks.index') }}" class="nav-item {{ request()->routeIs('admin.ai-tasks*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 12l2 2 4-4"/>
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

            <a href="{{ route('admin.ai-settings.index') }}" class="nav-item {{ request()->routeIs('admin.ai-settings*','admin.ai-usage*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
                    <path d="M12 2v2M12 20v2M2 12h2M20 12h2"/>
                </svg>
                Config. IA
            </a>

            {{-- SISTEMA --}}
            <span class="nav-section-label">Sistema</span>

            <a href="{{ route('admin.activity-logs.index') }}" class="nav-item {{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6M8 13h8M8 17h5"/>
                </svg>
                Logs Operacionais
            </a>

            <a href="{{ route('admin.system-health') }}" class="nav-item {{ request()->routeIs('admin.system-health') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                System Health
            </a>

            <a href="{{ route('admin.clients') }}" class="nav-item {{ request()->routeIs('admin.clients') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Clientes
            </a>

            @can('viewAny', App\Models\User::class)
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Usuários
            </a>
            @endcan

            {{-- MÓDULOS DESATIVADOS — visíveis só se flag ativa --}}
            @if(config('features.ads_module'))
            <span class="nav-section-label">Ads</span>
            <a href="{{ route('admin.ads.index') }}" class="nav-item {{ request()->routeIs('admin.ads.index') ? 'active' : '' }}">Ads</a>
            <a href="{{ route('admin.ads.campaigns.index') }}" class="nav-item {{ request()->routeIs('admin.ads.campaigns*') ? 'active' : '' }}">Campanhas</a>
            @endif

            @if(config('features.proposals_module') || config('features.budgets_module') || config('features.contracts_module'))
            <span class="nav-section-label">Comercial</span>
            @if(config('features.proposals_module'))
            <a href="{{ route('admin.proposals.index') }}" class="nav-item {{ request()->routeIs('admin.proposals*') ? 'active' : '' }}">Propostas</a>
            @endif
            @if(config('features.budgets_module'))
            <a href="{{ route('admin.budgets.index') }}" class="nav-item {{ request()->routeIs('admin.budgets*') ? 'active' : '' }}">Orçamentos</a>
            @endif
            @if(config('features.contracts_module'))
            <a href="{{ route('admin.contracts.index') }}" class="nav-item {{ request()->routeIs('admin.contracts*') ? 'active' : '' }}">Contratos</a>
            @endif
            @endif

            @if(config('features.reports_fake_module'))
            <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">Relatórios</a>
            @endif

            @if(config('features.cases_demo_module'))
            <a href="{{ route('admin.cases.index') }}" class="nav-item {{ request()->routeIs('admin.cases*') ? 'active' : '' }}">Cases</a>
            @endif

            @if(config('features.crm_module'))
            <a href="{{ route('admin.leads.index') }}" class="nav-item {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">Leads/CRM</a>
            @endif

            @endif {{-- end agency/admin --}}

            {{-- ===================== CLIENTE ===================== --}}
            @if(auth()->user()->isClientUser())

            <span class="nav-section-label">Minha Conta</span>

            <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Meu Painel
            </a>

            @php $clientPending = auth()->user()->client_id ? \App\Models\ApprovalRequest::where('client_id', auth()->user()->client_id)->where('status','pending')->count() : 0; @endphp
            <a href="{{ route('client.approvals.index') }}" class="nav-item {{ request()->routeIs('client.approvals*') && !request()->routeIs('client.social*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Aprovações
                @if($clientPending > 0)
                <span class="ml-auto text-xs bg-amber-900 text-amber-300 px-2 py-0.5 rounded-full">{{ $clientPending }}</span>
                @endif
            </a>

            @if(config('features.instagram_pipeline'))
            <span class="nav-section-label">Instagram</span>
            <a href="{{ route('client.social.posts.index') }}" class="nav-item {{ request()->routeIs('client.social.posts*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/>
                    <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                </svg>
                Meus Posts
            </a>
            <a href="{{ route('client.social.approvals.index') }}" class="nav-item {{ request()->routeIs('client.social.approvals*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Aprovar Posts
            </a>
            @endif

            @if(config('features.blog_pipeline'))
            <span class="nav-section-label">Blog</span>
            <a href="{{ route('client.blog.index') }}" class="nav-item {{ request()->routeIs('client.blog*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Blog
            </a>
            @endif

            <a href="{{ route('client.files.index') }}" class="nav-item {{ request()->routeIs('client.files*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
                Arquivos
            </a>

            <a href="{{ route('app.dashboard') }}" class="nav-item {{ request()->routeIs('app.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="5" y="2" width="14" height="20" rx="2"/>
                    <line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/><line x1="9" y1="15" x2="12" y2="15"/>
                </svg>
                App Mobile
            </a>

            {{-- Módulos cliente desativados por feature flag --}}
            @if(config('features.ads_module'))
            <span class="nav-section-label">Ads</span>
            <a href="{{ route('client.ads.campaigns.index') }}" class="nav-item">Campanhas</a>
            @endif

            @if(config('features.proposals_module'))
            <a href="{{ route('client.proposals.index') }}" class="nav-item">Propostas</a>
            @endif

            @if(config('features.budgets_module'))
            <a href="{{ route('client.budgets.index') }}" class="nav-item">Orçamentos</a>
            @endif

            @if(config('features.contracts_module'))
            <a href="{{ route('client.contracts.index') }}" class="nav-item">Contratos</a>
            @endif

            @endif {{-- end client --}}

        </nav>

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

    <div class="flex flex-col flex-1 ml-60 main-content">

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
                @if(config('ai.provider') === 'mock')
                <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-amber-600 bg-amber-50 border border-amber-200 rounded-full px-3 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>
                    IA: Modo Mock
                </span>
                @else
                <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-full px-3 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                    IA: {{ ucfirst(config('ai.provider')) }}
                </span>
                @endif

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
