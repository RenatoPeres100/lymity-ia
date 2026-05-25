<x-layouts.app title="Aprovações de Orçamento">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Aprovações de Orçamento</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Alterações de orçamento aguardando aprovação</p>
    </div>
    <a href="{{ route('admin.ads.budget-approvals.create') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Solicitar Alteração</a>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Campanha</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Solicitado por</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Budget Anterior</th>
                <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;color:#64748b;font-weight:600;">Novo Budget</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Motivo</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($changes as $bc)
            <tr style="border-top:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;">
                    @if($bc->campaign)
                    <a href="{{ route('admin.ads.campaigns.show', $bc->campaign) }}" style="color:#6366f1;font-size:.875rem;font-weight:500;">{{ Str::limit($bc->campaign->name, 30) }}</a>
                    @if($bc->campaign->client)<div style="font-size:.75rem;color:#94a3b8;">{{ $bc->campaign->client->name }}</div>@endif
                    @else —
                    @endif
                </td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">{{ $bc->requester?->name ?? '—' }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;text-align:right;">R$ {{ number_format($bc->old_budget ?? 0,2,',','.') }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;font-weight:600;text-align:right;color:#6366f1;">R$ {{ number_format($bc->new_budget,2,',','.') }}</td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">{{ Str::limit($bc->reason, 40) }}</td>
                <td style="padding:.75rem 1rem;">
                    <span style="background:{{ $bc->status === 'applied' ? '#dcfce7' : ($bc->status === 'approved' ? '#dbeafe' : ($bc->status === 'pending_approval' ? '#fef9c3' : '#fee2e2')) }};color:{{ $bc->status === 'applied' ? '#166534' : ($bc->status === 'approved' ? '#1e40af' : ($bc->status === 'pending_approval' ? '#92400e' : '#991b1b')) }};padding:.25rem .5rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $bc->status_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;">
                    @if($bc->status === 'approved')
                    <form method="POST" action="{{ route('admin.ads.budget-approvals.apply', $bc) }}" style="display:inline;">
                        @csrf
                        <button type="submit" style="background:#16a34a;color:#fff;padding:.25rem .75rem;border-radius:.375rem;font-size:.75rem;font-weight:500;border:none;cursor:pointer;">Aplicar</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma alteração de orçamento.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">{{ $changes->links() }}</div>
</div>

</x-layouts.app>
