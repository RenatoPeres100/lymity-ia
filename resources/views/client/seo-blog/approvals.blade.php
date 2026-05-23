<x-layouts.app title="Aprovações de Blog">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('client.seo.blog.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;">← Meu Blog</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Aprovações de Blog</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Posts aguardando sua revisão e aprovação</p>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Título</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Data</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvals as $approval)
            @php
                $sc = match($approval->status){'approved'=>'#16a34a','rejected'=>'#ef4444','pending'=>'#ca8a04',default=>'#64748b'};
                $sl = match($approval->status){'approved'=>'Aprovado','rejected'=>'Rejeitado','pending'=>'Pendente',default=>$approval->status};
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;">
                    <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $approval->title }}</div>
                    @if($approval->description)
                    <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">{{ Str::limit($approval->description, 80) }}</div>
                    @endif
                </td>
                <td style="padding:.75rem 1rem;">
                    <span style="font-size:.6875rem;font-weight:600;color:{{ $sc }};background:{{ $sc }}1a;padding:.2rem .5rem;border-radius:9999px;">{{ $sl }}</span>
                </td>
                <td style="padding:.75rem 1rem;font-size:.75rem;color:#94a3b8;">{{ $approval->created_at->format('d/m/Y') }}</td>
                <td style="padding:.75rem 1rem;text-align:right;">
                    <a href="{{ route('client.approvals.show', $approval) }}" style="font-size:.75rem;color:#6366f1;">Ver aprovação</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma aprovação de blog encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($approvals->hasPages())
    <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">{{ $approvals->withQueryString()->links() }}</div>
    @endif
</div>

</x-layouts.app>
