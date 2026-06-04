<x-layouts.app title="Pipeline de Prospecção">

{{-- SortableJS via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

@if(session('success'))
<div id="flash-success" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
    {{ session('success') }}
</div>
@endif

{{-- Toast de feedback do drag --}}
<div id="dnd-toast" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;padding:.75rem 1.25rem;border-radius:.75rem;font-size:.875rem;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);transition:opacity .3s;"></div>

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#0f172a;margin:0 0 .2rem;">Pipeline Kanban</h1>
        <p style="color:#64748b;font-size:.875rem;margin:0;">Arraste os cards entre as colunas para mover etapas</p>
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

{{-- Kanban Board --}}
<div style="overflow-x:auto;padding-bottom:1.5rem;margin:0 -1rem;padding-left:1rem;padding-right:1rem;">
    <div id="kanban-board" style="display:flex;gap:1rem;min-width:max-content;align-items:flex-start;">

        @foreach($kanban as $col)
        @php $stage = $col['stage']; $leads = $col['leads']; @endphp

        <div class="kanban-column"
             data-stage-id="{{ $stage->id }}"
             data-stage-name="{{ $stage->name }}"
             style="width:256px;flex-shrink:0;display:flex;flex-direction:column;">

            {{-- Column Header --}}
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;padding:.5rem .6rem;background:#fff;border-radius:.6rem;border:1px solid #e2e8f0;">
                <span style="width:10px;height:10px;border-radius:50%;background:{{ $stage->color ?? '#64748b' }};flex-shrink:0;display:inline-block;"></span>
                <span style="font-size:.8rem;font-weight:600;color:#374151;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $stage->name }}</span>
                <span class="stage-count" style="font-size:.7rem;background:#f1f5f9;color:#64748b;padding:.1rem .45rem;border-radius:999px;font-weight:600;min-width:1.5rem;text-align:center;">
                    {{ $col['count'] }}
                </span>
            </div>

            {{-- Drop Zone --}}
            <div class="drop-zone"
                 data-stage-id="{{ $stage->id }}"
                 style="display:flex;flex-direction:column;gap:.5rem;min-height:80px;border-radius:.6rem;padding:.25rem;transition:background .15s;">

                @forelse($leads as $lead)
                <div class="lead-card"
                     data-lead-id="{{ $lead->id }}"
                     data-stage-id="{{ $stage->id }}"
                     style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem;cursor:grab;user-select:none;transition:box-shadow .15s,transform .15s;">

                    {{-- Drag handle --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.35rem;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:.875rem;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->name }}</div>
                            @if($lead->company_name)
                            <div style="font-size:.75rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->company_name }}</div>
                            @endif
                        </div>
                        <div style="cursor:grab;padding:.1rem .2rem;color:#cbd5e1;flex-shrink:0;line-height:1;" title="Arrastar">
                            <svg width="12" height="16" viewBox="0 0 12 16" fill="currentColor">
                                <circle cx="3" cy="3" r="1.5"/><circle cx="9" cy="3" r="1.5"/>
                                <circle cx="3" cy="8" r="1.5"/><circle cx="9" cy="8" r="1.5"/>
                                <circle cx="3" cy="13" r="1.5"/><circle cx="9" cy="13" r="1.5"/>
                            </svg>
                        </div>
                    </div>

                    @if($lead->segment)
                    <div style="font-size:.7rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:.35rem;">{{ $lead->segment }}</div>
                    @endif

                    <div style="display:flex;gap:.3rem;flex-wrap:wrap;margin-bottom:.35rem;">
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
                    <div style="font-size:.7rem;{{ $lead->isOverdue() ? 'color:#dc2626;font-weight:600;' : 'color:#94a3b8;' }}margin-bottom:.35rem;">
                        📅 {{ $lead->next_follow_up_at->format('d/m') }}{{ $lead->isOverdue() ? ' ⚠' : '' }}
                    </div>
                    @endif

                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:.4rem;border-top:1px solid #f1f5f9;margin-top:.1rem;">
                        <span style="font-size:.7rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:130px;">
                            {{ $lead->owner?->name ?? '' }}
                        </span>
                        <a href="{{ route('admin.prospecting.leads.show', $lead) }}"
                           onclick="event.stopPropagation();"
                           style="font-size:.75rem;color:#6366f1;text-decoration:none;font-weight:500;flex-shrink:0;">Ver →</a>
                    </div>
                </div>
                @empty
                {{-- Empty placeholder (visible when column is empty) --}}
                <div class="empty-placeholder" style="border:2px dashed #e2e8f0;border-radius:.6rem;padding:1.5rem .5rem;text-align:center;font-size:.75rem;color:#cbd5e1;">
                    Arraste um lead aqui
                </div>
                @endforelse

            </div>
        </div>
        @endforeach

    </div>
</div>

{{-- Hidden form for move-stage (reused by JS) --}}
<form id="move-stage-form" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="prospect_stage_id" id="move-stage-input">
</form>

@endif

<style>
.lead-card:active { cursor: grabbing; }
.lead-card.sortable-ghost {
    opacity: .4;
    transform: scale(.97);
}
.lead-card.sortable-drag {
    opacity: .95;
    box-shadow: 0 12px 32px rgba(0,0,0,.18);
    transform: rotate(1.5deg) scale(1.02);
    cursor: grabbing !important;
}
.drop-zone.drag-over {
    background: #eef2ff;
    border-radius: .6rem;
}
.kanban-column.drop-target > .drop-zone {
    background: #eef2ff;
}
</style>

<script>
(function () {
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var toast = document.getElementById('dnd-toast');

    function showToast(msg, ok) {
        toast.textContent = msg;
        toast.style.background = ok ? '#f0fdf4' : '#fef2f2';
        toast.style.color      = ok ? '#15803d'  : '#dc2626';
        toast.style.border     = ok ? '1px solid #bbf7d0' : '1px solid #fecaca';
        toast.style.display    = 'block';
        toast.style.opacity    = '1';
        clearTimeout(toast._t);
        toast._t = setTimeout(function () {
            toast.style.opacity = '0';
            setTimeout(function () { toast.style.display = 'none'; }, 320);
        }, 2600);
    }

    function updateColumnCount(stageId, delta) {
        var col = document.querySelector('.kanban-column[data-stage-id="' + stageId + '"]');
        if (!col) return;
        var badge = col.querySelector('.stage-count');
        if (!badge) return;
        var n = parseInt(badge.textContent, 10) || 0;
        badge.textContent = Math.max(0, n + delta);
    }

    function toggleEmpty(zone) {
        var cards  = zone.querySelectorAll('.lead-card');
        var ph     = zone.querySelector('.empty-placeholder');
        if (!ph) {
            ph = document.createElement('div');
            ph.className = 'empty-placeholder';
            ph.style.cssText = 'border:2px dashed #e2e8f0;border-radius:.6rem;padding:1.5rem .5rem;text-align:center;font-size:.75rem;color:#cbd5e1;';
            ph.textContent = 'Arraste um lead aqui';
        }
        if (cards.length === 0) {
            zone.appendChild(ph);
        } else {
            if (ph.parentNode === zone) zone.removeChild(ph);
        }
    }

    function moveStage(leadId, stageId, stageName, onSuccess, onError) {
        var url = '/admin/prospecting/leads/' + leadId + '/move-stage';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type':  'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN':  CSRF,
                'X-HTTP-Method-Override': 'PATCH',
                'Accept': 'application/json',
            },
            body: '_method=PATCH&_token=' + encodeURIComponent(CSRF)
                + '&prospect_stage_id=' + encodeURIComponent(stageId),
        })
        .then(function (r) {
            if (r.ok || r.status === 302) { onSuccess(); return; }
            return r.json().then(function (d) { onError(d.message || 'Erro ao mover lead.'); });
        })
        .catch(function () { onError('Erro de conexão.'); });
    }

    // Init SortableJS on every drop-zone
    document.querySelectorAll('.drop-zone').forEach(function (zone) {
        Sortable.create(zone, {
            group:     'leads',          // shared group → cards move between columns
            animation: 160,
            ghostClass:  'sortable-ghost',
            dragClass:   'sortable-drag',
            handle:      '.lead-card',   // whole card is the handle
            forceFallback: false,

            onStart: function (evt) {
                document.querySelectorAll('.drop-zone').forEach(function (z) {
                    z.style.minHeight = '80px';
                });
            },

            onEnd: function (evt) {
                var card       = evt.item;
                var fromZone   = evt.from;
                var toZone     = evt.to;
                var fromStage  = fromZone.dataset.stageId;
                var toStage    = toZone.dataset.stageId;
                var leadId     = card.dataset.leadId;
                var stageName  = toZone.closest('.kanban-column').dataset.stageName;

                // Dropped in the same column — nothing to do
                if (fromStage === toStage) {
                    toggleEmpty(fromZone);
                    return;
                }

                // Optimistic UI
                toggleEmpty(fromZone);
                toggleEmpty(toZone);
                updateColumnCount(fromStage, -1);
                updateColumnCount(toStage,   +1);

                // Update card's data-stage-id
                card.dataset.stageId = toStage;

                moveStage(leadId, toStage, stageName,
                    function () {
                        showToast('Lead movido para "' + stageName + '"', true);
                    },
                    function (msg) {
                        // Rollback: move card back
                        fromZone.appendChild(card);
                        card.dataset.stageId = fromStage;
                        toggleEmpty(fromZone);
                        toggleEmpty(toZone);
                        updateColumnCount(fromStage, +1);
                        updateColumnCount(toStage,   -1);
                        showToast(msg, false);
                    }
                );
            },
        });
    });
})();
</script>

</x-layouts.app>
