<x-layouts.app title="Auditoria SEO">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <a href="{{ route('admin.seo.audits.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;">← Auditorias</a>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $seoAudit->title }}</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">{{ $seoAudit->website_url }} · {{ $seoAudit->client?->name ?? 'Agência' }}</p>
    </div>
    <div>
        @if($seoAudit->recommendations->isEmpty())
        <form method="POST" action="{{ route('admin.seo.audits.generate-mock', $seoAudit) }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;">Gerar Recomendações (Mock)</button>
        </form>
        @endif
    </div>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div>
        {{-- Recommendations --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1.5rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Recomendações ({{ $seoAudit->recommendations->count() }})</h3>
            @forelse($seoAudit->recommendations as $rec)
            @php
                $pc = match($rec->priority){
                    'critical'=>'#ef4444','high'=>'#f97316','medium'=>'#6366f1','low'=>'#64748b',default=>'#64748b'
                };
            @endphp
            <div style="border:1px solid #e2e8f0;border-radius:.5rem;padding:1rem;margin-bottom:.75rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                    <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $rec->title }}</div>
                    <span style="font-size:.6875rem;font-weight:600;color:{{ $pc }};background:{{ $pc }}1a;padding:.2rem .5rem;border-radius:9999px;">{{ $rec->priority_label }}</span>
                </div>
                @if($rec->description)
                <div style="font-size:.8125rem;color:#64748b;line-height:1.6;">{{ $rec->description }}</div>
                @endif
            </div>
            @empty
            <p style="font-size:.875rem;color:#94a3b8;text-align:center;padding:1rem 0;">Nenhuma recomendação gerada. Use o botão acima para gerar recomendações mock.</p>
            @endforelse
        </div>

        {{-- Summary --}}
        @if($seoAudit->summary)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Resumo Executivo</h3>
            <div style="font-size:.875rem;color:#374151;line-height:1.7;">{{ $seoAudit->summary }}</div>
        </div>
        @endif
    </div>

    <div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.875rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Score SEO</h3>
            @if($seoAudit->score !== null)
            <div style="text-align:center;padding:1rem 0;">
                <div style="font-size:3rem;font-weight:700;color:#1e293b;">{{ $seoAudit->score }}</div>
                <div style="font-size:.875rem;color:#64748b;">/100 — {{ $seoAudit->score_label }}</div>
            </div>
            @else
            <p style="font-size:.875rem;color:#94a3b8;text-align:center;">Score não definido</p>
            @endif

            <div style="margin-top:1rem;border-top:1px solid #f1f5f9;padding-top:1rem;">
                <div style="font-size:.75rem;color:#64748b;font-weight:500;">Status</div>
                <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $seoAudit->status_label }}</div>
                <div style="font-size:.75rem;color:#64748b;font-weight:500;margin-top:.75rem;">Recomendações</div>
                <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $seoAudit->recommendations->count() }} itens</div>
                <div style="font-size:.75rem;color:#64748b;font-weight:500;margin-top:.75rem;">Criada em</div>
                <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $seoAudit->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
