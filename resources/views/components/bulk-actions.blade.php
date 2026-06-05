{{--
  Bulk Actions Component
  Usage: <x-bulk-actions :action="route('admin.xxx.bulk-delete')" />
  Rows must have: <input type="checkbox" class="bulk-cb" value="{{ $item->id }}">
  Table header checkbox: <input type="checkbox" id="bulk-select-all">
--}}
@props([
    'action',
    'label'        => 'Excluir selecionados',
    'confirmMsg'   => 'Excluir os itens selecionados? Esta ação não pode ser desfeita.',
    'extraActions' => [],  // [['label'=>'...','value'=>'...','class'=>'...']]
    'method'       => 'DELETE',
])

{{-- Floating bulk bar --}}
<div id="bulk-bar"
     style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;
            background:#1e293b;border:1px solid #334155;border-radius:14px;
            padding:12px 20px;box-shadow:0 8px 40px rgba(0,0,0,.45);
            display:flex;align-items:center;gap:14px;min-width:340px;max-width:600px;
            animation:bulkSlideIn .18s ease-out;">
    <span id="bulk-count"
          style="font-size:.8rem;font-weight:700;color:#f1f5f9;white-space:nowrap;">
        0 selecionados
    </span>

    <div style="flex:1;"></div>

    @if(count($extraActions))
        @foreach($extraActions as $act)
            <button type="button"
                    onclick="bulkSubmit('{{ $action }}','{{ $act['value'] }}')"
                    style="font-size:.78rem;font-weight:600;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;background:#334155;color:#e2e8f0;"
                    class="{{ $act['class'] ?? '' }}">
                {{ $act['label'] }}
            </button>
        @endforeach
    @endif

    <button type="button"
            onclick="bulkConfirmDelete('{{ $action }}','{{ $confirmMsg }}')"
            style="font-size:.78rem;font-weight:700;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;background:#dc2626;color:#fff;display:flex;align-items:center;gap:6px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
            <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
        </svg>
        {{ $label }}
    </button>

    <button type="button" onclick="bulkClear()"
            style="font-size:.78rem;color:#64748b;background:none;border:none;cursor:pointer;padding:4px;">✕</button>
</div>

{{-- Hidden bulk form --}}
<form id="bulk-form" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="_method" id="bulk-method" value="{{ $method }}">
    <input type="hidden" name="_bulk_action" id="bulk-action-value" value="delete">
    <div id="bulk-ids"></div>
</form>

<style>
@keyframes bulkSlideIn {
    from { opacity:0; transform:translateX(-50%) translateY(16px); }
    to   { opacity:1; transform:translateX(-50%) translateY(0); }
}
.bulk-cb { width:16px;height:16px;cursor:pointer;accent-color:#6366f1; }
#bulk-select-all { width:16px;height:16px;cursor:pointer;accent-color:#6366f1; }
.bulk-row-selected { background:rgba(99,102,241,.07) !important; }
</style>

<script>
(function() {
    const bar      = document.getElementById('bulk-bar');
    const countEl  = document.getElementById('bulk-count');
    const selectAll= document.getElementById('bulk-select-all');

    function getCheckboxes() {
        return Array.from(document.querySelectorAll('.bulk-cb'));
    }

    function getChecked() {
        return getCheckboxes().filter(cb => cb.checked);
    }

    function updateBar() {
        const checked = getChecked();
        const n = checked.length;
        if (n > 0) {
            countEl.textContent = n + ' selecionado' + (n > 1 ? 's' : '');
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
        // Highlight rows
        getCheckboxes().forEach(cb => {
            const row = cb.closest('tr');
            if (row) row.classList.toggle('bulk-row-selected', cb.checked);
        });
        // Update select-all state
        if (selectAll) {
            const all = getCheckboxes();
            selectAll.indeterminate = n > 0 && n < all.length;
            selectAll.checked = all.length > 0 && n === all.length;
        }
    }

    // Select all
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            getCheckboxes().forEach(cb => { cb.checked = this.checked; });
            updateBar();
        });
    }

    // Individual checkboxes (delegated)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('bulk-cb')) updateBar();
    });

    // Click on row to toggle (optional, clicks outside checkbox)
    document.addEventListener('click', function(e) {
        const row = e.target.closest('tr[data-bulk-row]');
        if (row && e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON'
            && e.target.tagName !== 'INPUT' && !e.target.closest('a') && !e.target.closest('button') && !e.target.closest('form')) {
            const cb = row.querySelector('.bulk-cb');
            if (cb) { cb.checked = !cb.checked; updateBar(); }
        }
    });

    window.bulkClear = function() {
        getCheckboxes().forEach(cb => { cb.checked = false; });
        if (selectAll) selectAll.checked = false;
        updateBar();
    };

    window.bulkConfirmDelete = function(action, msg) {
        const ids = getChecked().map(cb => cb.value);
        if (!ids.length) return;
        if (!confirm(msg || 'Excluir os itens selecionados?')) return;
        bulkSubmit(action, 'delete');
    };

    window.bulkSubmit = function(action, actionValue) {
        const ids = getChecked().map(cb => cb.value);
        if (!ids.length) return;

        const form = document.getElementById('bulk-form');
        const idsContainer = document.getElementById('bulk-ids');
        document.getElementById('bulk-action-value').value = actionValue;

        form.action = action;
        idsContainer.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = id;
            idsContainer.appendChild(inp);
        });
        form.submit();
    };
})();
</script>
