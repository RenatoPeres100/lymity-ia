<x-layouts.app title="Novo Cluster SEO">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('admin.seo.clusters.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;">← Clusters</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Novo Cluster SEO</h2>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;max-width:640px;">
    <form method="POST" action="{{ route('admin.seo.clusters.store') }}">
        @csrf

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Nome do Cluster *</label>
            <input type="text" name="name" value="{{ old('name') }}" required style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Palavra-chave principal *</label>
            <input type="text" name="main_keyword" value="{{ old('main_keyword') }}" required style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Cliente</label>
                <select name="client_id" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;">
                    <option value="">Agência / Geral</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Status *</label>
                <select name="status" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;">
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>Pausado</option>
                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:1.5rem;">
            <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Descrição</label>
            <textarea name="description" rows="3" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">{{ old('description') }}</textarea>
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;">Salvar</button>
            <a href="{{ route('admin.seo.clusters.index') }}" style="color:#64748b;font-size:.875rem;padding:.5rem 1rem;border:1px solid #e2e8f0;border-radius:.5rem;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>

</x-layouts.app>
