<x-layouts.app title="Novo Orçamento">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
    <a href="{{ route('admin.budgets.index') }}" style="color:#64748b;font-size:.875rem;">← Orçamentos</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">Novo Orçamento</h2>
</div>

@if($errors->any())
<div class="alert alert-error"><ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.budgets.store') }}">
    @csrf
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Dados do Orçamento</span></div>
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
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-input" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Mês (1–12)</label>
                            <input type="number" name="month" class="form-input" min="1" max="12" value="{{ old('month', now()->month) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ano</label>
                            <input type="number" name="year" class="form-input" min="2024" max="2100" value="{{ old('year', now()->year) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:1rem;">
                <div class="card-header">
                    <span class="card-title">Itens do Orçamento</span>
                    <button type="button" onclick="addItem()" class="btn btn-secondary" style="font-size:.8rem;padding:6px 12px;">+ Adicionar Item</button>
                </div>
                <div class="card-body" id="itemsContainer">
                    <div id="emptyMsg" style="text-align:center;padding:1rem;color:#94a3b8;font-size:.875rem;">Nenhum item.</div>
                </div>
                <div style="padding:14px 22px;border-top:1px solid #f1f5f9;text-align:right;">
                    <strong style="font-size:.875rem;color:#64748b;">Total: </strong>
                    <strong id="totalDisplay" style="font-size:1.1rem;color:#1e293b;">R$ 0,00</strong>
                </div>
            </div>
        </div>

        <div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Salvar Orçamento</button>
                <a href="{{ route('admin.budgets.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<template id="itemTemplate">
    <div class="item-row" style="border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:10px;">
        <div style="display:grid;grid-template-columns:1fr auto;gap:8px;margin-bottom:8px;">
            <input type="text" name="items[__IDX__][name]" class="form-input" placeholder="Nome do item *" required>
            <button type="button" onclick="removeItem(this)" style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;border-radius:8px;padding:6px 10px;cursor:pointer;">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
            <div>
                <label style="font-size:.75rem;color:#64748b;">Categoria</label>
                <select name="items[__IDX__][category]" class="form-input" required>
                    <option value="media">Mídia</option>
                    <option value="production">Produção</option>
                    <option value="service">Serviço</option>
                    <option value="tool">Ferramenta</option>
                    <option value="other">Outro</option>
                </select>
            </div>
            <div>
                <label style="font-size:.75rem;color:#64748b;">Valor (R$)</label>
                <input type="number" name="items[__IDX__][amount]" class="form-input item-amount" step="0.01" min="0" value="0" oninput="updateTotal()">
            </div>
        </div>
        <input type="text" name="items[__IDX__][description]" class="form-input" placeholder="Descrição (opcional)">
    </div>
</template>

<script>
let itemCount = 0;

function addItem() {
    document.getElementById('emptyMsg')?.remove();
    const tpl = document.getElementById('itemTemplate').innerHTML.replace(/__IDX__/g, itemCount++);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('itemsContainer').appendChild(div.firstElementChild);
    updateTotal();
}

function removeItem(btn) {
    btn.closest('.item-row').remove();
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.item-amount').forEach(i => total += parseFloat(i.value || 0));
    document.getElementById('totalDisplay').textContent = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}
</script>

</x-layouts.app>
