<x-layouts.app title="{{ $clientContract->title }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="{{ route('client.contracts.index') }}" style="color:#64748b;font-size:.875rem;">← Contratos</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">{{ $clientContract->title }}</h2>
    <span class="badge badge-{{ $clientContract->status_color }}">{{ $clientContract->status_label }}</span>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Detalhes do Contrato</span></div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Status</div>
                <span class="badge badge-{{ $clientContract->status_color }}" style="margin-top:4px;">{{ $clientContract->status_label }}</span>
            </div>
            <div>
                <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Assinado em</div>
                <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $clientContract->signed_at?->format('d/m/Y H:i') ?? 'Não assinado' }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Criado em</div>
                <div style="color:#334155;margin-top:4px;">{{ $clientContract->created_at->format('d/m/Y') }}</div>
            </div>
        </div>

        @if($clientContract->description)
        <div style="background:#f8fafc;border-radius:8px;padding:16px;">
            <div style="font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Escopo</div>
            <p style="font-size:.875rem;color:#475569;white-space:pre-line;margin:0;">{{ $clientContract->description }}</p>
        </div>
        @endif

        <div style="margin-top:1.5rem;background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:12px;font-size:.82rem;color:#92400e;">
            ⚠️ Este contrato é um registro interno. Para dúvidas sobre assinatura, entre em contato com nossa equipe.
        </div>
    </div>
</div>

</x-layouts.app>
