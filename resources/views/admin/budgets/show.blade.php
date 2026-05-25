<x-layouts.app title="{{ $budget->title }}">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="{{ route('admin.budgets.index') }}" style="color:#64748b;font-size:.875rem;">← Orçamentos</a>
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;">{{ $budget->title }}</h2>
    <span class="badge badge-{{ $budget->status_color }}">{{ $budget->status_label }}</span>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <span class="card-title">Detalhes</span>
                <a href="{{ route('admin.budgets.edit', $budget) }}" class="btn btn-secondary" style="padding:6px 12px;font-size:.8rem;">Editar</a>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Cliente</div>
                        <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $budget->client->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Período</div>
                        <div style="font-weight:600;color:#1e293b;margin-top:4px;">{{ $budget->month_year_label }}</div>
                    </div>
                </div>
                @if($budget->description)
                <p style="font-size:.875rem;color:#334155;">{{ $budget->description }}</p>
                @endif
            </div>
        </div>

        {{-- Items by category --}}
        @php
        $categories = $budget->items->groupBy('category');
        $catLabels = ['media'=>'Mídia','production'=>'Produção','service'=>'Serviço','tool'=>'Ferramenta','other'=>'Outro'];
        $catColors = ['media'=>'blue','production'=>'purple','service'=>'green','tool'=>'orange','other'=>'gray'];
        @endphp

        <div class="card">
            <div class="card-header"><span class="card-title">Itens por Categoria</span></div>
            <div class="card-body" style="padding:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Item</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budget->items->sortBy('category') as $item)
                        <tr>
                            <td><span class="badge badge-{{ $catColors[$item->category] ?? 'gray' }}">{{ $catLabels[$item->category] ?? $item->category }}</span></td>
                            <td style="font-weight:500;">{{ $item->name }}</td>
                            <td style="font-size:.82rem;color:#64748b;">{{ $item->description ?? '—' }}</td>
                            <td style="font-weight:600;">R$ {{ number_format($item->amount, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:1.5rem;color:#94a3b8;">Nenhum item.</td></tr>
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
        <div class="card">
            <div class="card-header"><span class="card-title">Ações</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">
                @if($budget->status === 'draft')
                <form method="POST" action="{{ route('admin.budgets.send-approval', $budget) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Enviar para Aprovação</button>
                </form>
                @endif
                <a href="{{ route('admin.budgets.edit', $budget) }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Editar</a>
                <form method="POST" action="{{ route('admin.budgets.destroy', $budget) }}" onsubmit="return confirm('Remover orçamento?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;color:#ef4444;border-color:#fecdd3;">Excluir</button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top:1rem;">
            <div class="card-header"><span class="card-title">Por Categoria</span></div>
            <div class="card-body">
                @foreach($categories as $cat => $items)
                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:.82rem;color:#475569;">{{ $catLabels[$cat] ?? $cat }}</span>
                    <span style="font-weight:600;font-size:.875rem;">R$ {{ number_format($items->sum('amount'), 2, ',', '.') }}</span>
                </div>
                @endforeach
                <div style="display:flex;justify-content:space-between;padding-top:10px;margin-top:4px;">
                    <span style="font-weight:600;color:#1e293b;">Total</span>
                    <span style="font-weight:700;font-size:1.05rem;color:#1e293b;">R$ {{ number_format($budget->total_amount ?? 0, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
