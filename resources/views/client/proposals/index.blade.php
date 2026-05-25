<x-layouts.app title="Minhas Propostas">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Propostas Comerciais</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Propostas enviadas pela Lymity para sua aprovação</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Status</th>
                <th>Valor Total</th>
                <th>Válida até</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($proposals as $proposal)
            <tr>
                <td style="font-weight:500;">{{ $proposal->title }}</td>
                <td><span class="badge badge-{{ $proposal->status_color }}">{{ $proposal->status_label }}</span></td>
                <td style="font-weight:600;">{{ $proposal->total_amount ? 'R$ '.number_format($proposal->total_amount,2,',','.') : '—' }}</td>
                <td style="font-size:.82rem;">{{ $proposal->valid_until?->format('d/m/Y') ?? '—' }}</td>
                <td><a href="{{ route('client.proposals.show', $proposal) }}" class="btn btn-secondary" style="padding:5px 12px;font-size:.8rem;">Ver detalhes</a></td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">Nenhuma proposta disponível.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $proposals->links() }}

</x-layouts.app>
