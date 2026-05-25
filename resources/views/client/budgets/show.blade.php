<x-layouts.app title="{{ $budget->title }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="{{ route('client.budgets.index') }}" style="color:#64748b;font-size:.875rem;">← Orçamentos</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">{{ $budget->title }}</h2>
    <span class="badge badge-{{ $budget->status_color }}">{{ $budget->status_label }}</span>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><span class="card-title">Período: {{ $budget->month_year_label }}</span></div>
            <div class="card-body" style="padding:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Item</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $catLabels = ['media'=>'Mídia','production'=>'Produção','service'=>'Serviço','tool'=>'Ferramenta','other'=>'Outro']; $catColors = ['media'=>'blue','production'=>'purple','service'=>'green','tool'=>'orange','other'=>'gray']; @endphp
                        @forelse($budget->items->sortBy('category') as $item)
                        <tr>
                            <td><span class="badge badge-{{ $catColors[$item->category] ?? 'gray' }}">{{ $catLabels[$item->category] ?? $item->category }}</span></td>
                            <td>
                                <div style="font-weight:500;">{{ $item->name }}</div>
                                @if($item->description)<div style="font-size:.78rem;color:#64748b;">{{ $item->description }}</div>@endif
                            </td>
                            <td style="font-weight:600;">R$ {{ number_format($item->amount, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;padding:1rem;color:#94a3b8;">Sem itens.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div style="padding:14px 22px;text-align:right;border-top:1px solid #f1f5f9;">
                    <strong style="font-size:1.1rem;color:#1e293b;">Total: R$ {{ number_format($budget->total_amount ?? 0, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div>
        @if($budget->status === 'approved')
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header"><span class="card-title">Sua Resposta</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">
                <form method="POST" action="{{ route('client.budgets.approve', $budget) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:#22c55e;">✓ Aprovar</button>
                </form>
                <form method="POST" action="{{ route('client.budgets.reject', $budget) }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;color:#ef4444;border-color:#fecdd3;" onclick="return confirm('Rejeitar este orçamento?')">✗ Rejeitar</button>
                </form>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><span class="card-title">Comentar</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('client.budgets.comment', $budget) }}">
                    @csrf
                    <div class="form-group">
                        <textarea name="comment" class="form-input" rows="3" placeholder="Comentário ou dúvida..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
