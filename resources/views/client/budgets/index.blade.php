<x-layouts.app title="Orçamentos">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Orçamentos Mensais</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Orçamentos de mídia e operação do seu projeto</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Período</th>
                <th>Status</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($budgets as $budget)
            <tr>
                <td style="font-weight:500;">{{ $budget->title }}</td>
                <td style="font-size:.82rem;">{{ $budget->month_year_label }}</td>
                <td><span class="badge badge-{{ $budget->status_color }}">{{ $budget->status_label }}</span></td>
                <td style="font-weight:600;">R$ {{ number_format($budget->total_amount ?? 0, 2, ',', '.') }}</td>
                <td><a href="{{ route('client.budgets.show', $budget) }}" class="btn btn-secondary" style="padding:5px 12px;font-size:.8rem;">Ver</a></td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">Nenhum orçamento disponível.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $budgets->links() }}

</x-layouts.app>
