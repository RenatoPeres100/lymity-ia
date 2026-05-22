<x-layouts.app title="Usuários">

<div class="page-header mb-6 flex items-start justify-between flex-wrap gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Usuários</h2>
        <p class="text-sm text-slate-500 mt-1">Gerencie os usuários da plataforma.</p>
    </div>
    <button onclick="alert('Criação de usuários será liberada na Fase 2.')"
        class="btn btn-primary opacity-75 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Novo usuário
        <span class="text-xs bg-blue-800/50 rounded px-1.5 py-0.5 ml-1">Fase 2</span>
    </button>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Role</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Cliente / Empresa</th>
                <th>Último acesso</th>
                <th>Cadastro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-medium text-slate-800">{{ $user->name }}</div>
                            @if($user->job_title)
                            <div class="text-xs text-slate-400">{{ $user->job_title }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-slate-500 text-sm">{{ $user->email }}</td>
                <td>
                    @php
                        $roleBadge = match($user->role) {
                            'admin_geral'         => 'badge-purple',
                            'agencia_admin'       => 'badge-blue',
                            'agencia_operador'    => 'badge-blue',
                            'social_media', 'copywriter', 'designer', 'seo', 'gestor_trafego' => 'badge-orange',
                            'cliente_admin', 'cliente_colaborador' => 'badge-green',
                            'ai_employee'         => 'badge-purple',
                            default               => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $roleBadge }}">{{ $user->role_label }}</span>
                </td>
                <td>
                    <span class="badge {{ $user->user_type === 'ai' ? 'badge-purple' : ($user->user_type === 'client' ? 'badge-green' : 'badge-blue') }}">
                        {{ match($user->user_type) { 'agency' => 'Agência', 'client' => 'Cliente', 'ai' => 'IA', default => $user->user_type ?? '—' } }}
                    </span>
                </td>
                <td>
                    <span class="badge {{ $user->status === 'active' ? 'badge-green' : 'badge-red' }}">
                        {{ $user->status_label }}
                    </span>
                </td>
                <td class="text-sm text-slate-500">
                    {{ $user->client?->name ?? $user->company?->name ?? '—' }}
                </td>
                <td class="text-xs text-slate-400">
                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                </td>
                <td class="text-xs text-slate-400">{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="py-12 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <div class="text-sm font-medium">Nenhum usuário encontrado</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 text-sm text-slate-500">
        <span>{{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuários</span>
        {{ $users->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
