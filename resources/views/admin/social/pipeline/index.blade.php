<x-layouts.app title="Pipeline Social">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pipeline Social</h1>
            <p class="text-sm text-gray-500 mt-0.5">Esteira operacional de posts sociais da Lymity</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.social.posts.ai-create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Gerar com Social Media IA
            </a>
            <a href="{{ route('admin.social.posts.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 transition">
                + Criar Manual
            </a>
            <a href="{{ route('admin.social.posts.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 transition">
                Ver Listagem
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Pipeline columns --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

        {{-- 1. Rascunhos --}}
        @include('admin.social.pipeline._column', [
            'columnTitle' => 'Rascunhos',
            'colorClass'  => 'bg-gray-100 dark:bg-gray-700',
            'badgeClass'  => 'bg-gray-200 text-gray-700',
            'posts'       => $drafts,
            'emptyText'   => 'Nenhum rascunho',
        ])

        {{-- 2. Aguardando Aprovação --}}
        @include('admin.social.pipeline._column', [
            'columnTitle' => 'Aguardando Aprovação',
            'colorClass'  => 'bg-yellow-50 dark:bg-yellow-900/20',
            'badgeClass'  => 'bg-yellow-100 text-yellow-800',
            'posts'       => $pendingApproval,
            'emptyText'   => 'Nenhum aguardando',
        ])

        {{-- 3. Aprovados --}}
        @include('admin.social.pipeline._column', [
            'columnTitle' => 'Aprovados',
            'colorClass'  => 'bg-blue-50 dark:bg-blue-900/20',
            'badgeClass'  => 'bg-blue-100 text-blue-800',
            'posts'       => $approved,
            'emptyText'   => 'Nenhum aprovado',
        ])

        {{-- 4. Agendados --}}
        @include('admin.social.pipeline._column', [
            'columnTitle' => 'Agendados',
            'colorClass'  => 'bg-purple-50 dark:bg-purple-900/20',
            'badgeClass'  => 'bg-purple-100 text-purple-800',
            'posts'       => $scheduled,
            'emptyText'   => 'Nenhum agendado',
        ])

        {{-- 5. Publicados --}}
        @include('admin.social.pipeline._column', [
            'columnTitle' => 'Publicados',
            'colorClass'  => 'bg-green-50 dark:bg-green-900/20',
            'badgeClass'  => 'bg-green-100 text-green-800',
            'posts'       => $published,
            'emptyText'   => 'Nenhum publicado',
        ])

        {{-- 6. Falhas --}}
        @include('admin.social.pipeline._column', [
            'columnTitle' => 'Falhas',
            'colorClass'  => 'bg-red-50 dark:bg-red-900/20',
            'badgeClass'  => 'bg-red-100 text-red-800',
            'posts'       => $failed,
            'emptyText'   => 'Sem falhas',
        ])

    </div>

    {{-- Schedule Modal --}}
    <div id="schedule-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Agendar publicação</h3>
            <form id="schedule-form" method="POST">
                @csrf @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data e hora</label>
                    <input type="datetime-local" name="scheduled_at" id="schedule-datetime"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm"
                           required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeScheduleModal()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700">
                        Agendar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openScheduleModal(postId, currentDate) {
            const form = document.getElementById('schedule-form');
            form.action = '/admin/social/posts/' + postId + '/schedule';
            if (currentDate) {
                document.getElementById('schedule-datetime').value = currentDate.replace(' ', 'T').substring(0, 16);
            }
            document.getElementById('schedule-modal').classList.remove('hidden');
        }
        function closeScheduleModal() {
            document.getElementById('schedule-modal').classList.add('hidden');
        }
        document.getElementById('schedule-modal').addEventListener('click', function(e) {
            if (e.target === this) closeScheduleModal();
        });
    </script>

</x-layouts.app>
