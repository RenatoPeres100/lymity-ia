<x-layouts.app title="Novo Colaborador">

<div style="margin-bottom:24px;">
    <a href="{{ route('client.team.index') }}" style="color:#64748b;font-size:.875rem;text-decoration:none;">← Equipe</a>
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-top:8px;">Novo Colaborador</h1>
    <p style="font-size:.85rem;color:#64748b;">Adicione um membro da equipe ao seu ambiente.</p>
</div>

<form method="POST" action="{{ route('client.team.store') }}" style="max-width:640px;">
    @csrf

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#991b1b;font-size:.875rem;">
        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
    </div>
    @endif

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;margin-bottom:20px;">
        <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:20px;">Dados do Colaborador</h3>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Nome completo <span style="color:#ef4444;">*</span></label>
            <input name="name" value="{{ old('name') }}" required style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;box-sizing:border-box;" placeholder="Ex: Maria Silva">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">E-mail <span style="color:#ef4444;">*</span></label>
            <input name="email" type="email" value="{{ old('email') }}" required style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;box-sizing:border-box;" placeholder="maria@empresa.com">
            <p style="font-size:.75rem;color:#64748b;margin-top:4px;">Uma senha temporária será gerada automaticamente.</p>
        </div>

        <div style="margin-bottom:4px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Cargo / Função</label>
            <input name="job_title" value="{{ old('job_title') }}" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;box-sizing:border-box;" placeholder="Ex: Gerente de Marketing">
        </div>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;margin-bottom:20px;">
        <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:6px;">Permissões</h3>
        <p style="font-size:.8rem;color:#64748b;margin-bottom:20px;">Defina o que este colaborador pode fazer no sistema.</p>

        <div style="display:grid;gap:10px;">
            @foreach($permissions as $key => $label)
            <label style="display:flex;align-items:center;gap:12px;padding:12px 16px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;background:#f8fafc;">
                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                    {{ in_array($key, ['approvals.view','blog.view','social.view','content.view']) ? 'checked' : '' }}
                    style="width:16px;height:16px;accent-color:#6366f1;">
                <div>
                    <div style="font-size:.875rem;font-weight:500;color:#0f172a;">{{ $label }}</div>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    <div style="display:flex;gap:12px;">
        <button type="submit" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;">Criar Colaborador</button>
        <a href="{{ route('client.team.index') }}" style="color:#64748b;padding:12px 20px;font-size:.875rem;text-decoration:none;">Cancelar</a>
    </div>
</form>

</x-layouts.app>
