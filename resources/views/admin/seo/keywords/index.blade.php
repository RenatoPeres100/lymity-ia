<x-layouts.app title="Palavras-chave SEO">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Palavras-chave SEO</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Gerencie palavras-chave por cliente e status</p>
    </div>
    <a href="{{ route('admin.seo.keywords.create') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Nova Palavra-chave</a>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

{{-- Filters --}}
<form method="GET" style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;">
    <select name="client_id" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .75rem;font-size:.875rem;color:#374151;">
        <option value="">Todos os clientes</option>
        @foreach($clients as $client)
        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
        @endforeach
    </select>
    <select name="status" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .75rem;font-size:.875rem;color:#374151;">
        <option value="">Todos os status</option>
        <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planejado</option>
        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Em andamento</option>
        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Usado</option>
        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
    </select>
    <button type="submit" style="background:#6366f1;color:#fff;padding:.4rem 1rem;border-radius:.5rem;font-size:.875rem;border:none;cursor:pointer;">Filtrar</button>
    <a href="{{ route('admin.seo.keywords.index') }}" style="color:#64748b;font-size:.875rem;padding:.4rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;text-decoration:none;">Limpar</a>
</form>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Palavra-chave</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Cliente</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Intenção</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Prioridade</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Volume</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($keywords as $kw)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;font-size:.875rem;font-weight:500;color:#1e293b;">{{ $kw->keyword }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $kw->client?->name ?? '—' }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $kw->intent_label }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $kw->priority_label }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $kw->volume ? number_format($kw->volume) : '—' }}</td>
                <td style="padding:.75rem 1rem;">
                    <span style="font-size:.6875rem;font-weight:600;padding:.2rem .5rem;border-radius:9999px;background:#f1f5f9;color:#475569;">{{ $kw->status_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;text-align:right;">
                    <a href="{{ route('admin.seo.keywords.edit', $kw) }}" style="font-size:.75rem;color:#6366f1;margin-right:.75rem;">Editar</a>
                    <form method="POST" action="{{ route('admin.seo.keywords.destroy', $kw) }}" style="display:inline;" onsubmit="return confirm('Remover?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:.75rem;color:#ef4444;background:none;border:none;cursor:pointer;">Remover</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma palavra-chave cadastrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($keywords->hasPages())
    <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">{{ $keywords->withQueryString()->links() }}</div>
    @endif
</div>

</x-layouts.app>
