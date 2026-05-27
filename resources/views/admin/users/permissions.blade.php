<x-layouts.app title="Permissões — {{ $user->name }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
    <a href="{{ route('admin.users.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Usuários</a>
    <span style="color:#cbd5e1;">/</span>
    <a href="{{ route('admin.users.show', $user) }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">{{ $user->name }}</a>
    <span style="color:#cbd5e1;">/</span>
    <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;">Permissões</h1>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

{{-- Role preset info banner --}}
<div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
    <div>
        <div style="font-size:.78rem;font-weight:700;color:#1e40af;margin-bottom:2px;">Perfil: {{ $user->role_label }}</div>
        <div style="font-size:.82rem;color:#3b82f6;">{{ $presetLabel }}</div>
        @if($user->isAdminGeral())
        <div style="font-size:.75rem;color:#6b7280;margin-top:4px;">Admin Geral tem acesso irrestrito — permissões abaixo são informativas.</div>
        @else
        <div style="font-size:.75rem;color:#6b7280;margin-top:4px;">
            <strong>{{ count($presetIds) }}</strong> permissões padrão para este perfil ·
            <strong>{{ count($userPermIds) }}</strong> permissões atuais do usuário
        </div>
        @endif
    </div>
    @if(!$user->isAdminGeral())
    <form method="POST" action="{{ route('admin.users.permissions.reset', $user) }}" style="flex-shrink:0;">
        @csrf
        <button type="submit"
            onclick="return confirm('Redefinir para as permissões padrão do perfil {{ $user->role_label }}?\nPermissões personalizadas serão substituídas.')"
            style="background:#fff;color:#2563eb;border:1px solid #93c5fd;padding:8px 16px;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;">
            ↺ Redefinir para padrão do perfil
        </button>
    </form>
    @endif
</div>

<form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
@csrf
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-size:.95rem;font-weight:700;color:#0f172a;">Permissões de {{ $user->name }}</h2>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:4px;">
                <span style="display:inline-block;width:12px;height:12px;background:#dbeafe;border:1px solid #93c5fd;border-radius:2px;vertical-align:middle;margin-right:4px;"></span>Padrão do perfil
                <span style="display:inline-block;width:12px;height:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:2px;vertical-align:middle;margin-left:12px;margin-right:4px;"></span>Marcado
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="toggleAll(true)" style="background:#eff6ff;color:#2563eb;border:1px solid #93c5fd;padding:7px 14px;border-radius:7px;font-size:.78rem;cursor:pointer;">Marcar tudo</button>
            <button type="button" onclick="toggleAll(false)" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:7px 14px;border-radius:7px;font-size:.78rem;cursor:pointer;">Desmarcar tudo</button>
            <button type="button" onclick="applyPreset()" style="background:#f0fdf4;color:#16a34a;border:1px solid #86efac;padding:7px 14px;border-radius:7px;font-size:.78rem;cursor:pointer;">Aplicar padrão</button>
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
    <div style="margin-bottom:22px;">
        <div style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f1f5f9;">{{ $label }}</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
            @foreach($perms as $perm)
            @php
                $isChecked  = in_array($perm->id, $userPermIds);
                $isPreset   = in_array($perm->id, $presetIds);
                $bg     = $isChecked ? '#eff6ff' : ($isPreset ? '#f0fdf4' : '#f8fafc');
                $border = $isChecked ? '#93c5fd' : ($isPreset ? '#86efac' : '#e2e8f0');
            @endphp
            <label data-preset="{{ $isPreset ? '1' : '0' }}" data-perm-id="{{ $perm->id }}"
                style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;padding:8px 12px;background:{{ $bg }};border:1px solid {{ $border }};border-radius:7px;transition:.1s;">
                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                    {{ $isChecked ? 'checked' : '' }}
                    style="accent-color:#4a6cf7;width:14px;height:14px;flex-shrink:0;margin-top:2px;">
                <div>
                    <div style="font-size:.78rem;font-weight:600;color:#0f172a;line-height:1.3;">{{ $perm->name }}</div>
                    <code style="font-size:.65rem;color:#94a3b8;">{{ $perm->key }}</code>
                    @if($isPreset)
                    <div style="font-size:.62rem;color:#16a34a;font-weight:600;margin-top:1px;">✓ padrão do perfil</div>
                    @endif
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
const presetIds = @json($presetIds);

function toggleAll(state) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.checked = state;
        styleLabel(cb.closest('label'), state, cb.closest('label').dataset.preset === '1');
    });
}

function applyPreset() {
    document.querySelectorAll('label[data-perm-id]').forEach(label => {
        const id = parseInt(label.dataset.permId);
        const cb = label.querySelector('input');
        cb.checked = presetIds.includes(id);
        styleLabel(label, cb.checked, label.dataset.preset === '1');
    });
}

function styleLabel(label, checked, isPreset) {
    if (checked) {
        label.style.background = '#eff6ff';
        label.style.borderColor = '#93c5fd';
    } else if (isPreset) {
        label.style.background = '#f0fdf4';
        label.style.borderColor = '#86efac';
    } else {
        label.style.background = '#f8fafc';
        label.style.borderColor = '#e2e8f0';
    }
}

document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
    cb.addEventListener('change', function() {
        const label   = this.closest('label');
        const isPreset = label.dataset.preset === '1';
        styleLabel(label, this.checked, isPreset);
    });
});
</script>

</x-layouts.app>
