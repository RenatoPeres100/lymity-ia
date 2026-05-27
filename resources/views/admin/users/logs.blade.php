<x-layouts.app title="Logs — {{ $user->name }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
    <a href="{{ route('admin.users.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Usuários</a>
    <span style="color:#cbd5e1;">/</span>
    <a href="{{ route('admin.users.show', $user) }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">{{ $user->name }}</a>
    <span style="color:#cbd5e1;">/</span>
    <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;">Logs de atividade</h1>
</div>

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ação</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Descrição</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">IP</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Data</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:12px 16px;">
                    <code style="background:#f8fafc;border:1px solid #e2e8f0;padding:2px 7px;border-radius:4px;font-size:.72rem;color:#475569;">{{ $log->action }}</code>
                </td>
                <td style="padding:12px 16px;font-size:.82rem;color:#334155;">{{ $log->description }}</td>
                <td style="padding:12px 16px;font-size:.78rem;color:#94a3b8;font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
                <td style="padding:12px 16px;font-size:.78rem;color:#94a3b8;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="padding:48px;text-align:center;color:#94a3b8;font-size:.875rem;">Nenhum log encontrado para este usuário.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
    <div style="padding:12px 16px;border-top:1px solid #f1f5f9;font-size:.8rem;color:#64748b;">
        {{ $logs->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
