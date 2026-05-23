<x-layouts.app title="Novo Plano de Conteúdo SEO">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('admin.seo.content-plans.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;">← Planos</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Novo Plano de Conteúdo SEO</h2>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;max-width:640px;">
    <form method="POST" action="{{ route('admin.seo.content-plans.store') }}">
        @csrf

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Título *</label>
            <input type="text" name="title" value="{{ old('title') }}" required style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Mês *</label>
                <select name="month" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;">
                    <option value="1" {{ old('month') == 1 ? 'selected' : '' }}>Janeiro</option>
                    <option value="2" {{ old('month') == 2 ? 'selected' : '' }}>Fevereiro</option>
                    <option value="3" {{ old('month') == 3 ? 'selected' : '' }}>Março</option>
                    <option value="4" {{ old('month') == 4 ? 'selected' : '' }}>Abril</option>
                    <option value="5" {{ old('month', now()->month) == 5 ? 'selected' : '' }}>Maio</option>
                    <option value="6" {{ old('month') == 6 ? 'selected' : '' }}>Junho</option>
                    <option value="7" {{ old('month') == 7 ? 'selected' : '' }}>Julho</option>
                    <option value="8" {{ old('month') == 8 ? 'selected' : '' }}>Agosto</option>
                    <option value="9" {{ old('month') == 9 ? 'selected' : '' }}>Setembro</option>
                    <option value="10" {{ old('month') == 10 ? 'selected' : '' }}>Outubro</option>
                    <option value="11" {{ old('month') == 11 ? 'selected' : '' }}>Novembro</option>
                    <option value="12" {{ old('month') == 12 ? 'selected' : '' }}>Dezembro</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Ano *</label>
                <input type="number" name="year" value="{{ old('year', now()->year) }}" min="2024" max="2030" required style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Status *</label>
                <select name="status" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;">
                    <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="pending_approval" {{ old('status') == 'pending_approval' ? 'selected' : '' }}>Aguardando aprovação</option>
                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Aprovado</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Cliente</label>
            <select name="client_id" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;">
                <option value="">Agência / Geral</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:1.5rem;">
            <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Descrição / Estratégia</label>
            <textarea name="description" rows="5" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">{{ old('description') }}</textarea>
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;">Salvar</button>
            <a href="{{ route('admin.seo.content-plans.index') }}" style="color:#64748b;font-size:.875rem;padding:.5rem 1rem;border:1px solid #e2e8f0;border-radius:.5rem;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>

</x-layouts.app>
