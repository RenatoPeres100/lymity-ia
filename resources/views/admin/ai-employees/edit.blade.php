<x-layouts.app title="Editar {{ $aiEmployee->name }}">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Editar {{ $aiEmployee->name }}</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.ai-employees.index') }}" style="color:#6b8fff;text-decoration:none;">Funcionários IA</a> / Editar</p>
    </div>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#dc2626;font-size:.875rem;">
    <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.ai-employees.update', $aiEmployee) }}">
@csrf
@method('PUT')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:28px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:18px;">Informações básicas</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $aiEmployee->name) }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Role Key *</label>
                    <input type="text" name="role_key" value="{{ old('role_key', $aiEmployee->role_key) }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Título</label>
                    <input type="text" name="title" value="{{ old('title', $aiEmployee->title) }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Avatar (emoji)</label>
                    <input type="text" name="avatar" value="{{ old('avatar', $aiEmployee->avatar) }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Descrição</label>
                <textarea name="description" rows="3" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;">{{ old('description', $aiEmployee->description) }}</textarea>
            </div>

            <div>
                <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">System Prompt</label>
                <textarea name="system_prompt" rows="5" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;font-family:monospace;">{{ old('system_prompt', $aiEmployee->system_prompt) }}</textarea>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Configurações</div>

            <div style="margin-bottom:14px;">
                <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Autonomia</label>
                <select name="autonomy_level" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    @foreach(['low'=>'Baixa','medium'=>'Média','high'=>'Alta'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('autonomy_level',$aiEmployee->autonomy_level)===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Status</label>
                <select name="status" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    @foreach(['active'=>'Ativo','inactive'=>'Inativo','disabled'=>'Desativado'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('status',$aiEmployee->status)===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Máx. tarefas/dia</label>
                <input type="number" name="max_tasks_per_day" value="{{ old('max_tasks_per_day', $aiEmployee->max_tasks_per_day) }}" min="1" max="100" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Cliente padrão</label>
                <select name="default_client_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    <option value="">— Nenhum —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('default_client_id',$aiEmployee->default_client_id)==$client->id?'selected':'' }}>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach([
                    ['requires_approval_default','Requer aprovação por padrão'],
                    ['can_publish','Pode publicar'],
                    ['can_send_messages','Pode enviar mensagens'],
                    ['can_manage_ads_budget','Pode gerenciar orçamento'],
                ] as [$field,$label])
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, $aiEmployee->$field ? '1' : '0')=='1'?'checked':'' }} style="accent-color:#4a6cf7;width:16px;height:16px;">
                    <span style="font-size:.8rem;color:#94a3b8;">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Habilidades</div>
            @php $employeeSkillIds = $aiEmployee->skills->pluck('id')->toArray(); @endphp
            <div style="display:flex;flex-direction:column;gap:8px;max-height:200px;overflow-y:auto;">
                @foreach($skills as $skill)
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="skills[]" value="{{ $skill->id }}" {{ in_array($skill->id,$employeeSkillIds)?'checked':'' }} style="accent-color:#4a6cf7;width:14px;height:14px;">
                    <span style="font-size:.8rem;color:#94a3b8;">{{ $skill->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

</div>

<div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end;">
    <a href="{{ route('admin.ai-employees.show', $aiEmployee) }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Cancelar</a>
    <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;">Salvar Alterações</button>
</div>
</form>

</x-layouts.app>
