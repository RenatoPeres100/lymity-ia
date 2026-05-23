<x-layouts.app title="Plano de Conteúdo SEO">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <a href="{{ route('admin.seo.content-plans.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;">← Planos</a>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $seoContentPlan->title }}</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">{{ $seoContentPlan->month_name }} / {{ $seoContentPlan->year }} · {{ $seoContentPlan->client?->name ?? 'Agência' }}</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.seo.content-plans.edit', $seoContentPlan) }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">Editar</a>
    </div>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Estratégia e Descrição</h3>
        @if($seoContentPlan->description)
        <div style="font-size:.875rem;color:#374151;line-height:1.7;white-space:pre-wrap;">{{ $seoContentPlan->description }}</div>
        @else
        <p style="font-size:.875rem;color:#94a3b8;">Nenhuma descrição cadastrada.</p>
        @endif
    </div>

    <div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.875rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Detalhes</h3>
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Status</div>
                    <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $seoContentPlan->status_label }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Período</div>
                    <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $seoContentPlan->month_name }} / {{ $seoContentPlan->year }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Cliente</div>
                    <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $seoContentPlan->client?->name ?? 'Agência' }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Criado em</div>
                    <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $seoContentPlan->created_at->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
