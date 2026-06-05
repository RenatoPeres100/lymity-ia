<x-layouts.app title="Tarefas Operacionais">

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tarefas Operacionais</h1>
                <p class="text-sm text-gray-500 mt-0.5">Tarefas recorrentes dos funcionários IA. Cada tarefa executa de forma autônoma e gera conteúdo para aprovação.</p>
            </div>
            <a href="{{ route('admin.agent-tasks.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Tarefa
            </a>
        </div>

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <select name="status" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="">Todos os status</option>
                <option value="active" @selected(request('status') === 'active')>Ativa</option>
                <option value="paused" @selected(request('status') === 'paused')>Pausada</option>
                <option value="disabled" @selected(request('status') === 'disabled')>Desativada</option>
                <option value="archived" @selected(request('status') === 'archived')>Arquivada</option>
            </select>
            <select name="task_type" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="">Todos os tipos</option>
                <option value="blog_post_recurring" @selected(request('task_type') === 'blog_post_recurring')>Blog Post</option>
                <option value="instagram_post_recurring" @selected(request('task_type') === 'instagram_post_recurring')>Instagram Post</option>
                <option value="instagram_carousel_recurring" @selected(request('task_type') === 'instagram_carousel_recurring')>Instagram Carrossel</option>
            </select>
            <select name="employee" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="">Todos os funcionários</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee') == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg dark:bg-gray-700 dark:hover:bg-gray-600">Filtrar</button>
            @if(request()->anyFilled(['status', 'task_type', 'employee']))
                <a href="{{ route('admin.agent-tasks.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpar</a>
            @endif
        </form>

        @if($tasks->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                <p class="text-gray-500">Nenhuma tarefa operacional cadastrada.</p>
                <a href="{{ route('admin.agent-tasks.create') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Criar primeira tarefa</a>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Tarefa</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Funcionário</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Frequência</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Próxima Execução</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($tasks as $task)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        <a href="{{ route('admin.agent-tasks.show', $task) }}" class="hover:text-indigo-600">{{ $task->title }}</a>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $task->task_type_label }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $task->aiEmployee?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    <div>{{ $task->frequency_label }}</div>
                                    @if($task->days_of_week)
                                        <div class="text-xs text-gray-500">{{ $task->days_label }}</div>
                                    @endif
                                    @if($task->time_of_day)
                                        <div class="text-xs text-gray-500">{{ \Str::before($task->time_of_day, ':') }}:{{ explode(':', $task->time_of_day)[1] ?? '00' }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                    {{ $task->next_run_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php $color = $task->status_color; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $color === 'green' ? 'bg-green-100 text-green-800' : ($color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : ($color === 'red' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700')) }}">
                                        {{ $task->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.agent-tasks.show', $task) }}" class="text-xs text-indigo-600 hover:underline">Ver</a>
                                        @if($task->isActive())
                                            <form method="POST" action="{{ route('admin.agent-tasks.run-now', $task) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs text-emerald-600 hover:underline">Executar</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.agent-tasks.pause', $task) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-xs text-yellow-600 hover:underline">Pausar</button>
                                            </form>
                                        @elseif($task->isPaused())
                                            <form method="POST" action="{{ route('admin.agent-tasks.activate', $task) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-xs text-green-600 hover:underline">Ativar</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.agent-tasks.edit', $task) }}" class="text-xs text-gray-500 hover:underline">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $tasks->links() }}</div>
        @endif
    </div>

</x-layouts.app>
