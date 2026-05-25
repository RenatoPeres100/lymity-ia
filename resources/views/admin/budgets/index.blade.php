<x-layouts.app title="Orçamentos">

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Orçamentos Mensais</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Orçamentos de mídia, produção e serviços por cliente</p>
    </div>
    <a href="{{ route('admin.budgets.create') }}" class="btn btn-primary">+ Novo Orçamento</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    <select name="client_id" class="form-input" style="width:200px;">
        <option value="">Todos os clientes</option>
        @foreach($clients as $c)
        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
    </select>
    <select name="status" class="form-input" style="width:180px;">
        <option value="">Todos os status</option>
        @foreach(['draft'=>'Rascunho','pending_approval'=>'Aguardando Aprovação','approved'=>'Aprovado','rejected'=>'Rejeitado','active'=>'Ativo','archived'=>'Arquivado'] as $val=>$label)
        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary">Filtrar</button>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Título</th>
                <th>Mês/Ano</th>
                <th>Status</th>
                <th>Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($budgets as $budget)
            <tr>
                <td style="color:#94a3b8;font-size:.75rem;">{{ $budget->id }}</td>
                <td style="font-weight:500;">{{ $budget->client->name ?? '—' }}</td>
                <td>{{ $budget->title }}</td>
                <td style="font-size:.82rem;">{{ $budget->month_year_label }}</td>
                <td><span class="badge badge-{{ $budget->status_color }}">{{ $budget->status_label }}</span></td>
                <td style="font-weight:600;">{{ $budget->total_amount ? 'R$ '.number_format($budget->total_amount,2,',','.') : '—' }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <a href="{{ route('admin.budgets.show', $budget) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.78rem;">Ver</a>
                        <a href="{{ route('admin.budgets.edit', $budget) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.78rem;">Editar</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">Nenhum orçamento criado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $budgets->links() }}

</x-layouts.app>
