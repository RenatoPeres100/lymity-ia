<x-layouts.app title="Leads">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
@endif

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#0f172a;margin:0 0 .25rem;">Leads</h1>
        <p style="color:#64748b;font-size:.9rem;margin:0;">{{ $leads->total() }} lead(s) encontrado(s)</p>
    </div>
    @can('create', \App\Models\ProspectLead::class)
    <a href="{{ route('admin.prospecting.leads.create') }}"
       style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;display:flex;align-items:center;gap:.4rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Novo Lead
    </a>
    @endcan
</div>

{{-- Filters --}}
<form method="GET" style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;margin-bottom:1rem;">
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end;">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Buscar nome, empresa, segmento..."
            style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;width:100%;">
        <select name="stage_id" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;width:100%;">
            <option value="">Etapa</option>
            @foreach($stages as $stage)
            <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
            @endforeach
        </select>
        <select name="interest_level" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;width:100%;">
            <option value="">Interesse</option>
            <option value="hot"  {{ request('interest_level') === 'hot'  ? 'selected' : '' }}>Quente</option>
            <option value="warm" {{ request('interest_level') === 'warm' ? 'selected' : '' }}>Morno</option>
            <option value="cold" {{ request('interest_level') === 'cold' ? 'selected' : '' }}>Frio</option>
        </select>
        <select name="status" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;width:100%;">
            <option value="">Status</option>
            <option value="open"     {{ request('status') === 'open'     ? 'selected' : '' }}>Aberto</option>
            <option value="won"      {{ request('status') === 'won'      ? 'selected' : '' }}>Fechado</option>
            <option value="lost"     {{ request('status') === 'lost'     ? 'selected' : '' }}>Perdido</option>
            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Arquivado</option>
        </select>
        <div style="display:flex;gap:.4rem;">
            <button type="submit" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.875rem;white-space:nowrap;">Filtrar</button>
            <a href="{{ route('admin.prospecting.leads.index') }}" style="background:#f1f5f9;color:#374151;padding:.5rem .75rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;border:1px solid #e2e8f0;white-space:nowrap;">Limpar</a>
        </div>
    </div>
    <div style="margin-top:.5rem;">
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:#64748b;cursor:pointer;">
            <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }} onchange="this.form.submit()">
            Mostrar apenas follow-ups atrasados
        </label>
    </div>
</form>

{{-- Table --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <th style="padding:.75rem 1rem;width:40px;"><input type="checkbox" id="bulk-select-all"></th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Lead</th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Empresa</th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Etapa</th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Fit</th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Interesse</th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Follow-up</th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Status</th>
                    <th style="padding:.75rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr data-bulk-row style="border-bottom:1px solid #f1f5f9;transition:background .1s;cursor:pointer;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                    <td style="padding:.75rem 1rem;"><input type="checkbox" class="bulk-cb" value="{{ $lead->id }}"></td>
                    <td style="padding:.75rem 1rem;">
                        <div style="font-weight:500;color:#1e293b;">{{ $lead->name }}</div>
                        @if($lead->segment)<div style="font-size:.75rem;color:#94a3b8;">{{ $lead->segment }}</div>@endif
                    </td>
                    <td style="padding:.75rem 1rem;color:#64748b;">{{ $lead->company_name ?: '—' }}</td>
                    <td style="padding:.75rem 1rem;">
                        @if($lead->stage)
                        <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.8rem;">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $lead->stage->color ?? '#64748b' }};flex-shrink:0;"></span>
                            {{ $lead->stage->name }}
                        </span>
                        @else —
                        @endif
                    </td>
                    <td style="padding:.75rem 1rem;">
                        @if($lead->fit_score !== null)
                        <span style="font-weight:600;color:#6366f1;">{{ $lead->fit_score }}%</span>
                        @else <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="padding:.75rem 1rem;">
                        @if($lead->interest_level)
                        <span style="font-size:.75rem;padding:.2rem .5rem;border-radius:999px;font-weight:600;
                            {{ $lead->interest_level === 'hot' ? 'background:#fef2f2;color:#dc2626;' : ($lead->interest_level === 'warm' ? 'background:#fefce8;color:#a16207;' : 'background:#eff6ff;color:#2563eb;') }}">
                            {{ $lead->interest_label }}
                        </span>
                        @else <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="padding:.75rem 1rem;{{ $lead->isOverdue() ? 'color:#dc2626;font-weight:600;' : 'color:#64748b;' }}">
                        {{ $lead->next_follow_up_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td style="padding:.75rem 1rem;">
                        <span style="font-size:.75rem;padding:.2rem .5rem;border-radius:999px;font-weight:600;
                            {{ $lead->status === 'open' ? 'background:#eff6ff;color:#2563eb;' : ($lead->status === 'won' ? 'background:#f0fdf4;color:#16a34a;' : 'background:#f1f5f9;color:#64748b;') }}">
                            {{ $lead->status_label }}
                        </span>
                    </td>
                    <td style="padding:.75rem 1rem;text-align:right;">
                        <a href="{{ route('admin.prospecting.leads.show', $lead) }}" style="color:#6366f1;text-decoration:none;font-size:.875rem;font-weight:500;">Ver →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:3rem;text-align:center;color:#94a3b8;">Nenhum lead encontrado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">
        {{ $leads->links() }}
    </div>
    @endif
</div>

<x-bulk-actions :action="route('admin.prospecting.leads.bulk-delete')" />

</x-layouts.app>
