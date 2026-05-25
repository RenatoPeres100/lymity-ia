<x-layouts.app title="Editar Orçamento">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
    <a href="{{ route('admin.budgets.show', $budget) }}" style="color:#64748b;font-size:.875rem;">← Orçamento</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">Editar: {{ $budget->title }}</h2>
</div>

@if($errors->any())
<div class="alert alert-error"><ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.budgets.update', $budget) }}">
    @csrf @method('PUT')
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Dados</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Cliente *</label>
                        <select name="client_id" class="form-input" required>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ $budget->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título *</label>
                        <input type="text" name="title" class="form-input" value="{{ old('title', $budget->title) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-input" rows="3">{{ old('description', $budget->description) }}</textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Mês</label>
                            <input type="number" name="month" class="form-input" min="1" max="12" value="{{ old('month', $budget->month) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ano</label>
                            <input type="number" name="year" class="form-input" min="2024" max="2100" value="{{ old('year', $budget->year) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:1rem;">
                <div class="card-header">
                    <span class="card-title">Itens</span>
                    <button type="button" onclick="addItem()" class="btn btn-secondary" style="font-size:.8rem;padding:6px 12px;">+ Adicionar</button>
                </div>
                <div class="card-body" id="itemsContainer">
                    @foreach($budget->items as $i => $item)
                    <div class="item-row" style="border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:10px;">
                        <div style="display:grid;grid-template-columns:1fr auto;gap:8px;margin-bottom:8px;">
                            <input type="text" name="items[{{ $i }}][name]" class="form-input" value="{{ $item->name }}" required>
                            <button type="button" onclick="removeItem(this)" style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;border-radius:8px;padding:6px 10px;cursor:pointer;">✕</button>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                            <div>
                                <label style="font-size:.75rem;color:#64748b;">Categoria</label>
                                <select name="items[{{ $i }}][category]" class="form-input" required>
                                    @foreach(['media'=>'Mídia','production'=>'Produção','service'=>'Serviço','tool'=>'Ferramenta','other'=>'Outro'] as $cat=>$label)
                                    <option value="{{ $cat }}" {{ $item->category === $cat ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="font-size:.75rem;color:#64748b;">Valor (R$)</label>
                                <input type="number" name="items[{{ $i }}][amount]" class="form-input item-amount" step="0.01" min="0" value="{{ $item->amount }}" oninput="updateTotal()">
                            </div>
                        </div>
                        <input type="text" name="items[{{ $i }}][description]" class="form-input" value="{{ $item->description }}" placeholder="Descrição">
                    </div>
                    @endforeach
                </div>
                <div style="padding:14px 22px;border-top:1px solid #f1f5f9;text-align:right;">
                    <strong id="totalDisplay" style="font-size:1.1rem;color:#1e293b;">R$ {{ number_format($budget->total_amount ?? 0, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Salvar</button>
                <a href="{{ route('admin.budgets.show', $budget) }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<template id="itemTemplate">
    <div class="item-row" style="border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:10px;">
        <div style="display:grid;grid-template-columns:1fr auto;gap:8px;margin-bottom:8px;">
            <input type="text" name="items[__IDX__][name]" class="form-input" placeholder="Nome *" required>
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
        <input type="text" name="items[__IDX__][description]" class="form-input" placeholder="Descrição">
    </div>
</template>

<script>
let itemCount = {{ $budget->items->count() }};
function addItem() {
    const tpl = document.getElementById('itemTemplate').innerHTML.replace(/__IDX__/g, itemCount++);
    const div = document.createElement('div'); div.innerHTML = tpl;
    document.getElementById('itemsContainer').appendChild(div.firstElementChild);
    updateTotal();
}
function removeItem(btn) { btn.closest('.item-row').remove(); updateTotal(); }
function updateTotal() {
    let t = 0;
    document.querySelectorAll('.item-amount').forEach(i => t += parseFloat(i.value || 0));
    document.getElementById('totalDisplay').textContent = 'R$ ' + t.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}
</script>

</x-layouts.app>
