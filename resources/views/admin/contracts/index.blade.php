<x-layouts.app title="Contratos">

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Contratos</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Registros de contratos internos dos clientes</p>
    </div>
    <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">+ Novo Contrato</a>
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
        @foreach(['draft'=>'Rascunho','pending_signature'=>'Aguardando Assinatura','signed'=>'Assinado','canceled'=>'Cancelado','archived'=>'Arquivado'] as $val=>$label)
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
                <th>Status</th>
                <th>Assinado em</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $contract)
            <tr>
                <td style="color:#94a3b8;font-size:.75rem;">{{ $contract->id }}</td>
                <td style="font-weight:500;">{{ $contract->client->name ?? '—' }}</td>
                <td>{{ $contract->title }}</td>
                <td><span class="badge badge-{{ $contract->status_color }}">{{ $contract->status_label }}</span></td>
                <td style="font-size:.82rem;">{{ $contract->signed_at?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:.82rem;color:#64748b;">{{ $contract->created_at->format('d/m/Y') }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.78rem;">Ver</a>
                        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.78rem;">Editar</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">Nenhum contrato criado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $contracts->links() }}

</x-layouts.app>
