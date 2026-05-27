<x-layouts.app title="Permissões — {{ $user->name }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
    <a href="{{ route('admin.users.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Usuários</a>
    <span style="color:#cbd5e1;">/</span>
    <a href="{{ route('admin.users.show', $user) }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">{{ $user->name }}</a>
    <span style="color:#cbd5e1;">/</span>
    <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;">Permissões</h1>
</div>

@if($user->isAdminGeral())
<div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:10px;padding:16px 20px;margin-bottom:24px;color:#1e40af;font-size:.85rem;">
    ℹ️ Este usuário é <strong>Admin Geral</strong> e tem acesso irrestrito a toda a plataforma. As permissões abaixo não restringem seu acesso.
</div>
@endif

<form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
@csrf
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
            <h2 style="font-size:.95rem;font-weight:700;color:#0f172a;">Permissões de {{ $user->name }}</h2>
            <p style="font-size:.8rem;color:#64748b;margin-top:4px;">Role atual: <strong>{{ $user->role_label }}</strong></p>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="button" onclick="toggleAll(true)" style="background:#eff6ff;color:#2563eb;border:1px solid #93c5fd;padding:7px 14px;border-radius:7px;font-size:.78rem;cursor:pointer;">Marcar tudo</button>
            <button type="button" onclick="toggleAll(false)" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:7px 14px;border-radius:7px;font-size:.78rem;cursor:pointer;">Desmarcar tudo</button>
        </div>
    </div>

    @foreach($grouped as $module => $perms)
    @php
        $moduleLabels = [
            'users'=>'Usuários','clients'=>'Clientes','settings'=>'Configurações',
            'dashboard'=>'Dashboard','operation'=>'Operação','approvals'=>'Aprovações',
            'blog'=>'Blog','social'=>'Social Media','instagram'=>'Instagram',
            'content'=>'Conteúdo','ai'=>'Funcionários IA','logs'=>'Logs',
            'system'=>'Sistema','ads'=>'Ads','seo'=>'SEO',
        ];
        $label = $moduleLabels[$module] ?? ucfirst($module);
    @endphp
    <div style="margin-bottom:20px;">
        <div style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f1f5f9;">{{ $label }}</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
            @foreach($perms as $perm)
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 12px;background:{{ in_array($perm->id,$userPermIds)?'#eff6ff':'#f8fafc' }};border:1px solid {{ in_array($perm->id,$userPermIds)?'#93c5fd':'#e2e8f0' }};border-radius:7px;transition:.15s;" onmousedown="this.style.opacity='.7'" onmouseup="this.style.opacity='1'">
                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" {{ in_array($perm->id,$userPermIds)?'checked':'' }} style="accent-color:#4a6cf7;width:14px;height:14px;">
                <div>
                    <div style="font-size:.78rem;font-weight:600;color:#0f172a;">{{ $perm->name }}</div>
                    <code style="font-size:.65rem;color:#94a3b8;">{{ $perm->key }}</code>
                </div>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach

    <div style="display:flex;gap:12px;padding-top:16px;border-top:1px solid #f1f5f9;">
        <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.85rem;font-weight:600;padding:11px 24px;border-radius:8px;border:none;cursor:pointer;">Salvar permissões</button>
        <a href="{{ route('admin.users.show', $user) }}" style="background:#f1f5f9;color:#475569;font-size:.85rem;font-weight:600;padding:11px 24px;border-radius:8px;text-decoration:none;">Cancelar</a>
    </div>
</div>
</form>

<script>
function toggleAll(state) {
    document.querySelectorAll('input[type=checkbox]').forEach(cb => {
        cb.checked = state;
        const label = cb.closest('label');
        label.style.background   = state ? '#eff6ff' : '#f8fafc';
        label.style.borderColor  = state ? '#93c5fd' : '#e2e8f0';
    });
}
</script>

</x-layouts.app>
