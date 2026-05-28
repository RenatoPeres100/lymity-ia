<x-layouts.app title="Editar {{ $user->name }}">

<div style="margin-bottom:24px;">
    <a href="{{ route('client.team.show', $user) }}" style="color:#64748b;font-size:.875rem;text-decoration:none;">← {{ $user->name }}</a>
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-top:8px;">Editar Colaborador</h1>
</div>

<form method="POST" action="{{ route('client.team.update', $user) }}" style="max-width:640px;">
    @csrf @method('PUT')

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#991b1b;font-size:.875rem;">
        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
    </div>
    @endif

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;margin-bottom:20px;">

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Nome completo <span style="color:#ef4444;">*</span></label>
            <input name="name" value="{{ old('name', $user->name) }}" required style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">E-mail</label>
            <input value="{{ $user->email }}" disabled style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;box-sizing:border-box;background:#f8fafc;color:#94a3b8;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Cargo / Função</label>
            <input name="job_title" value="{{ old('job_title', $user->job_title) }}" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;box-sizing:border-box;">
        </div>

        <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Status</label>
            <select name="status" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;box-sizing:border-box;background:#fff;">
                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Ativo</option>
                <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;margin-bottom:20px;">
        <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:6px;">Permissões</h3>
        <p style="font-size:.8rem;color:#64748b;margin-bottom:16px;">Selecione o que este colaborador pode acessar.</p>
        <div style="display:grid;gap:10px;">
            @foreach($availablePermissions as $key => $label)
            <label style="display:flex;align-items:center;gap:12px;padding:12px 16px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;background:#f8fafc;">
                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                    {{ $user->permissions->pluck('key')->contains($key) ? 'checked' : '' }}
                    style="width:16px;height:16px;accent-color:#6366f1;">
                <span style="font-size:.875rem;color:#0f172a;">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <div style="display:flex;gap:12px;">
        <button type="submit" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;">Salvar Alterações</button>
        <a href="{{ route('client.team.show', $user) }}" style="color:#64748b;padding:12px 20px;font-size:.875rem;text-decoration:none;">Cancelar</a>
    </div>
</form>

</x-layouts.app>
