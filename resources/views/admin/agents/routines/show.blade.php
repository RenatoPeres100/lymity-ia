<x-layouts.app title="Rotina: {{ $routine->title }}">

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.agents.routines.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $routine->title }}</h1>
                    <p class="text-sm text-gray-500">{{ $routine->routine_type_label }} — {{ $routine->aiEmployee?->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.agents.routines.run-now', $routine) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                        Executar Agora
                    </button>
                </form>
                <a href="{{ route('admin.agents.routines.edit', $routine) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Editar
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                <div><span class="text-gray-500">Agente:</span> <span class="ml-1 font-medium text-gray-900 dark:text-white">{{ $routine->aiEmployee?->name }}</span></div>
                <div><span class="text-gray-500">Tipo:</span> <span class="ml-1 text-gray-700 dark:text-gray-300">{{ $routine->routine_type_label }}</span></div>
                <div><span class="text-gray-500">Status:</span>
                    @if($routine->active)
                        <span class="ml-1 text-green-600 font-semibold">Ativa</span>
                    @else
                        <span class="ml-1 text-gray-400 font-semibold">Pausada</span>
                    @endif
                </div>
                <div><span class="text-gray-500">Frequência:</span> <span class="ml-1 text-gray-700 dark:text-gray-300">{{ $routine->frequency_label }}</span></div>
                <div><span class="text-gray-500">Dias:</span> <span class="ml-1 text-gray-700 dark:text-gray-300">{{ $routine->days_label }}</span></div>
                <div><span class="text-gray-500">Horário:</span> <span class="ml-1 text-gray-700 dark:text-gray-300">{{ $routine->time_of_day ? substr($routine->time_of_day, 0, 5) : '—' }}</span></div>
                <div><span class="text-gray-500">Quantidade:</span> <span class="ml-1 text-gray-700 dark:text-gray-300">{{ $routine->content_quantity }}</span></div>
                <div><span class="text-gray-500">Próx. execução:</span> <span class="ml-1 text-gray-700 dark:text-gray-300">{{ $routine->next_run_at?->format('d/m/Y H:i') ?? '—' }}</span></div>
                <div><span class="text-gray-500">Última execução:</span> <span class="ml-1 text-gray-700 dark:text-gray-300">{{ $routine->last_run_at?->diffForHumans() ?? 'Nunca' }}</span></div>
            </div>
            @if($routine->description)
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">{{ $routine->description }}</div>
            @endif
        </div>

        {{-- Recent runs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Execuções Recentes</h3>
                <a href="{{ route('admin.agents.routines.runs', $routine) }}" class="text-sm text-indigo-600 hover:underline">Ver todas</a>
            </div>
            @if($routine->runs->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Nenhuma execução registrada.</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Data</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Resumo / Erro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($routine->runs->take(5) as $run)
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $run->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">
                                @php
                                    $statusTextClass = match($run->status) {
                                        'completed' => 'text-green-600',
                                        'running'   => 'text-blue-600',
                                        'queued'    => 'text-yellow-600',
                                        'failed'    => 'text-red-600',
                                        default     => 'text-gray-500',
                                    };
                                    $statusDotClass = match($run->status) {
                                        'completed' => 'bg-green-500',
                                        'running'   => 'bg-blue-500',
                                        'queued'    => 'bg-yellow-500',
                                        'failed'    => 'bg-red-500',
                                        default     => 'bg-gray-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 {{ $statusTextClass }} text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDotClass }}"></span>
                                    {{ $run->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-500 text-xs max-w-xs truncate">
                                {{ $run->output_summary ?? $run->error_message ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>

</x-layouts.app>
