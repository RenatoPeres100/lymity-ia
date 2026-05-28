<x-layouts.app title="Editar Usuário">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
    <a href="{{ route('admin.users.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Usuários</a>
    <span style="color:#cbd5e1;">/</span>
    <a href="{{ route('admin.users.show', $user) }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">{{ $user->name }}</a>
    <span style="color:#cbd5e1;">/</span>
    <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;">Editar</h1>
</div>

<form method="POST" action="{{ route('admin.users.update', $user) }}">
@csrf @method('PUT')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:28px;">
        <h2 style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Dados do Usuário</h2>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.8rem;color:#dc2626;">
            <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Nome *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">E-mail *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
            </div>
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Cargo / Função</label>
                <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Status *</label>
                <select name="status" required style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                    <option value="active"   {{ old('status',$user->status)==='active'?'selected':'' }}>Ativo</option>
                    <option value="inactive" {{ old('status',$user->status)==='inactive'?'selected':'' }}>Inativo</option>
                    <option value="blocked"  {{ old('status',$user->status)==='blocked'?'selected':'' }}>Bloqueado</option>
                </select>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Acesso</h2>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Tipo de usuário *</label>
                <select name="user_type" id="user_type" required onchange="handleTypeChange(this.value)" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;">
                    <option value="internal" {{ old('user_type',$user->user_type)==='internal'?'selected':'' }}>Interno (Agência)</option>
                    <option value="agency"   {{ old('user_type',$user->user_type)==='agency'?'selected':'' }}>Agência</option>
                    <option value="client"   {{ old('user_type',$user->user_type)==='client'?'selected':'' }}>Cliente</option>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Perfil (role) *</label>
                <select name="role" id="role_select" required style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;">
                    @foreach($roles as $key => $label)
                    <option value="{{ $key }}" {{ old('role',$user->role)===$key?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div id="admin_alert" style="display:none;background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:8px 12px;margin-top:8px;font-size:.75rem;color:#c2410c;">
                    ⚠️ Admin Geral tem acesso irrestrito.
                </div>
            </div>

            <div id="company_field">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Empresa</label>
                <select name="company_id" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;">
                    <option value="">— Selecionar empresa —</option>
                    @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ old('company_id',$user->company_id)==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="client_field" style="display:none;margin-top:14px;">
                <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:5px;">Cliente vinculado</label>
                <select name="client_id" id="client_select" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;">
                    <option value="">— Selecionar cliente —</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id',$user->client_id)==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:10px;padding:14px;">
            <div style="font-size:.75rem;font-weight:600;color:#1e40af;margin-bottom:4px;">Criado em</div>
            <div style="font-size:.8rem;color:#1d4ed8;">{{ $user->created_at->format('d/m/Y H:i') }}</div>
            @if($user->last_login_at)
            <div style="font-size:.75rem;font-weight:600;color:#1e40af;margin-top:8px;margin-bottom:4px;">Último acesso</div>
            <div style="font-size:.8rem;color:#1d4ed8;">{{ $user->last_login_at->format('d/m/Y H:i') }}</div>
            @endif
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" style="flex:1;background:#4a6cf7;color:#fff;font-size:.85rem;font-weight:600;padding:11px;border-radius:8px;border:none;cursor:pointer;">Salvar alterações</button>
            <a href="{{ route('admin.users.show', $user) }}" style="flex:1;background:#f1f5f9;color:#475569;font-size:.85rem;font-weight:600;padding:11px;border-radius:8px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </div>
</div>
</form>

<script>
function handleTypeChange(type) {
    const cf = document.getElementById('client_field');
    const co = document.getElementById('company_field');
    const cs = document.getElementById('client_select');
    if (type === 'client') { cf.style.display='block'; co.style.display='none'; cs.required=true; }
    else                   { cf.style.display='none';  co.style.display='block'; cs.required=false; }
}
document.getElementById('role_select').addEventListener('change', function() {
    document.getElementById('admin_alert').style.display = this.value === 'admin_geral' ? 'block' : 'none';
});
handleTypeChange(document.getElementById('user_type').value);
if (document.getElementById('role_select').value === 'admin_geral') document.getElementById('admin_alert').style.display='block';
</script>

</x-layouts.app>
