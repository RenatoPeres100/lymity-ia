<x-layouts.app title="Nova Aprovação">

<div style="margin-bottom:24px;">
    <a href="{{ route('admin.approvals.index') }}" style="color:#6b8fff;font-size:.8rem;text-decoration:none;">← Voltar para Aprovações</a>
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-top:8px;">Nova Solicitação de Aprovação</h1>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#dc2626;font-size:.875rem;">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:28px;max-width:720px;">
    <form method="POST" action="{{ route('admin.approvals.store') }}">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <div>
                <label style="display:block;font-size:.8rem;font-weight:600;color:#475569;margin-bottom:6px;">Cliente (opcional)</label>
                <select name="client_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    <option value="">Sem cliente específico</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.8rem;font-weight:600;color:#475569;margin-bottom:6px;">Vencimento (opcional)</label>
                <input type="date" name="due_at" value="{{ old('due_at') }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#475569;margin-bottom:6px;">Título <span style="color:#f87171;">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;" placeholder="Descreva brevemente o que precisa de aprovação">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#475569;margin-bottom:6px;">Descrição</label>
            <textarea name="description" rows="4" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;" placeholder="Detalhes adicionais sobre a aprovação...">{{ old('description') }}</textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <div>
                <label style="display:block;font-size:.8rem;font-weight:600;color:#475569;margin-bottom:6px;">Tipo de Aprovação <span style="color:#f87171;">*</span></label>
                <select name="approval_type" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    @foreach(['post'=>'Post','campaign'=>'Campanha','budget'=>'Orçamento','blog'=>'Blog','website_page'=>'Página Website','proposal'=>'Proposta','external_action'=>'Ação Externa','ai_task'=>'Tarefa IA','other'=>'Outro'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('approval_type')===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.8rem;font-weight:600;color:#475569;margin-bottom:6px;">Nível de Sensibilidade <span style="color:#f87171;">*</span></label>
                <select name="sensitive_level" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                    @foreach(['low'=>'Baixo','medium'=>'Médio','high'=>'Alto','critical'=>'Crítico'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('sensitive_level')===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="margin-bottom:28px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#475569;margin-bottom:6px;">Payload / Dados adicionais (JSON ou texto livre)</label>
            <textarea name="payload" rows="3" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.8rem;resize:vertical;font-family:monospace;" placeholder='{"observacao":"..."}'>{{ old('payload') }}</textarea>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;">Criar Solicitação</button>
            <a href="{{ route('admin.approvals.index') }}" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;">Cancelar</a>
        </div>
    </form>
</div>

</x-layouts.app>
