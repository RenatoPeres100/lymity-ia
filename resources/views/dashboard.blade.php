<x-layouts.app title="Dashboard">

<div class="mb-6 flex items-start justify-between flex-wrap gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Olá, {{ explode(' ', auth()->user()->name)[0] }} 👋</h2>
        <p class="text-sm text-slate-500 mt-1">Aqui está o resumo da sua plataforma.</p>
    </div>
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-300 bg-blue-950 border border-blue-800 rounded-full px-3 py-1.5">
        ✦ Fase 2 — Site Institucional &amp; Blog
    </span>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            </svg>
        </div>
        <div class="stat-label">Clientes</div>
        <div class="stat-value">{{ $stats['total_clients'] }}</div>
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
        </div>
        <div class="stat-label">Usuários</div>
        <div class="stat-value">{{ $stats['total_users'] }}</div>
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4l3 3"/>
            </svg>
        </div>
        <div class="stat-label">Func. IA</div>
        <div class="stat-value">{{ $stats['ai_employees'] }}</div>
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4"/>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
        </div>
        <div class="stat-label">Aprovações</div>
        <div class="stat-value">{{ $stats['pending_approvals'] }}</div>
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-cyan-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-cyan-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="stat-label">Posts plan.</div>
        <div class="stat-value">{{ $stats['planned_posts'] }}</div>
    </div>

    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="stat-label">Campanhas</div>
        <div class="stat-value">{{ $stats['campaigns_analysis'] }}</div>
    </div>

</div>

{{-- Phase 2 Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>
        <div class="stat-label">Blog Posts</div>
        <div class="stat-value">{{ $stats['blog_posts'] ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="stat-label">Cases</div>
        <div class="stat-value">{{ $stats['cases'] ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            </svg>
        </div>
        <div class="stat-label">Leads</div>
        <div class="stat-value">{{ $stats['leads'] ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.07 4.93A10 10 0 0 1 21.5 12a10 10 0 0 1-2.43 7.07M4.93 4.93A10 10 0 0 0 2.5 12a10 10 0 0 0 2.43 7.07"/>
            </svg>
        </div>
        <div class="stat-label">Leads novos</div>
        <div class="stat-value">{{ $stats['new_leads'] ?? 0 }}</div>
    </div>
</div>

{{-- Bottom grid --}}
<div class="grid lg:grid-cols-3 gap-6">

    {{-- Recent clients --}}
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <span class="card-title">Clientes recentes</span>
            <a href="{{ route('admin.clients') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium">Ver todos →</a>
        </div>
        @if($recent_clients->isEmpty())
        <div class="py-12 text-center text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            </svg>
            <div class="text-sm font-medium text-slate-500">Nenhum cliente cadastrado</div>
            <div class="text-xs mt-1">Os clientes aparecerão aqui.</div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Segmento</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_clients as $client)
                    <tr>
                        <td>
                            <div class="font-medium text-slate-800">{{ $client->name }}</div>
                            @if($client->website)
                            <div class="text-xs text-slate-400">{{ $client->website }}</div>
                            @endif
                        </td>
                        <td>
                            @if($client->segment)
                            <span class="badge badge-blue">{{ $client->segment }}</span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $client->status === 'active' ? 'badge-green' : 'badge-gray' }}">
                                {{ $client->status === 'active' ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="text-slate-400 text-xs">{{ $client->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Próximas ações --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Próximas ações</span>
        </div>
        <div class="card-body space-y-3">
            @foreach([
                ['Configurar API do Claude', 'Necessário para ativar funcionários IA', 'Pendente', 'amber'],
                ['Criar primeiro funcionário IA', 'Fase 2 desbloqueará esse recurso', 'Fase 2', 'blue'],
                ['Configurar campanhas', 'Integrar Google Ads e Meta Ads', 'Fase 3', 'blue'],
                ['Criar blog da agência', 'SEO e conteúdo institucional', 'Fase 3', 'blue'],
            ] as [$title, $desc, $status, $color])
            <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 border border-slate-100">
                <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $color === 'amber' ? 'bg-amber-400' : 'bg-blue-400' }}"></div>
                <div>
                    <div class="text-sm font-medium text-slate-700">{{ $title }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $desc }}</div>
                </div>
                <span class="ml-auto text-xs badge {{ $color === 'amber' ? 'badge-orange' : 'badge-blue' }} shrink-0">
                    {{ $status }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>

</x-layouts.app>
