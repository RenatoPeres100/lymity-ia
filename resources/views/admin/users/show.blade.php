<x-layouts.app title="Usuário — {{ $user->name }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
    <a href="{{ route('admin.users.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Usuários</a>
    <span style="color:#cbd5e1;">/</span>
    <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;">{{ $user->name }}</h1>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#991b1b;font-size:.875rem;">✗ {{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

    {{-- User card --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#4a6cf7,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;font-weight:700;flex-shrink:0;">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <div>
                    <div style="font-size:1.1rem;font-weight:700;color:#0f172a;">{{ $user->name }}</div>
                    <div style="font-size:.85rem;color:#64748b;">{{ $user->email }}</div>
                    @if($user->job_title)
                    <div style="font-size:.8rem;color:#94a3b8;">{{ $user->job_title }}</div>
                    @endif
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:.85rem;">
                <div>
                    <div style="color:#64748b;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Perfil</div>
                    <div style="color:#0f172a;font-weight:600;">{{ $user->role_label }}</div>
                </div>
                <div>
                    <div style="color:#64748b;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Tipo</div>
                    <div style="color:#0f172a;">{{ match($user->user_type){'internal'=>'Interno','agency'=>'Agência','client'=>'Cliente',default=>'IA'} }}</div>
                </div>
                <div>
                    <div style="color:#64748b;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Status</div>
                    @php
                        $sb = match($user->status){
                            'active'  =>['bg'=>'#f0fdf4','color'=>'#16a34a','label'=>'Ativo'],
                            'inactive'=>['bg'=>'#fefce8','color'=>'#a16207','label'=>'Inativo'],
                            'blocked' =>['bg'=>'#fef2f2','color'=>'#dc2626','label'=>'Bloqueado'],
                            default   =>['bg'=>'#f8fafc','color'=>'#64748b','label'=>$user->status],
                        };
                    @endphp
                    <span style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};font-size:.75rem;font-weight:600;padding:3px 10px;border-radius:5px;">{{ $sb['label'] }}</span>
                </div>
                <div>
                    <div style="color:#64748b;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Vínculo</div>
                    <div style="color:#0f172a;">{{ $user->client?->name ?? $user->company?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="color:#64748b;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Cadastrado em</div>
                    <div style="color:#475569;">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div style="color:#64748b;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Último acesso</div>
                    <div style="color:#475569;">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</div>
                </div>
            </div>
        </div>

        {{-- Permissions summary --}}
        @if($user->permissions->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;">Permissões diretas</h3>
                @can('managePermissions', $user)
                <a href="{{ route('admin.users.permissions.edit', $user) }}" style="font-size:.75rem;color:#4a6cf7;text-decoration:none;">Gerenciar →</a>
                @endcan
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach($user->permissions as $p)
                <span style="background:#eff6ff;color:#2563eb;border:1px solid #93c5fd;font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:5px;">{{ $p->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recent logs --}}
        @if($logs->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;">Logs recentes</h3>
                @can('viewLogs', $user)
                <a href="{{ route('admin.users.logs', $user) }}" style="font-size:.75rem;color:#4a6cf7;text-decoration:none;">Ver todos →</a>
                @endcan
            </div>
            @foreach($logs as $log)
            <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:.8rem;">
                <div style="color:#0f172a;font-weight:500;">{{ $log->description }}</div>
                <div style="color:#94a3b8;font-size:.72rem;margin-top:2px;">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->ip_address ?? '—' }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Actions panel --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:10px;">
        <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:6px;">Ações rápidas</h3>

        @can('update', $user)
        <a href="{{ route('admin.users.edit', $user) }}" style="display:block;background:#eff6ff;color:#2563eb;border:1px solid #93c5fd;padding:10px 14px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;text-align:center;">✏️ Editar dados</a>
        @endcan

        @can('managePermissions', $user)
        <a href="{{ route('admin.users.permissions.edit', $user) }}" style="display:block;background:#f5f3ff;color:#7c3aed;border:1px solid #c4b5fd;padding:10px 14px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;text-align:center;">🔐 Permissões</a>
        @endcan

        @can('resetPassword', $user)
        <a href="{{ route('admin.users.reset-password.edit', $user) }}" style="display:block;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;padding:10px 14px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;text-align:center;">🔑 Resetar senha</a>
        @endcan

        @can('viewLogs', $user)
        <a href="{{ route('admin.users.logs', $user) }}" style="display:block;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;padding:10px 14px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;text-align:center;">📋 Ver todos os logs</a>
        @endcan

        @if($user->status !== 'active')
        @can('activate', $user)
        <form method="POST" action="{{ route('admin.users.activate', $user) }}">@csrf
            <button type="submit" style="width:100%;background:#f0fdf4;color:#16a34a;border:1px solid #86efac;padding:10px 14px;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;">✓ Ativar usuário</button>
        </form>
        @endcan
        @else
        @can('deactivate', $user)
        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">@csrf
            <button type="submit" onclick="return confirm('Inativar este usuário?')" style="width:100%;background:#fefce8;color:#a16207;border:1px solid #fde047;padding:10px 14px;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;">⏸ Inativar</button>
        </form>
        @endcan
        @endif

        @if($user->status !== 'blocked')
        @can('block', $user)
        <form method="POST" action="{{ route('admin.users.block', $user) }}">@csrf
            <button type="submit" onclick="return confirm('Bloquear este usuário definitivamente?')" style="width:100%;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:10px 14px;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;">🚫 Bloquear</button>
        </form>
        @endcan
        @endif
    </div>
</div>

</x-layouts.app>
