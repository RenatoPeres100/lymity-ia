<x-layouts.app title="Novo Lead">

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#0f172a;margin:0 0 .25rem;">Novo Lead</h1>
        <p style="color:#64748b;font-size:.9rem;margin:0;">Cadastrar novo lead no pipeline de prospecção</p>
    </div>
    <a href="{{ route('admin.prospecting.leads.index') }}"
       style="background:#f1f5f9;color:#374151;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;border:1px solid #e2e8f0;">
        Cancelar
    </a>
</div>

<form action="{{ route('admin.prospecting.leads.store') }}" method="POST">
    @csrf
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Dados do Lead</h3>
        </div>
        <div style="padding:1.25rem;">
            @include('admin.prospecting.partials.lead-form', ['lead' => new \App\Models\ProspectLead(), 'owners' => $owners ?? []])
        </div>
        <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:.5rem;background:#f8fafc;">
            <a href="{{ route('admin.prospecting.leads.index') }}"
               style="background:#f1f5f9;color:#374151;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;border:1px solid #e2e8f0;">
                Cancelar
            </a>
            <button type="submit"
                    style="background:#6366f1;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.875rem;font-weight:500;">
                Criar Lead
            </button>
        </div>
    </div>
</form>

</x-layouts.app>
