<x-layouts.app title="{{ $proposal->title }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="{{ route('admin.proposals.index') }}" style="color:#64748b;font-size:.875rem;">← Propostas</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">{{ $proposal->title }}</h2>
    <span class="badge badge-{{ $proposal->status_color }}">{{ $proposal->status_label }}</span>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

    <div>
        {{-- Proposal details --}}
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <span class="card-title">Detalhes</span>
                <a href="{{ route('admin.proposals.edit', $proposal) }}" class="btn btn-secondary" style="padding:6px 12px;font-size:.8rem;">Editar</a>
            </div>
            <div class="card-body">
                @if($proposal->description)
                <p style="font-size:.9rem;color:#334155;margin-bottom:1rem;">{{ $proposal->description }}</p>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Cliente</div>
                        <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $proposal->client->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Válida até</div>
                        <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $proposal->valid_until?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Criado por</div>
                        <div style="color:#334155;margin-top:4px;">{{ $proposal->creator->name ?? '—' }}</div>
                    </div>
                    @if($proposal->approved_by)
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Aprovado por</div>
                        <div style="color:#334155;margin-top:4px;">{{ $proposal->approver->name ?? '—' }}</div>
                    </div>
                    @endif
                </div>

                @if($proposal->terms)
                <div style="background:#f8fafc;border-radius:8px;padding:14px;">
                    <div style="font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Termos e Condições</div>
                    <p style="font-size:.875rem;color:#475569;white-space:pre-line;margin:0;">{{ $proposal->terms }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><span class="card-title">Itens da Proposta</span></div>
            <div class="card-body" style="padding:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qtd</th>
                            <th>Preço Unit.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proposal->items as $item)
                        <tr>
                            <td>
                                <div style="font-weight:500;">{{ $item->name }}</div>
                                @if($item->description)<div style="font-size:.78rem;color:#64748b;">{{ $item->description }}</div>@endif
                            </td>
                            <td>{{ number_format($item->quantity, 0, ',', '.') }}x</td>
                            <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td style="font-weight:600;">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:1rem;">Nenhum item.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div style="padding:14px 22px;text-align:right;border-top:1px solid #f1f5f9;">
                    <strong style="font-size:1.1rem;color:#1e293b;">Total: R$ {{ number_format($proposal->total_amount ?? 0, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        {{-- Approval history --}}
        @if($proposal->approvalRequests->count())
        <div class="card">
            <div class="card-header"><span class="card-title">Histórico de Aprovações</span></div>
            <div class="card-body">
                @foreach($proposal->approvalRequests as $ar)
                <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span class="badge badge-{{ $ar->status === 'approved' ? 'green' : ($ar->status === 'rejected' ? 'red' : ($ar->status === 'pending' ? 'orange' : 'gray')) }}">{{ $ar->status }}</span>
                        <span style="font-size:.8rem;color:#64748b;">{{ $ar->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="font-size:.875rem;color:#334155;">{{ $ar->title }}</div>
                    @if($ar->actions->count())
                    <div style="margin-top:6px;">
                        @foreach($ar->actions->take(3) as $action)
                        <div style="font-size:.75rem;color:#94a3b8;">• {{ $action->action }} — {{ $action->notes }}</div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Actions sidebar --}}
    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Ações</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">

                @if($proposal->status === 'draft')
                <form method="POST" action="{{ route('admin.proposals.send-approval', $proposal) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Enviar para Aprovação
                    </button>
                </form>
                @endif

                @if($proposal->status === 'approved')
                <form method="POST" action="{{ route('admin.proposals.send-client', $proposal) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:#22c55e;">
                        Enviar ao Cliente
                    </button>
                </form>
                @endif

                <a href="{{ route('admin.proposals.edit', $proposal) }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Editar Proposta</a>

                <a href="{{ route('admin.approvals.index') }}?approvable_type=Proposal" class="btn btn-secondary" style="width:100%;justify-content:center;font-size:.8rem;">Ver Aprovações</a>

                <form method="POST" action="{{ route('admin.proposals.destroy', $proposal) }}" onsubmit="return confirm('Remover proposta?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;color:#ef4444;border-color:#fecdd3;">Excluir</button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top:1rem;">
            <div class="card-header"><span class="card-title">Resumo</span></div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="font-size:.82rem;color:#64748b;">Itens</span>
                        <span style="font-weight:600;">{{ $proposal->items->count() }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="font-size:.82rem;color:#64748b;">Valor Total</span>
                        <span style="font-weight:700;color:#1e293b;">R$ {{ number_format($proposal->total_amount ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="font-size:.82rem;color:#64748b;">Criada em</span>
                        <span style="font-size:.82rem;">{{ $proposal->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

</x-layouts.app>
