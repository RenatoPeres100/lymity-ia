<x-layouts.app title="Rotinas dos Agentes">

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Rotinas dos Agentes IA</h1>
                <p class="text-sm text-gray-500 mt-0.5">Agenda, execução e histórico das rotinas operacionais dos agentes.</p>
            </div>
            <a href="{{ route('admin.agents.routines.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Rotina
            </a>
        </div>

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if($routines->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                <p class="text-gray-500">Nenhuma rotina cadastrada.</p>
                <a href="{{ route('admin.agents.routines.create') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Criar primeira rotina</a>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Agente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo / Título</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Frequência</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Próx. Execução</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Última Execução</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($routines as $routine)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $routine->aiEmployee?->name ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $routine->title }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $routine->routine_type_label }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $routine->frequency_label }}</div>
                                @if($routine->days_of_week)
                                <div class="text-xs text-gray-500">{{ $routine->days_label }}</div>
                                @endif
                                @if($routine->time_of_day)
                                <div class="text-xs text-gray-400">{{ substr($routine->time_of_day, 0, 5) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($routine->next_run_at)
                                    <span class="{{ $routine->isDue() ? 'text-orange-600 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $routine->next_run_at->format('d/m/Y H:i') }}
                                    </span>
                                    <div class="text-xs text-gray-400">{{ $routine->next_run_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($routine->last_run_at)
                                    <span class="text-gray-700 dark:text-gray-300">{{ $routine->last_run_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-gray-400">Nunca</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($routine->active)
                                    <span class="inline-flex items-center gap-1 text-green-600 text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Ativa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-gray-400 text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Pausada
                                    </span>
                                @endif
                                @if($routine->requires_approval)
                                <div class="text-xs text-amber-600 mt-0.5">Requer aprovação</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Run now --}}
                                    <form method="POST" action="{{ route('admin.agents.routines.run-now', $routine) }}">
                                        @csrf
                                        <button type="submit" title="Executar agora"
                                                class="p-1.5 rounded text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>

                                    {{-- Pause/Activate --}}
                                    @if($routine->active)
                                    <form method="POST" action="{{ route('admin.agents.routines.pause', $routine) }}">
                                        @csrf
                                        <button type="submit" title="Pausar"
                                                class="p-1.5 rounded text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
                                        </button>
                                    </form>
                                    @else
                                    <form method="POST" action="{{ route('admin.agents.routines.activate', $routine) }}">
                                        @csrf
                                        <button type="submit" title="Ativar"
                                                class="p-1.5 rounded text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Runs --}}
                                    <a href="{{ route('admin.agents.routines.runs', $routine) }}" title="Ver execuções"
                                       class="p-1.5 rounded text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.agents.routines.edit', $routine) }}" title="Editar"
                                       class="p-1.5 rounded text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.agents.routines.destroy', $routine) }}"
                                          onsubmit="return confirm('Excluir a rotina \"{{ addslashes($routine->title) }}\"? Esta ação não pode ser desfeita.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Excluir"
                                                class="p-1.5 rounded text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($routines->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $routines->links() }}
                </div>
                @endif
            </div>
        @endif

    </div>

</x-layouts.app>
