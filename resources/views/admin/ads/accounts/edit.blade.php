<x-layouts.app title="Editar Conta de Anúncio">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('admin.ads.accounts.index') }}" style="font-size:.875rem;color:#6366f1;">← Voltar</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Editar: {{ $adAccount->account_name }}</h2>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;max-width:600px;">
    <form method="POST" action="{{ route('admin.ads.accounts.update', $adAccount) }}">
        @csrf @method('PUT')
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Nome da Conta *</label>
                <input type="text" name="account_name" value="{{ old('account_name', $adAccount->account_name) }}" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Plataforma *</label>
                <select name="platform" required style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                    <option value="google_ads" {{ $adAccount->platform === 'google_ads' ? 'selected' : '' }}>Google Ads</option>
                    <option value="meta_ads" {{ $adAccount->platform === 'meta_ads' ? 'selected' : '' }}>Meta Ads</option>
                    <option value="linkedin_ads" {{ $adAccount->platform === 'linkedin_ads' ? 'selected' : '' }}>LinkedIn Ads</option>
                    <option value="tiktok_ads" {{ $adAccount->platform === 'tiktok_ads' ? 'selected' : '' }}>TikTok Ads</option>
                </select>
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Cliente</label>
                <select name="client_id" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                    <option value="">Nenhum (agência)</option>
                    @foreach($clients as $cl)
                    <option value="{{ $cl->id }}" {{ $adAccount->client_id == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">ID Externo da Conta</label>
                <input type="text" name="external_account_id" value="{{ old('external_account_id', $adAccount->external_account_id) }}" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
            </div>
            <div>
                <label style="font-size:.875rem;font-weight:500;color:#374151;display:block;margin-bottom:.375rem;">Status</label>
                <select name="status" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.875rem;">
                    <option value="active" {{ $adAccount->status === 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="paused" {{ $adAccount->status === 'paused' ? 'selected' : '' }}>Pausada</option>
                    <option value="disconnected" {{ $adAccount->status === 'disconnected' ? 'selected' : '' }}>Desconectada</option>
                </select>
            </div>
        </div>
        <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;">Salvar Alterações</button>
            <a href="{{ route('admin.ads.accounts.index') }}" style="background:#f1f5f9;color:#374151;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>

</x-layouts.app>
