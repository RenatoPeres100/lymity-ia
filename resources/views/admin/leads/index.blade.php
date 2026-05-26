<x-layouts.app title="Leads">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Leads</h1>
        <p style="font-size:.85rem;color:#64748b;">Contatos e solicitações de diagnóstico recebidos.</p>
    </div>
    <div style="font-size:.8rem;color:#64748b;background:#f1f5f9;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;">
        Total: <strong style="color:#334155;">{{ $leads->total() }}</strong>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Nome / Empresa</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Contato</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Interesse</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Recebido</th>
                <th style="text-align:right;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <td style="padding:14px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#334155;">{{ $lead->name }}</div>
                    @if($lead->company)<div style="font-size:.75rem;color:#475569;">{{ $lead->company }}</div>@endif
                </td>
                <td style="padding:14px 16px;">
                    <div style="font-size:.8rem;color:#94a3b8;">{{ $lead->email }}</div>
                    @if($lead->phone)<div style="font-size:.78rem;color:#475569;">{{ $lead->phone }}</div>@endif
                </td>
                <td style="padding:14px 16px;font-size:.8rem;color:#94a3b8;">{{ $lead->service_interest ? str_replace('_',' ',ucfirst($lead->service_interest)) : '—' }}</td>
                <td style="padding:14px 16px;">
                    @php
                        $sc = ['new'=>['#0c2a3b','#22d3ee','Novo'],'contacted'=>['#1a2a05','#86efac','Contatado'],'qualified'=>['#052e16','#4ade80','Qualificado'],'converted'=>['#1a1a05','#fbbf24','Convertido'],'lost'=>['#2d0a0a','#f87171','Perdido']];
                        $s = $sc[$lead->status] ?? $sc['new'];
                    @endphp
                    <span style="background:{{ $s[0] }};color:{{ $s[1] }};font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;">{{ $s[2] }}</span>
                </td>
                <td style="padding:14px 16px;font-size:.78rem;color:#64748b;">{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:14px 16px;text-align:right;">
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('admin.leads.show', $lead) }}" style="font-size:.78rem;color:#6b8fff;font-weight:600;text-decoration:none;">Ver</a>
                        <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir este lead?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:.78rem;color:#f87171;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:48px 16px;text-align:center;color:#475569;font-size:.875rem;">Nenhum lead recebido ainda.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($leads->hasPages())
<div style="margin-top:20px;">{{ $leads->links() }}</div>
@endif

</x-layouts.app>
