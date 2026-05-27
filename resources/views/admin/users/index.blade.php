<x-layouts.app title="Usuários">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#991b1b;font-size:.875rem;">✗ {{ session('error') }}</div>
@endif

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Usuários</h1>
        <p style="font-size:.85rem;color:#64748b;">Gerencie usuários, roles e permissões da plataforma.</p>
    </div>
    @can('create', App\Models\User::class)
    <a href="{{ route('admin.users.create') }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:9px 20px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Novo usuário
    </a>
    @endcan
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:28px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;margin-bottom:6px;">Total</div>
        <div style="font-size:1.8rem;font-weight:700;color:#0f172a;">{{ $stats['total'] }}</div>
    </div>
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:18px;">
        <div style="font-size:.7rem;color:#166534;text-transform:uppercase;letter-spacing:.06em;font-weight:600;margin-bottom:6px;">Ativos</div>
        <div style="font-size:1.8rem;font-weight:700;color:#16a34a;">{{ $stats['active'] }}</div>
    </div>
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:12px;padding:18px;">
        <div style="font-size:.7rem;color:#991b1b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;margin-bottom:6px;">Inativos/Bloq.</div>
        <div style="font-size:1.8rem;font-weight:700;color:#dc2626;">{{ $stats['inactive'] }}</div>
    </div>
    <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:12px;padding:18px;">
        <div style="font-size:.7rem;color:#1e40af;text-transform:uppercase;letter-spacing:.06em;font-weight:600;margin-bottom:6px;">Internos</div>
        <div style="font-size:1.8rem;font-weight:700;color:#2563eb;">{{ $stats['internal'] }}</div>
    </div>
    <div style="background:#f0fdf4;border:1px solid #6ee7b7;border-radius:12px;padding:18px;">
        <div style="font-size:.7rem;color:#065f46;text-transform:uppercase;letter-spacing:.06em;font-weight:600;margin-bottom:6px;">Clientes</div>
        <div style="font-size:1.8rem;font-weight:700;color:#059669;">{{ $stats['clients'] }}</div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar nome ou e-mail..." style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;min-width:200px;">
        <select name="role" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os perfis</option>
            @foreach(['admin_geral'=>'Admin Geral','agencia_admin'=>'Admin Agência','agencia_operador'=>'Operador','social_media'=>'Social Media','copywriter'=>'Copywriter','blog_writer'=>'Blog Writer','seo'=>'SEO','designer'=>'Designer','gestor_trafego'=>'Gestor de Tráfego','cliente_admin'=>'Admin Cliente','cliente_colaborador'=>'Colaborador','viewer'=>'Visualizador','ai_employee'=>'Funcionário IA'] as $val=>$label)
            <option value="{{ $val }}" {{ request('role')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="user_type" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os tipos</option>
            <option value="internal" {{ request('user_type')==='internal'?'selected':'' }}>Interno</option>
            <option value="client"   {{ request('user_type')==='client'?'selected':'' }}>Cliente</option>
            <option value="ai_employee" {{ request('user_type')==='ai_employee'?'selected':'' }}>IA</option>
        </select>
        <select name="status" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os status</option>
            <option value="active"   {{ request('status')==='active'?'selected':'' }}>Ativo</option>
            <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inativo</option>
            <option value="blocked"  {{ request('status')==='blocked'?'selected':'' }}>Bloqueado</option>
        </select>
        <select name="client_id" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os clientes</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">Filtrar</button>
        @if(request()->hasAny(['search','role','user_type','status','client_id']))
        <a href="{{ route('admin.users.index') }}" style="color:#4f46e5;font-size:.8rem;text-decoration:none;">Limpar</a>
        @endif
    </form>
</div>

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Usuário</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Perfil</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Tipo</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Vínculo</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Último acesso</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:12px 16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#4a6cf7,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr($user->name,0,1)) }}
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $user->name }}</div>
                            <div style="font-size:.75rem;color:#64748b;">{{ $user->email }}</div>
                            @if($user->job_title)
                            <div style="font-size:.7rem;color:#94a3b8;">{{ $user->job_title }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="padding:12px 16px;">
                    @php
                        $rb = match($user->role) {
                            'admin_geral'                                        => ['bg'=>'#f5f3ff','color'=>'#7c3aed','border'=>'#c4b5fd'],
                            'agencia_admin','agencia_operador'                   => ['bg'=>'#eff6ff','color'=>'#2563eb','border'=>'#93c5fd'],
                            'social_media','copywriter','blog_writer','seo','designer','gestor_trafego' => ['bg'=>'#fff7ed','color'=>'#c2410c','border'=>'#fed7aa'],
                            'cliente_admin','cliente_colaborador'                => ['bg'=>'#f0fdf4','color'=>'#16a34a','border'=>'#86efac'],
                            'ai_employee'                                        => ['bg'=>'#fdf4ff','color'=>'#9333ea','border'=>'#e9d5ff'],
                            default                                              => ['bg'=>'#f8fafc','color'=>'#64748b','border'=>'#e2e8f0'],
                        };
                    @endphp
                    <span style="background:{{ $rb['bg'] }};color:{{ $rb['color'] }};border:1px solid {{ $rb['border'] }};font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:5px;">{{ $user->role_label }}</span>
                </td>
                <td style="padding:12px 16px;">
                    @php
                        $tb = match($user->user_type) {
                            'internal' => ['bg'=>'#eff6ff','color'=>'#1d4ed8','label'=>'Interno'],
                            'agency'   => ['bg'=>'#eff6ff','color'=>'#1d4ed8','label'=>'Agência'],
                            'client'   => ['bg'=>'#ecfdf5','color'=>'#065f46','label'=>'Cliente'],
                            default    => ['bg'=>'#fdf4ff','color'=>'#9333ea','label'=>'IA'],
                        };
                    @endphp
                    <span style="background:{{ $tb['bg'] }};color:{{ $tb['color'] }};font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:5px;border:1px solid {{ $tb['bg'] }};">{{ $tb['label'] }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:#475569;">
                    {{ $user->client?->name ?? $user->company?->name ?? '—' }}
                </td>
                <td style="padding:12px 16px;">
                    @php
                        $sb = match($user->status) {
                            'active'   => ['bg'=>'#f0fdf4','color'=>'#16a34a','border'=>'#86efac','label'=>'Ativo'],
                            'inactive' => ['bg'=>'#fefce8','color'=>'#a16207','border'=>'#fde047','label'=>'Inativo'],
                            'blocked'  => ['bg'=>'#fef2f2','color'=>'#dc2626','border'=>'#fca5a5','label'=>'Bloqueado'],
                            default    => ['bg'=>'#f8fafc','color'=>'#64748b','border'=>'#e2e8f0','label'=>$user->status],
                        };
                    @endphp
                    <span style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};border:1px solid {{ $sb['border'] }};font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:5px;">{{ $sb['label'] }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.75rem;color:#94a3b8;">
                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                </td>
                <td style="padding:12px 16px;text-align:right;">
                    <div style="display:flex;justify-content:flex-end;gap:6px;flex-wrap:wrap;">
                        @can('view', $user)
                        <a href="{{ route('admin.users.show', $user) }}" title="Visualizar" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:5px 10px;border-radius:6px;font-size:.75rem;text-decoration:none;">Ver</a>
                        @endcan
                        @can('update', $user)
                        <a href="{{ route('admin.users.edit', $user) }}" title="Editar" style="background:#eff6ff;color:#2563eb;border:1px solid #93c5fd;padding:5px 10px;border-radius:6px;font-size:.75rem;text-decoration:none;">Editar</a>
                        @endcan
                        @can('managePermissions', $user)
                        <a href="{{ route('admin.users.permissions.edit', $user) }}" title="Permissões" style="background:#f5f3ff;color:#7c3aed;border:1px solid #c4b5fd;padding:5px 10px;border-radius:6px;font-size:.75rem;text-decoration:none;">Permissões</a>
                        @endcan
                        @can('resetPassword', $user)
                        <a href="{{ route('admin.users.reset-password.edit', $user) }}" title="Resetar senha" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;padding:5px 10px;border-radius:6px;font-size:.75rem;text-decoration:none;">Senha</a>
                        @endcan
                        @if($user->status !== 'active')
                        @can('activate', $user)
                        <form method="POST" action="{{ route('admin.users.activate', $user) }}" style="display:inline;">@csrf
                            <button type="submit" style="background:#f0fdf4;color:#16a34a;border:1px solid #86efac;padding:5px 10px;border-radius:6px;font-size:.75rem;cursor:pointer;">Ativar</button>
                        </form>
                        @endcan
                        @else
                        @can('deactivate', $user)
                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" style="display:inline;">@csrf
                            <button type="submit" onclick="return confirm('Inativar {{ $user->name }}?')" style="background:#fefce8;color:#a16207;border:1px solid #fde047;padding:5px 10px;border-radius:6px;font-size:.75rem;cursor:pointer;">Inativar</button>
                        </form>
                        @endcan
                        @endif
                        @if($user->status !== 'blocked')
                        @can('block', $user)
                        <form method="POST" action="{{ route('admin.users.block', $user) }}" style="display:inline;">@csrf
                            <button type="submit" onclick="return confirm('Bloquear {{ $user->name }}?')" style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:5px 10px;border-radius:6px;font-size:.75rem;cursor:pointer;">Bloquear</button>
                        </form>
                        @endcan
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="padding:48px;text-align:center;color:#94a3b8;">
                    <svg width="40" height="40" style="margin:0 auto 12px;display:block;opacity:.4;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <div style="font-size:.875rem;font-weight:500;">Nenhum usuário encontrado</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid #f1f5f9;font-size:.8rem;color:#64748b;">
        <span>{{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuários</span>
        {{ $users->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
