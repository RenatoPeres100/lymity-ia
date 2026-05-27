<x-layouts.app title="Redefinir Senha — {{ $user->name }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
    <a href="{{ route('admin.users.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Usuários</a>
    <span style="color:#cbd5e1;">/</span>
    <a href="{{ route('admin.users.show', $user) }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">{{ $user->name }}</a>
    <span style="color:#cbd5e1;">/</span>
    <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;">Redefinir Senha</h1>
</div>

<div style="max-width:480px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:28px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
            <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#4a6cf7,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem;font-weight:700;">
                {{ strtoupper(substr($user->name,0,1)) }}
            </div>
            <div>
                <div style="font-weight:600;color:#0f172a;">{{ $user->name }}</div>
                <div style="font-size:.8rem;color:#64748b;">{{ $user->email }}</div>
            </div>
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.8rem;color:#dc2626;">
            <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.users.reset-password.update', $user) }}">
        @csrf
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Nova senha *</label>
                <input type="password" name="password" required minlength="8" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Confirmar senha *</label>
                <input type="password" name="password_confirmation" required style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
            </div>
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 14px;margin-bottom:20px;font-size:.78rem;color:#c2410c;">
                ⚠️ A senha atual do usuário será substituída imediatamente. Esta ação é registrada em log.
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="flex:1;background:#dc2626;color:#fff;font-size:.85rem;font-weight:600;padding:11px;border-radius:8px;border:none;cursor:pointer;">Redefinir senha</button>
                <a href="{{ route('admin.users.show', $user) }}" style="flex:1;background:#f1f5f9;color:#475569;font-size:.85rem;font-weight:600;padding:11px;border-radius:8px;text-decoration:none;text-align:center;">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
