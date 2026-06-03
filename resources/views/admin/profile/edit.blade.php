<x-layouts.app title="Meu Perfil">
<div class="space-y-6 max-w-2xl">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Meu Perfil</h1>
        <p class="text-sm text-slate-500 mt-1">Edite suas informações pessoais e senha de acesso.</p>
    </div>

    {{-- Success / Error --}}
    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;color:#166534;font-size:14px;">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;color:#991b1b;font-size:14px;">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Avatar + role info --}}
    <div class="card">
        <div class="card-body" style="display:flex;align-items:center;gap:20px;">
            <div style="width:64px;height:64px;border-radius:50%;background:#1e293b;border:2px solid #6366f1;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;color:#a5b4fc;flex-shrink:0;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:18px;font-weight:700;color:#0f172a;">{{ $user->name }}</div>
                <div style="font-size:13px;color:#64748b;margin-top:2px;">{{ $user->email }}</div>
                <div style="margin-top:6px;">
                    <span style="background:#f1f5f9;color:#475569;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.04em;">
                        {{ $user->role_label ?? ucwords(str_replace('_',' ',$user->role)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Dados pessoais --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-800 text-sm">Dados Pessoais</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nome completo</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cargo / Função</label>
                    <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                        placeholder="Ex: Gerente de Marketing"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary text-sm">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Alterar senha --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-800 text-sm">Alterar Senha</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Senha atual</label>
                    <input type="password" name="current_password"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400"
                        required autocomplete="current-password">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nova senha</label>
                    <input type="password" name="password"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400"
                        required autocomplete="new-password">
                    <p class="text-xs text-slate-400 mt-1">Mínimo 8 caracteres.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400"
                        required autocomplete="new-password">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary text-sm">Alterar senha</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Info somente leitura --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-slate-800 text-sm">Informações da conta</h3>
        </div>
        <div class="card-body space-y-2">
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                <span class="text-sm text-slate-500">Perfil / Papel</span>
                <span class="text-sm font-medium text-slate-700">{{ $user->role_label ?? ucwords(str_replace('_',' ',$user->role)) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                <span class="text-sm text-slate-500">Tipo</span>
                <span class="text-sm font-medium text-slate-700">{{ ucfirst($user->user_type ?? '—') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                <span class="text-sm text-slate-500">Status</span>
                <span class="badge badge-{{ $user->status === 'active' ? 'approved' : 'rejected' }}">{{ $user->status === 'active' ? 'Ativo' : 'Inativo' }}</span>
            </div>
            @if($user->last_login_at)
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span class="text-sm text-slate-500">Último login</span>
                <span class="text-sm text-slate-700">{{ $user->last_login_at->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>

</div>
</x-layouts.app>
