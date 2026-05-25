<x-layouts.app title="Contas de Anúncio">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Contas de Anúncio</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Gerencie contas Google Ads, Meta Ads e outras plataformas</p>
    </div>
    <a href="{{ route('admin.ads.accounts.create') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Nova Conta</a>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="background:#fef9c3;border:1px solid #fde047;color:#92400e;padding:.625rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.8125rem;">
    🔒 Tokens de acesso não são exibidos por segurança. Nenhuma ação real é executada nas plataformas de anúncio nesta fase.
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Conta</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Plataforma</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Cliente / Agência</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Tokens</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;color:#64748b;font-weight:600;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $acc)
            <tr style="border-top:1px solid #f1f5f9;@if($loop->even)background:#fafafa;@endif">
                <td style="padding:.75rem 1rem;font-size:.875rem;font-weight:500;color:#1e293b;">
                    {{ $acc->account_name }}
                    @if($acc->external_account_id)<div style="font-size:.75rem;color:#94a3b8;">ID: {{ $acc->external_account_id }}</div>@endif
                </td>
                <td style="padding:.75rem 1rem;">
                    <span style="background:#dbeafe;color:#1e40af;padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">{{ $acc->platform_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#374151;">
                    {{ $acc->client?->name ?? ($acc->company?->name ?? '—') }}
                </td>
                <td style="padding:.75rem 1rem;">
                    <span style="background:{{ $acc->status === 'active' ? '#dcfce7' : ($acc->status === 'paused' ? '#fef9c3' : '#fee2e2') }};color:{{ $acc->status === 'active' ? '#166534' : ($acc->status === 'paused' ? '#92400e' : '#991b1b') }};padding:.25rem .5rem;border-radius:.25rem;font-size:.75rem;font-weight:500;">
                        {{ match($acc->status) { 'active'=>'Ativa', 'paused'=>'Pausada', 'disconnected'=>'Desconectada', default=>$acc->status } }}
                    </span>
                </td>
                <td style="padding:.75rem 1rem;font-size:.8125rem;color:#94a3b8;">
                    <span style="font-style:italic;">••••••••</span>
                </td>
                <td style="padding:.75rem 1rem;">
                    <a href="{{ route('admin.ads.accounts.edit', $acc) }}" style="color:#6366f1;font-size:.8125rem;font-weight:500;">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma conta cadastrada.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">{{ $accounts->links() }}</div>
</div>

</x-layouts.app>
