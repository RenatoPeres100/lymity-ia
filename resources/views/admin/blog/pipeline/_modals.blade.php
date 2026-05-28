{{-- Schedule modal --}}
<div id="scheduleModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h3 id="scheduleModalTitle" class="font-semibold text-gray-900 dark:text-white mb-1">Agendar Publicação</h3>
        <p id="scheduleModalSubtitle" class="text-xs text-gray-500 dark:text-gray-400 mb-4 hidden"></p>
        <form id="scheduleForm" method="POST">
            @csrf
            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Data e hora</label>
            <input type="datetime-local" id="scheduleInput" name="scheduled_at" required
                   min="{{ now()->addMinutes(1)->format('Y-m-d\TH:i') }}"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm mb-4">
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeScheduleModal()"
                        class="px-4 py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    Cancelar
                </button>
                <button type="submit" id="scheduleSubmitBtn" class="px-4 py-2 text-sm rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-medium">
                    Agendar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reject modal --}}
<div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Reprovar Post</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Motivo (opcional)</label>
            <textarea name="reason" rows="3" placeholder="Descreva o motivo..."
                      class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm mb-4"></textarea>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium">
                    Reprovar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openScheduleModal(postId, currentScheduledAt) {
    document.getElementById('scheduleForm').action = '/admin/blog/posts/' + postId + '/schedule';

    var input    = document.getElementById('scheduleInput');
    var title    = document.getElementById('scheduleModalTitle');
    var subtitle = document.getElementById('scheduleModalSubtitle');
    var btn      = document.getElementById('scheduleSubmitBtn');

    if (currentScheduledAt) {
        // Pre-fill with existing value (format: "2026-06-01 09:00:00" -> "2026-06-01T09:00")
        input.value = currentScheduledAt.replace(' ', 'T').substring(0, 16);
        title.textContent   = 'Editar Agendamento';
        btn.textContent     = 'Salvar Agendamento';
        subtitle.textContent = 'Agendado para ' + currentScheduledAt.substring(0, 16).replace('T', ' ');
        subtitle.classList.remove('hidden');
    } else {
        input.value         = '';
        title.textContent   = 'Agendar Publicação';
        btn.textContent     = 'Agendar';
        subtitle.classList.add('hidden');
    }

    document.getElementById('scheduleModal').classList.remove('hidden');
    setTimeout(function() { input.focus(); }, 50);
}
function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
}
function openRejectModal(postId) {
    document.getElementById('rejectForm').action = '/admin/blog/posts/' + postId + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
