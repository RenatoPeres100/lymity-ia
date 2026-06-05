<x-layouts.app title="Central de Memória IA">

    <div class="max-w-6xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Central de Memória IA</h1>
                <p class="text-sm text-gray-500 mt-0.5">Memórias operacionais dos funcionários IA. Padrões aprovados, rejeitados, feedbacks e regras.</p>
            </div>
            <a href="{{ route('admin.ai-memory.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Memória
            </a>
        </div>

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <select name="type" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="">Todos os tipos</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="employee" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="">Todos os funcionários</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee') == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
            <select name="active" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="">Todas</option>
                <option value="1" @selected(request('active') === '1')>Ativas</option>
                <option value="0" @selected(request('active') === '0')>Inativas</option>
            </select>
            <button type="submit" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg dark:bg-gray-700">Filtrar</button>
        </form>

        @if($memories->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-10 text-center text-gray-500">
                Nenhuma memória cadastrada.
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 w-10"><input type="checkbox" id="bulk-select-all"></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Título</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Funcionário</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Relevância</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Ativa</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($memories as $mem)
                            <tr data-bulk-row class="hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer">
                                <td class="px-4 py-3"><input type="checkbox" class="bulk-cb" value="{{ $mem->id }}"></td>
                                <td class="px-4 py-3">
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded">
                                        {{ $mem->memory_type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $mem->title }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ \Str::limit($mem->getEffectiveContent(), 100) }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $mem->employee?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $mem->relevance_score ?? 50 }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs {{ $mem->active ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ $mem->active ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.ai-memory.edit', $mem) }}" class="text-xs text-indigo-600 hover:underline">Editar</a>
                                        @if($mem->active)
                                            <form method="POST" action="{{ route('admin.ai-memory.deactivate', $mem) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-xs text-yellow-600 hover:underline">Desativar</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.ai-memory.activate', $mem) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-xs text-green-600 hover:underline">Ativar</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.ai-memory.destroy', $mem) }}" class="inline"
                                              onsubmit="return confirm('Remover esta memória?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:underline">Remover</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $memories->links() }}</div>
        @endif
    </div>

<x-bulk-actions :action="route('admin.ai-memory.bulk-delete')" />

</x-layouts.app>
