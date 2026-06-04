<x-layouts.app title="Pipeline de Prospecção">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
@endif

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#0f172a;margin:0 0 .25rem;">Pipeline Kanban</h1>
        <p style="color:#64748b;font-size:.9rem;margin:0;">Visualização das etapas de prospecção</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        @can('create', \App\Models\ProspectLead::class)
        <a href="{{ route('admin.prospecting.leads.create') }}"
           style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;display:flex;align-items:center;gap:.4rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Novo Lead
        </a>
        @endcan
        <a href="{{ route('admin.prospecting.leads.index') }}"
           style="background:#f1f5f9;color:#374151;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;border:1px solid #e2e8f0;">
            Ver Lista
        </a>
    </div>
</div>

@if(empty($kanban))
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:3rem;text-align:center;">
    <p style="color:#64748b;margin-bottom:.5rem;">Nenhum pipeline configurado.</p>
    <p style="font-size:.875rem;color:#94a3b8;">Execute: <code style="background:#f1f5f9;padding:.1rem .4rem;border-radius:.25rem;">php artisan prospecting:install-default-pipeline</code></p>
</div>
@else
<div style="overflow-x:auto;padding-bottom:1rem;">
    <div style="display:flex;gap:1rem;min-width:max-content;">
        @foreach($kanban as $col)
        @php $stage = $col['stage']; $leads = $col['leads']; @endphp
        <div style="width:240px;flex-shrink:0;">
            {{-- Column header --}}
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;padding:0 .25rem;">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ $stage->color ?? '#64748b' }};flex-shrink:0;"></div>
                <span style="font-size:.8rem;font-weight:600;color:#374151;flex:1;">{{ $stage->name }}</span>
                <span style="font-size:.7rem;background:#f1f5f9;color:#64748b;padding:.1rem .4rem;border-radius:999px;font-weight:600;">{{ $col['count'] }}</span>
            </div>

            {{-- Cards --}}
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @forelse($leads as $lead)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.04);">
                    <div style="font-weight:600;font-size:.875rem;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->name }}</div>
                    @if($lead->company_name)
                    <div style="font-size:.75rem;color:#64748b;margin-top:.15rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->company_name }}</div>
                    @endif
                    @if($lead->segment)
                    <div style="font-size:.7rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->segment }}</div>
                    @endif

                    <div style="display:flex;gap:.3rem;margin-top:.5rem;flex-wrap:wrap;">
                        @if($lead->fit_score !== null)
                        <span style="font-size:.7rem;background:#eef2ff;color:#4f46e5;padding:.15rem .4rem;border-radius:999px;font-weight:600;">{{ $lead->fit_score }}%</span>
                        @endif
                        @if($lead->interest_level)
                        <span style="font-size:.7rem;padding:.15rem .4rem;border-radius:999px;font-weight:600;
                            {{ $lead->interest_level === 'hot' ? 'background:#fef2f2;color:#dc2626;' : ($lead->interest_level === 'warm' ? 'background:#fefce8;color:#a16207;' : 'background:#eff6ff;color:#2563eb;') }}">
                            {{ $lead->interest_label }}
                        </span>
                        @endif
                    </div>

                    @if($lead->next_follow_up_at)
                    <div style="font-size:.7rem;margin-top:.4rem;{{ $lead->isOverdue() ? 'color:#dc2626;font-weight:600;' : 'color:#94a3b8;' }}">
                        📅 {{ $lead->next_follow_up_at->format('d/m') }}{{ $lead->isOverdue() ? ' (atrasado)' : '' }}
                    </div>
                    @endif

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.6rem;padding-top:.5rem;border-top:1px solid #f1f5f9;">
                        <span style="font-size:.7rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:120px;">{{ $lead->owner?->name ?? '' }}</span>
                        <a href="{{ route('admin.prospecting.leads.show', $lead) }}"
                           style="font-size:.75rem;color:#6366f1;text-decoration:none;font-weight:500;flex-shrink:0;">Ver →</a>
                    </div>

                    @can('moveStage', $lead)
                    <form action="{{ route('admin.prospecting.leads.move-stage', $lead) }}" method="POST" style="margin-top:.5rem;">
                        @csrf @method('PATCH')
                        <select name="prospect_stage_id" onchange="this.form.submit()"
                                style="width:100%;font-size:.7rem;border:1px solid #e2e8f0;border-radius:.375rem;padding:.25rem .4rem;background:#f8fafc;color:#475569;">
                            <option value="">Mover para...</option>
                            @foreach($col['stage']->pipeline->stages as $s)
                            <option value="{{ $s->id }}" {{ $s->id === $lead->prospect_stage_id ? 'disabled' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endcan
                </div>
                @empty
                <div style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:.75rem;padding:1rem;text-align:center;font-size:.75rem;color:#94a3b8;">
                    Sem leads
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

</x-layouts.app>
