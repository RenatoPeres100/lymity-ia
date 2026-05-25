<x-layouts.app title="Editar Contrato">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
    <a href="{{ route('admin.contracts.show', $clientContract) }}" style="color:#64748b;font-size:.875rem;">← Contrato</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">Editar: {{ $clientContract->title }}</h2>
</div>

@if($errors->any())
<div class="alert alert-error"><ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.contracts.update', $clientContract) }}">
    @csrf @method('PUT')
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
        <div class="card">
            <div class="card-header"><span class="card-title">Dados do Contrato</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Cliente *</label>
                    <select name="client_id" class="form-input" required>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ $clientContract->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Título *</label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $clientContract->title) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descrição / Escopo</label>
                    <textarea name="description" class="form-input" rows="5">{{ old('description', $clientContract->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Caminho do Arquivo</label>
                    <input type="text" name="file_path" class="form-input" value="{{ old('file_path', $clientContract->file_path) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input" required>
                        @foreach(['draft'=>'Rascunho','pending_signature'=>'Aguardando Assinatura','signed'=>'Assinado','canceled'=>'Cancelado','archived'=>'Arquivado'] as $val=>$label)
                        <option value="{{ $val }}" {{ $clientContract->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Salvar</button>
                <a href="{{ route('admin.contracts.show', $clientContract) }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Cancelar</a>
            </div>
        </div>
    </div>
</form>

</x-layouts.app>
