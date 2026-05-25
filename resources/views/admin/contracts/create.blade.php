<x-layouts.app title="Novo Contrato">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
    <a href="{{ route('admin.contracts.index') }}" style="color:#64748b;font-size:.875rem;">← Contratos</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">Novo Contrato</h2>
</div>

@if($errors->any())
<div class="alert alert-error"><ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.contracts.store') }}">
    @csrf
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
        <div class="card">
            <div class="card-header"><span class="card-title">Dados do Contrato</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Cliente *</label>
                    <select name="client_id" class="form-input" required>
                        <option value="">Selecione...</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Título *</label>
                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descrição / Escopo</label>
                    <textarea name="description" class="form-input" rows="5">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Caminho do Arquivo (opcional)</label>
                    <input type="text" name="file_path" class="form-input" value="{{ old('file_path') }}" placeholder="/storage/contratos/contrato.pdf">
                    <span class="form-error" style="color:#94a3b8;font-size:.75rem;">Somente registros internos. Nenhum arquivo é processado nesta fase.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Inicial</label>
                    <select name="status" class="form-input" required>
                        @foreach(['draft'=>'Rascunho','pending_signature'=>'Aguardando Assinatura'] as $val=>$label)
                        <option value="{{ $val }}" {{ old('status','draft') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Salvar Contrato</button>
                <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Cancelar</a>
            </div>
        </div>
    </div>
</form>

</x-layouts.app>
