<x-layouts.app title="Editar Agendamento">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:4px;">Editar Agendamento</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.ai-schedules.index') }}" style="color:#6b8fff;text-decoration:none;">Agendamentos IA</a> / Editar</p>
    </div>
</div>

@if($errors->any())
<div style="background:#2d0a0a;border:1px solid #7f1d1d;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#f87171;font-size:.875rem;">
    <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.ai-schedules.update', $aiSchedule) }}">
@csrf @method('PUT')
<div class="table-wrapper" style="padding:28px;max-width:600px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
            <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Funcionário IA *</label>
            <select name="ai_employee_id" required style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;">
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('ai_employee_id',$aiSchedule->ai_employee_id)==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Frequência *</label>
            <select name="frequency" required style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;">
                @foreach(['manual'=>'Manual','hourly'=>'De hora em hora','daily'=>'Diário','weekly'=>'Semanal','monthly'=>'Mensal'] as $val=>$label)
                <option value="{{ $val }}" {{ old('frequency',$aiSchedule->frequency)===$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
            <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Minutos por execução</label>
            <input type="number" name="work_minutes_per_run" value="{{ old('work_minutes_per_run',$aiSchedule->work_minutes_per_run) }}" min="1" max="480" style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;">
        </div>
        <div>
            <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Máx. tarefas por execução</label>
            <input type="number" name="max_tasks_per_run" value="{{ old('max_tasks_per_run',$aiSchedule->max_tasks_per_run) }}" min="1" max="20" style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
            <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Cliente</label>
            <select name="client_id" style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;">
                <option value="">— Nenhum —</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id',$aiSchedule->client_id)==$client->id?'selected':'' }}>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;color:#94a3b8;display:block;margin-bottom:6px;">Horário do dia</label>
            <input type="time" name="time_of_day" value="{{ old('time_of_day', $aiSchedule->time_of_day) }}" style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;">
        </div>
    </div>

    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" name="active" value="1" {{ old('active',$aiSchedule->active)?'checked':'' }} style="accent-color:#4a6cf7;width:16px;height:16px;">
        <span style="font-size:.8rem;color:#94a3b8;">Ativo</span>
    </label>
</div>

<div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end;">
    <a href="{{ route('admin.ai-schedules.index') }}" style="background:#1e293b;color:#94a3b8;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Cancelar</a>
    <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;">Salvar</button>
</div>
</form>

</x-layouts.app>
