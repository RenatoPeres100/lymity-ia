<x-layouts.app title="Nova Tarefa IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Nova Tarefa IA</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.ai-tasks.index') }}" style="color:#6b8fff;text-decoration:none;">Tarefas IA</a> / Criar</p>
    </div>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#dc2626;font-size:.875rem;">
    <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.ai-tasks.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

    <div class="table-wrapper" style="padding:28px;">
        <div style="font-size:.8rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:18px;">Detalhes da Tarefa</div>

        <div style="margin-bottom:16px;">
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Funcionário IA *</label>
            <select name="ai_employee_id" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                <option value="">— Selecione —</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('ai_employee_id')==$emp->id?'selected':'' }}>{{ $emp->name }} — {{ $emp->title }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Título *</label>
            <input type="text" name="title" value="{{ old('title') }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;" placeholder="Descreva o objetivo da tarefa">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Tipo de Tarefa</label>
                <select name="task_type" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    <option value="general">Geral</option>
                    <option value="social_post" {{ old('task_type')==='social_post'?'selected':'' }}>Post Social Media</option>
                    <option value="seo_plan" {{ old('task_type')==='seo_plan'?'selected':'' }}>Plano SEO</option>
                    <option value="ads_analysis" {{ old('task_type')==='ads_analysis'?'selected':'' }}>Análise de Anúncios</option>
                    <option value="copywriting" {{ old('task_type')==='copywriting'?'selected':'' }}>Copywriting</option>
                    <option value="project_plan" {{ old('task_type')==='project_plan'?'selected':'' }}>Plano de Projeto</option>
                    <option value="lead_qualification" {{ old('task_type')==='lead_qualification'?'selected':'' }}>Qualificação de Lead</option>
                    <option value="data_analysis" {{ old('task_type')==='data_analysis'?'selected':'' }}>Análise de Dados</option>
                    <option value="creative_briefing" {{ old('task_type')==='creative_briefing'?'selected':'' }}>Briefing Criativo</option>
                </select>
            </div>
            <div>
                <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Prioridade</label>
                <select name="priority" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    <option value="low">Baixa</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high" {{ old('priority')==='high'?'selected':'' }}>Alta</option>
                    <option value="urgent" {{ old('priority')==='urgent'?'selected':'' }}>Urgente</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Cliente</label>
            <select name="client_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                <option value="">— Nenhum —</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id')==$client->id?'selected':'' }}>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:.78rem;color:#475569;display:block;margin-bottom:6px;">Descrição / Instruções</label>
            <textarea name="description" rows="4" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;" placeholder="Contexto adicional para o funcionário IA...">{{ old('description') }}</textarea>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Controles</div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="hidden" name="requires_approval" value="0">
                    <input type="checkbox" name="requires_approval" value="1" checked style="accent-color:#4a6cf7;width:16px;height:16px;">
                    <div>
                        <div style="font-size:.8rem;color:#334155;font-weight:600;">Requer aprovação</div>
                        <div style="font-size:.72rem;color:#475569;">Output aguardará revisão</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="hidden" name="sensitive_action" value="0">
                    <input type="checkbox" name="sensitive_action" value="1" style="accent-color:#f59e0b;width:16px;height:16px;">
                    <div>
                        <div style="font-size:.8rem;color:#334155;font-weight:600;">Ação sensível</div>
                        <div style="font-size:.72rem;color:#475569;">Envolve publicação ou verba</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="table-wrapper" style="padding:24px;background:#0f1a0e;border-color:#166534;">
            <div style="font-size:.72rem;color:#4ade80;font-weight:700;margin-bottom:8px;">⚠️ Modo Mock Ativo</div>
            <p style="font-size:.78rem;color:#6b8fff;line-height:1.6;margin:0;">O output gerado é apenas rascunho. Nenhuma publicação real será feita.</p>
        </div>
    </div>

</div>

<div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end;">
    <a href="{{ route('admin.ai-tasks.index') }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Cancelar</a>
    <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;">Criar Tarefa</button>
</div>
</form>

</x-layouts.app>
