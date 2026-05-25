<x-layouts.app title="{{ $clientContract->title }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="{{ route('admin.contracts.index') }}" style="color:#64748b;font-size:.875rem;">← Contratos</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">{{ $clientContract->title }}</h2>
    <span class="badge badge-{{ $clientContract->status_color }}">{{ $clientContract->status_label }}</span>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Detalhes</span>
            <a href="{{ route('admin.contracts.edit', $clientContract) }}" class="btn btn-secondary" style="padding:6px 12px;font-size:.8rem;">Editar</a>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Cliente</div>
                    <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $clientContract->client->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Assinado em</div>
                    <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $clientContract->signed_at?->format('d/m/Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Criado em</div>
                    <div style="color:#334155;margin-top:4px;">{{ $clientContract->created_at->format('d/m/Y') }}</div>
                </div>
                @if($clientContract->file_path)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Arquivo</div>
                    <div style="color:#4a6cf7;font-size:.82rem;margin-top:4px;word-break:break-all;">{{ $clientContract->file_path }}</div>
                </div>
                @endif
            </div>
            @if($clientContract->description)
            <div style="background:#f8fafc;border-radius:8px;padding:16px;">
                <div style="font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Escopo / Descrição</div>
                <p style="font-size:.875rem;color:#475569;white-space:pre-line;margin:0;">{{ $clientContract->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Ações</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">
                @if($clientContract->status === 'draft')
                <form method="POST" action="{{ route('admin.contracts.pending-signature', $clientContract) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Enviar para Assinatura</button>
                </form>
                @endif
                @if($clientContract->status === 'pending_signature')
                <form method="POST" action="{{ route('admin.contracts.mark-signed', $clientContract) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:#22c55e;">Marcar como Assinado</button>
                </form>
                @endif
                @if(!in_array($clientContract->status, ['canceled','archived']))
                <form method="POST" action="{{ route('admin.contracts.cancel', $clientContract) }}" onsubmit="return confirm('Cancelar contrato?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;color:#ef4444;border-color:#fecdd3;">Cancelar Contrato</button>
                </form>
                @endif
            </div>
        </div>

        <div class="card" style="margin-top:1rem;">
            <div class="card-header"><span class="card-title">Info</span></div>
            <div class="card-body">
                <div style="background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:12px;font-size:.82rem;color:#92400e;">
                    ⚠️ Contratos são registros internos. Assinatura digital não está disponível nesta fase.
                </div>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
