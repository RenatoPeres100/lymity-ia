<x-layouts.app title="Editar Memória IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Editar Memória</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.ai-memories.index') }}" style="color:#6b8fff;text-decoration:none;">Memórias IA</a> / Editar</p>
    </div>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#dc2626;font-size:.875rem;">
    <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.ai-memories.update', $aiMemory) }}">
@csrf @method('PUT')
<div class="table-wrapper" style="padding:28px;max-width:700px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Funcionário IA *</label>
            <select name="ai_employee_id" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('ai_employee_id',$aiMemory->ai_employee_id)==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Tipo *</label>
            <select name="memory_type" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                @foreach(['brand'=>'Marca','preference'=>'Preferência','feedback'=>'Feedback','performance'=>'Desempenho','rule'=>'Regra','insight'=>'Insight'] as $val=>$label)
                <option value="{{ $val }}" {{ old('memory_type',$aiMemory->memory_type)===$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Título *</label>
            <input type="text" name="title" value="{{ old('title',$aiMemory->title) }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
        </div>
        <div>
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Peso (1-10)</label>
            <input type="number" name="weight" value="{{ old('weight',$aiMemory->weight) }}" min="1" max="10" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
        </div>
    </div>

    <div style="margin-bottom:16px;">
        <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Cliente</label>
        <select name="client_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
            <option value="">— Global —</option>
            @foreach($clients as $client)
            <option value="{{ $client->id }}" {{ old('client_id',$aiMemory->client_id)==$client->id?'selected':'' }}>{{ $client->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Conteúdo *</label>
        <textarea name="content" rows="5" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;">{{ old('content',$aiMemory->content) }}</textarea>
    </div>
</div>

<div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end;">
    <a href="{{ route('admin.ai-memories.index') }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Cancelar</a>
    <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;">Salvar</button>
</div>
</form>

</x-layouts.app>
