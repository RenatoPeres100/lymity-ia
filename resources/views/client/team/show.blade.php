<x-layouts.app title="{{ $user->name }}">

<div style="margin-bottom:24px;">
    <a href="{{ route('client.team.index') }}" style="color:#64748b;font-size:.875rem;text-decoration:none;">← Equipe</a>
    <div style="display:flex;justify-content:space-between;align-items:start;margin-top:8px;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $user->name }}</h1>
            <p style="font-size:.85rem;color:#64748b;">{{ $user->job_title ?: 'Colaborador' }} · {{ $user->email }}</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('client.team.edit', $user) }}" style="background:#f1f5f9;color:#374151;padding:9px 18px;border-radius:8px;font-size:.825rem;font-weight:600;text-decoration:none;">Editar</a>
            @if($user->status === 'active')
            <form method="POST" action="{{ route('client.team.deactivate', $user) }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:#fef2f2;color:#991b1b;padding:9px 18px;border-radius:8px;font-size:.825rem;font-weight:600;border:none;cursor:pointer;">Desativar</button>
            </form>
            @else
            <form method="POST" action="{{ route('client.team.activate', $user) }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:#f0fdf4;color:#166534;padding:9px 18px;border-radius:8px;font-size:.825rem;font-weight:600;border:none;cursor:pointer;">Ativar</button>
            </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    {{-- Info --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
        <h3 style="font-size:.875rem;font-weight:700;color:#374151;margin-bottom:16px;">Informações</h3>
        <div style="display:grid;gap:12px;font-size:.875rem;">
            <div><span style="color:#64748b;">Nome:</span> <strong>{{ $user->name }}</strong></div>
            <div><span style="color:#64748b;">E-mail:</span> {{ $user->email }}</div>
            <div><span style="color:#64748b;">Cargo:</span> {{ $user->job_title ?: '—' }}</div>
            <div><span style="color:#64748b;">Status:</span>
                @if($user->status === 'active')
                    <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:20px;font-size:.75rem;font-weight:600;">Ativo</span>
                @else
                    <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:.75rem;font-weight:600;">Inativo</span>
                @endif
            </div>
            <div><span style="color:#64748b;">Último login:</span> {{ $user->last_login_at?->format('d/m/Y H:i') ?: 'Nunca' }}</div>
        </div>
    </div>

    {{-- Permissions --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:.875rem;font-weight:700;color:#374151;">Permissões</h3>
        </div>

        <form method="POST" action="{{ route('client.team.permissions', $user) }}">
            @csrf @method('PUT')
            <div style="display:grid;gap:8px;margin-bottom:16px;">
                @foreach($availablePermissions as $key => $label)
                <label style="display:flex;align-items:center;gap:10px;font-size:.825rem;cursor:pointer;">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                        {{ $user->permissions->pluck('key')->contains($key) ? 'checked' : '' }}
                        style="width:15px;height:15px;accent-color:#6366f1;">
                    {{ $label }}
                </label>
                @endforeach
            </div>
            @if(auth()->user()->isCliente())
            <button type="submit" style="background:#6366f1;color:#fff;padding:8px 16px;border-radius:7px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;width:100%;">Salvar Permissões</button>
            @endif
        </form>
    </div>
</div>

{{-- Danger zone --}}
@if(auth()->user()->isCliente())
<div style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:20px;margin-top:20px;">
    <h4 style="font-size:.875rem;font-weight:700;color:#991b1b;margin-bottom:8px;">Zona de risco</h4>
    <p style="font-size:.8rem;color:#64748b;margin-bottom:12px;">Esta ação é permanente e não pode ser desfeita.</p>
    <form method="POST" action="{{ route('client.team.destroy', $user) }}" onsubmit="return confirm('Remover {{ $user->name }} da equipe?')">
        @csrf @method('DELETE')
        <button type="submit" style="background:#fee2e2;color:#991b1b;padding:9px 18px;border-radius:8px;font-size:.825rem;font-weight:600;border:none;cursor:pointer;">Remover colaborador</button>
    </form>
</div>
@endif

</x-layouts.app>
