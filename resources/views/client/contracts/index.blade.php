<x-layouts.app title="Contratos">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Contratos</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Contratos do seu projeto com a Lymity AI Agency</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Status</th>
                <th>Assinado em</th>
                <th>Data</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $contract)
            <tr>
                <td style="font-weight:500;">{{ $contract->title }}</td>
                <td><span class="badge badge-{{ $contract->status_color }}">{{ $contract->status_label }}</span></td>
                <td style="font-size:.82rem;">{{ $contract->signed_at?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:.82rem;color:#64748b;">{{ $contract->created_at->format('d/m/Y') }}</td>
                <td><a href="{{ route('client.contracts.show', $contract) }}" class="btn btn-secondary" style="padding:5px 12px;font-size:.8rem;">Ver</a></td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">Nenhum contrato disponível.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $contracts->links() }}

</x-layouts.app>
