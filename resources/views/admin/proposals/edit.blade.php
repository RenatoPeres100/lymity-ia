<x-layouts.app title="Editar Proposta">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
    <a href="{{ route('admin.proposals.show', $proposal) }}" style="color:#64748b;font-size:.875rem;">← Proposta</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">Editar: {{ $proposal->title }}</h2>
</div>

@if($errors->any())
<div class="alert alert-error">
    <ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.proposals.update', $proposal) }}" id="proposalForm">
    @csrf @method('PUT')
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Dados da Proposta</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Cliente *</label>
                        <select name="client_id" class="form-input" required>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ $proposal->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título *</label>
                        <input type="text" name="title" class="form-input" value="{{ old('title', $proposal->title) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-input" rows="3">{{ old('description', $proposal->description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Termos e Condições</label>
                        <textarea name="terms" class="form-input" rows="4">{{ old('terms', $proposal->terms) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:1rem;">
                <div class="card-header">
                    <span class="card-title">Itens</span>
                    <button type="button" onclick="addItem()" class="btn btn-secondary" style="font-size:.8rem;padding:6px 12px;">+ Adicionar</button>
                </div>
                <div class="card-body" id="itemsContainer">
                    @forelse($proposal->items as $i => $item)
                    <div class="item-row" style="border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:10px;">
                        <div style="display:grid;grid-template-columns:1fr auto;gap:8px;margin-bottom:8px;">
                            <input type="text" name="items[{{ $i }}][name]" class="form-input" value="{{ $item->name }}" required>
                            <button type="button" onclick="removeItem(this)" style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;border-radius:8px;padding:6px 10px;cursor:pointer;">✕</button>
                        </div>
                        <input type="text" name="items[{{ $i }}][description]" class="form-input" value="{{ $item->description }}" placeholder="Descrição" style="margin-bottom:8px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <div>
                                <label style="font-size:.75rem;color:#64748b;">Qtd</label>
                                <input type="number" name="items[{{ $i }}][quantity]" class="form-input item-qty" step="0.01" min="0.01" value="{{ $item->quantity }}" oninput="updateTotal()">
                            </div>
                            <div>
                                <label style="font-size:.75rem;color:#64748b;">Preço Unit. (R$)</label>
                                <input type="number" name="items[{{ $i }}][unit_price]" class="form-input item-price" step="0.01" min="0" value="{{ $item->unit_price }}" oninput="updateTotal()">
                            </div>
                        </div>
                    </div>
                    @empty
                    <div id="emptyMsg" style="text-align:center;padding:1rem;color:#94a3b8;font-size:.875rem;">Nenhum item.</div>
                    @endforelse
                </div>
                <div style="padding:14px 22px;border-top:1px solid #f1f5f9;text-align:right;">
                    <strong style="font-size:.875rem;color:#64748b;">Total: </strong>
                    <strong id="totalDisplay" style="font-size:1.1rem;color:#1e293b;">R$ {{ number_format($proposal->total_amount ?? 0, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Configurações</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Válida até</label>
                        <input type="date" name="valid_until" class="form-input" value="{{ old('valid_until', $proposal->valid_until?->toDateString()) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status atual</label>
                        <span class="badge badge-{{ $proposal->status_color }}">{{ $proposal->status_label }}</span>
                    </div>
                </div>
            </div>
            <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Salvar Alterações</button>
                <a href="{{ route('admin.proposals.show', $proposal) }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Cancelar</a>
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
        <input type="text" name="items[__IDX__][description]" class="form-input" placeholder="Descrição" style="margin-bottom:8px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div>
                <label style="font-size:.75rem;color:#64748b;">Qtd</label>
                <input type="number" name="items[__IDX__][quantity]" class="form-input item-qty" step="0.01" min="0.01" value="1" oninput="updateTotal()">
            </div>
            <div>
                <label style="font-size:.75rem;color:#64748b;">Preço Unit. (R$)</label>
                <input type="number" name="items[__IDX__][unit_price]" class="form-input item-price" step="0.01" min="0" value="0" oninput="updateTotal()">
            </div>
        </div>
    </div>
</template>

<script>
let itemCount = {{ $proposal->items->count() }};

function addItem() {
    document.getElementById('emptyMsg')?.remove();
    const template = document.getElementById('itemTemplate').innerHTML;
    const html = template.replace(/__IDX__/g, itemCount++);
    const div = document.createElement('div');
    div.innerHTML = html;
    document.getElementById('itemsContainer').appendChild(div.firstElementChild);
    updateTotal();
}

function removeItem(btn) {
    btn.closest('.item-row').remove();
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        total += qty * price;
    });
    document.getElementById('totalDisplay').textContent = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}
</script>

</x-layouts.app>
