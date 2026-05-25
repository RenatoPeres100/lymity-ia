<x-layouts.app title="Solicitar Alteração de Orçamento">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('admin.ads.budget-approvals.index') }}" style="font-size:.875rem;color:#6366f1;">← Voltar</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Solicitar Alteração de Orçamento</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">A alteração será submetida para aprovação antes de ser aplicada.</p>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;max-width:550px;">
    <form method="POST" action="{{ route('admin.ads.budget-approvals.store') }}">
        @csrf
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Campanha *</label>
                <select name="ad_campaign_id" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                    <option value="">Selecione a campanha...</option>
                    @foreach($campaigns as $c)
                    <option value="{{ $c->id }}" {{ old('ad_campaign_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} {{ $c->client ? '('.$c->client->name.')' : '' }} — R$ {{ number_format($c->daily_budget,2,',','.') }}/dia
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Novo Orçamento Diário (R$) *</label>
                <input type="number" name="new_budget" value="{{ old('new_budget') }}" min="1" step="0.01" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Motivo da Alteração *</label>
                <textarea name="reason" rows="3" required placeholder="Descreva o motivo..." style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">{{ old('reason') }}</textarea>
            </div>
        </div>
        <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;">Solicitar Aprovação</button>
            <a href="{{ route('admin.ads.budget-approvals.index') }}" style="background:#f1f5f9;color:#374151;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>

</x-layouts.app>
