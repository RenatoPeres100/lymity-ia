<x-layouts.app title="Cases">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Cases</h1>
        <p style="font-size:.85rem;color:#64748b;">Gerencie cases e resultados de clientes.</p>
    </div>
    <a href="{{ route('admin.cases.create') }}" class="btn-primary">+ Novo Case</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Título / Cliente</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Segmento</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:right;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cases as $case)
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <td style="padding:14px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#334155;margin-bottom:2px;">{{ $case->title }}</div>
                    @if($case->client_name)<div style="font-size:.75rem;color:#475569;">{{ $case->client_name }}</div>@endif
                </td>
                <td style="padding:14px 16px;font-size:.8rem;color:#94a3b8;">{{ $case->industry ?? '—' }}</td>
                <td style="padding:14px 16px;">
                    @if($case->published_at && $case->published_at->isPast())
                    <span style="background:#dcfce7;color:#166534;border:1px solid #86efac;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;">Publicado</span>
                    @else
                    <span style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;">Rascunho</span>
                    @endif
                </td>
                <td style="padding:14px 16px;text-align:right;">
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('admin.cases.edit', $case) }}" style="font-size:.78rem;color:#6b8fff;font-weight:600;text-decoration:none;">Editar</a>
                        <form action="{{ route('admin.cases.destroy', $case) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir este case?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:.78rem;color:#f87171;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding:48px 16px;text-align:center;color:#475569;font-size:.875rem;">Nenhum case encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
