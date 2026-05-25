<x-layouts.app title="Nova Campanha">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('admin.ads.campaigns.index') }}" style="font-size:.875rem;color:#6366f1;">← Voltar</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Nova Campanha de Anúncio</h2>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;max-width:700px;">
    @if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">
        <ul style="margin:0;padding-left:1.25rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.ads.campaigns.store') }}">
        @csrf
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Conta de Anúncio *</label>
                <select name="ad_account_id" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                    <option value="">Selecione a conta...</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('ad_account_id') == $acc->id ? 'selected' : '' }}>
                        {{ $acc->platform_label }} — {{ $acc->account_name }} {{ $acc->client ? '('.$acc->client->name.')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Cliente</label>
                    <select name="client_id" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                        <option value="">Nenhum (agência)</option>
                        @foreach($clients as $cl)
                        <option value="{{ $cl->id }}" {{ old('client_id') == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Objetivo *</label>
                    <select name="objective" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                        <option value="">Selecione...</option>
                        @foreach(['leads'=>'Leads','sales'=>'Vendas','traffic'=>'Tráfego','awareness'=>'Awareness','engagement'=>'Engajamento','app'=>'App','remarketing'=>'Remarketing'] as $v=>$l)
                        <option value="{{ $v }}" {{ old('objective') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Nome da Campanha *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: Campanha Leads — Verão 2026" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Orçamento Diário (R$)</label>
                    <input type="number" name="daily_budget" value="{{ old('daily_budget') }}" step="0.01" min="0" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Orçamento Total (R$)</label>
                    <input type="number" name="total_budget" value="{{ old('total_budget') }}" step="0.01" min="0" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Data de Início</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Data de Fim</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                </div>
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Resumo da Estratégia</label>
                <textarea name="strategy_summary" rows="4" placeholder="Descreva a estratégia da campanha..." style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">{{ old('strategy_summary') }}</textarea>
            </div>
            <div>
                <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;color:#374151;cursor:pointer;">
                    <input type="checkbox" name="requires_approval" value="1" {{ old('requires_approval', true) ? 'checked' : '' }}>
                    Exige aprovação antes de ativar
                </label>
            </div>
        </div>
        <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;">Criar Campanha</button>
            <a href="{{ route('admin.ads.campaigns.index') }}" style="background:#f1f5f9;color:#374151;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>

</x-layouts.app>
