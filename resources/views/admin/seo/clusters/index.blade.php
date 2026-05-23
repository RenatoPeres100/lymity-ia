<x-layouts.app title="Clusters SEO">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Clusters SEO</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Agrupe palavras-chave temáticas para estratégia de conteúdo</p>
    </div>
    <a href="{{ route('admin.seo.clusters.create') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Novo Cluster</a>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Nome</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Palavra-chave principal</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Cliente</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($clusters as $cluster)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;font-size:.875rem;font-weight:500;color:#1e293b;">{{ $cluster->name }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $cluster->main_keyword }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $cluster->client?->name ?? '—' }}</td>
                <td style="padding:.75rem 1rem;">
                    <span style="font-size:.6875rem;font-weight:600;padding:.2rem .5rem;border-radius:9999px;background:#f1f5f9;color:#475569;">{{ $cluster->status_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;text-align:right;">
                    <a href="{{ route('admin.seo.clusters.edit', $cluster) }}" style="font-size:.75rem;color:#6366f1;margin-right:.75rem;">Editar</a>
                    <form method="POST" action="{{ route('admin.seo.clusters.destroy', $cluster) }}" style="display:inline;" onsubmit="return confirm('Remover?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:.75rem;color:#ef4444;background:none;border:none;cursor:pointer;">Remover</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhum cluster cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($clusters->hasPages())
    <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">{{ $clusters->withQueryString()->links() }}</div>
    @endif
</div>

</x-layouts.app>
