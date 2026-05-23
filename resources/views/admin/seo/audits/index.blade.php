<x-layouts.app title="Auditorias SEO">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Auditorias SEO</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Diagnóstico técnico e recomendações SEO por site</p>
    </div>
    <a href="{{ route('admin.seo.audits.create') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Nova Auditoria</a>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Título</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">URL</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Cliente</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Score</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($audits as $audit)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;font-size:.875rem;font-weight:500;color:#1e293b;">{{ $audit->title }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ Str::limit($audit->website_url, 30) }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $audit->client?->name ?? 'Agência' }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;font-weight:600;color:#1e293b;">{{ $audit->score ?? '—' }}</td>
                <td style="padding:.75rem 1rem;">
                    <span style="font-size:.6875rem;font-weight:600;padding:.2rem .5rem;border-radius:9999px;background:#f1f5f9;color:#475569;">{{ $audit->status_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;text-align:right;">
                    <a href="{{ route('admin.seo.audits.show', $audit) }}" style="font-size:.75rem;color:#6366f1;">Ver</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma auditoria criada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($audits->hasPages())
    <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">{{ $audits->withQueryString()->links() }}</div>
    @endif
</div>

</x-layouts.app>
