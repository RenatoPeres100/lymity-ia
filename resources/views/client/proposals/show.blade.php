<x-layouts.app title="{{ $proposal->title }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="{{ route('client.proposals.index') }}" style="color:#64748b;font-size:.875rem;">← Propostas</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">{{ $proposal->title }}</h2>
    <span class="badge badge-{{ $proposal->status_color }}">{{ $proposal->status_label }}</span>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><span class="card-title">Proposta Comercial</span></div>
            <div class="card-body">
                @if($proposal->description)
                <p style="font-size:.9rem;color:#334155;margin-bottom:1.5rem;">{{ $proposal->description }}</p>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Válida até</div>
                        <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $proposal->valid_until?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Valor Total</div>
                        <div style="font-weight:700;font-size:1.2rem;color:#1e293b;margin-top:4px;">R$ {{ number_format($proposal->total_amount ?? 0, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><span class="card-title">Itens</span></div>
            <div class="card-body" style="padding:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Serviço / Item</th>
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
                            <td>{{ $item->quantity }}x</td>
                            <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td style="font-weight:600;">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:1rem;color:#94a3b8;">Sem itens.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div style="padding:14px 22px;text-align:right;border-top:1px solid #f1f5f9;">
                    <strong style="font-size:1.15rem;color:#1e293b;">Total: R$ {{ number_format($proposal->total_amount ?? 0, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        @if($proposal->terms)
        <div class="card">
            <div class="card-header"><span class="card-title">Termos e Condições</span></div>
            <div class="card-body">
                <p style="font-size:.875rem;color:#475569;white-space:pre-line;margin:0;">{{ $proposal->terms }}</p>
            </div>
        </div>
        @endif
    </div>

    <div>
        @if(in_array($proposal->status, ['sent', 'approved']))
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><span class="card-title">Sua Resposta</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem;">
                <form method="POST" action="{{ route('client.proposals.accept', $proposal) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Comentário (opcional)</label>
                        <textarea name="notes" class="form-input" rows="2" placeholder="Observações ao aceitar..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:#22c55e;">✓ Aceitar Proposta</button>
                </form>
                <form method="POST" action="{{ route('client.proposals.reject', $proposal) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Motivo da rejeição (opcional)</label>
                        <textarea name="notes" class="form-input" rows="2" placeholder="Explique o motivo..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;color:#ef4444;border-color:#fecdd3;" onclick="return confirm('Rejeitar esta proposta?')">✗ Rejeitar</button>
                </form>
            </div>
        </div>
        @elseif($proposal->status === 'accepted')
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body" style="text-align:center;padding:1.5rem;">
                <div style="font-size:2rem;margin-bottom:8px;">✅</div>
                <div style="font-weight:600;color:#15803d;">Proposta Aceita</div>
                <div style="font-size:.82rem;color:#64748b;margin-top:4px;">Nossa equipe entrará em contato.</div>
            </div>
        </div>
        @elseif($proposal->status === 'rejected')
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body" style="text-align:center;padding:1.5rem;">
                <div style="font-size:2rem;margin-bottom:8px;">❌</div>
                <div style="font-weight:600;color:#be123c;">Proposta Rejeitada</div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><span class="card-title">Comentar</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('client.proposals.comment', $proposal) }}">
                    @csrf
                    <div class="form-group">
                        <textarea name="comment" class="form-input" rows="3" placeholder="Deixe um comentário ou dúvida..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;">Enviar Comentário</button>
                </form>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
