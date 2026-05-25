<x-layouts.app title="Editar Campanha">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('admin.ads.campaigns.show', $adCampaign) }}" style="font-size:.875rem;color:#6366f1;">← Voltar</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Editar: {{ $adCampaign->name }}</h2>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;max-width:700px;">
    <form method="POST" action="{{ route('admin.ads.campaigns.update', $adCampaign) }}">
        @csrf @method('PUT')
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Nome da Campanha *</label>
                <input type="text" name="name" value="{{ old('name', $adCampaign->name) }}" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Objetivo *</label>
                <select name="objective" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                    @foreach(['leads'=>'Leads','sales'=>'Vendas','traffic'=>'Tráfego','awareness'=>'Awareness','engagement'=>'Engajamento','app'=>'App','remarketing'=>'Remarketing'] as $v=>$l)
                    <option value="{{ $v }}" {{ $adCampaign->objective === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Orçamento Diário (R$)</label>
                    <input type="number" name="daily_budget" value="{{ old('daily_budget', $adCampaign->daily_budget) }}" step="0.01" min="0" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Orçamento Total (R$)</label>
                    <input type="number" name="total_budget" value="{{ old('total_budget', $adCampaign->total_budget) }}" step="0.01" min="0" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Data de Início</label>
                    <input type="date" name="start_date" value="{{ $adCampaign->start_date?->format('Y-m-d') }}" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Data de Fim</label>
                    <input type="date" name="end_date" value="{{ $adCampaign->end_date?->format('Y-m-d') }}" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Resumo da Estratégia</label>
                <textarea name="strategy_summary" rows="4" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">{{ old('strategy_summary', $adCampaign->strategy_summary) }}</textarea>
            </div>
        </div>
        <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;">Salvar Alterações</button>
            <a href="{{ route('admin.ads.campaigns.show', $adCampaign) }}" style="background:#f1f5f9;color:#374151;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>

</x-layouts.app>
