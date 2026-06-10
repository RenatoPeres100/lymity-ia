<x-layouts.app title="{{ $agentTask->title }}">

    <div class="max-w-6xl mx-auto space-y-6">

        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('admin.agent-tasks.index') }}" class="hover:text-indigo-600">Tarefas Operacionais</a>
                    <span>/</span>
                    <span>{{ $agentTask->title }}</span>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $agentTask->title }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    @php $c = $agentTask->status_color; @endphp
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $c === 'green' ? 'bg-green-100 text-green-800' : ($c === 'yellow' ? 'bg-yellow-100 text-yellow-800' : ($c === 'red' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $agentTask->status_label }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $agentTask->task_type_label }}</span>
                    @if($agentTask->aiEmployee)
                        <span class="text-xs text-gray-500">• {{ $agentTask->aiEmployee->name }}</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2">
                @if($agentTask->isActive())
                    <form method="POST" action="{{ route('admin.agent-tasks.run-now', $agentTask) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg font-medium transition">
                            Executar Agora
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.agent-tasks.pause', $agentTask) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg font-medium transition">
                            Pausar
                        </button>
                    </form>
                @elseif($agentTask->isPaused())
                    <form method="POST" action="{{ route('admin.agent-tasks.activate', $agentTask) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg font-medium transition">
                            Ativar
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.agent-tasks.edit', $agentTask) }}"
                   class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm rounded-lg font-medium transition">
                    Editar
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-3 gap-4">
            {{-- Info cards --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Próxima Execução</div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    {{ $agentTask->next_run_at?->format('d/m/Y H:i') ?? '—' }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $agentTask->frequency_label }}
                    @if($agentTask->days_of_week) • {{ $agentTask->days_label }} @endif
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Último Sucesso</div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    {{ $agentTask->last_success_at?->format('d/m/Y H:i') ?? 'Nunca' }}
                </div>
                @if($agentTask->failure_count > 0)
                    <div class="text-xs text-red-500 mt-0.5">{{ $agentTask->failure_count }} falha(s)</div>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Execuções Total</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $runs->count() }}</div>
                <div class="text-xs text-gray-500 mt-0.5">Pacotes: {{ $packages->count() }}</div>
            </div>
        </div>

        {{-- Instructions --}}
        @if($agentTask->operational_instructions)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-semibold text-gray-800 dark:text-white text-sm mb-3">Instruções Operacionais</h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $agentTask->operational_instructions }}</p>
            </div>
        @endif

        {{-- Compact context --}}
        @if($agentTask->compact_task_context)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Contexto Compacto da Tarefa</h2>
                    <span class="text-xs text-gray-400">v{{ $agentTask->version }} • gerado {{ $agentTask->compact_task_context_generated_at?->diffForHumans() ?? 'nunca' }}</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 rounded-lg p-3">{{ $agentTask->compact_task_context }}</p>
            </div>
        @endif

        {{-- Recent Runs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Últimas Execuções</h2>
                <a href="{{ route('admin.agent-tasks.runs', $agentTask) }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
            </div>
            @if($runs->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Nenhuma execução ainda.</div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Iniciada</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Duração</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Erro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($runs as $run)
                            <tr>
                                <td class="px-4 py-2 text-gray-500">{{ $run->id }}</td>
                                <td class="px-4 py-2">
                                    @php $sc = $run->status_color; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs
                                        {{ $sc === 'green' ? 'bg-green-100 text-green-800' : ($sc === 'blue' ? 'bg-blue-100 text-blue-800' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-800' : ($sc === 'red' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'))) }}">
                                        {{ $run->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-gray-600 text-xs">{{ $run->started_at?->format('d/m H:i') ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-600 text-xs">{{ $run->duration ?? '—' }}</td>
                                <td class="px-4 py-2 text-xs max-w-xs">
                                    @if($run->error_message)
                                        <span class="text-red-600">{{ \Str::limit($run->error_message, 80) }}</span>
                                        @php
                                            $meta = $run->metadata ?? [];
                                            $isPartial = isset($meta['payload_received_keys']);
                                        @endphp
                                        @if($isPartial)
                                        <div class="mt-1 p-2 bg-amber-50 border border-amber-200 rounded text-amber-800 text-xs space-y-0.5">
                                            <div><strong>Payload incompleto</strong> — A IA retornou artigo incompleto. Verifique <code>AI_BLOG_MAX_OUTPUT_TOKENS</code>.</div>
                                            @if(!empty($meta['payload_received_keys']))
                                            <div>Campos recebidos: <span class="font-mono">{{ implode(', ', (array)$meta['payload_received_keys']) }}</span></div>
                                            @endif
                                            @if(!empty($meta['finish_reason']))
                                            <div>finish_reason: <span class="font-mono font-bold {{ $meta['finish_reason'] === 'MAX_TOKENS' ? 'text-red-700' : '' }}">{{ $meta['finish_reason'] }}</span></div>
                                            @endif
                                            @if(!empty($meta['output_tokens']))
                                            <div>output_tokens: {{ $meta['output_tokens'] }}</div>
                                            @endif
                                            @if(!empty($meta['retry_attempted']))
                                            <div>Retry tentado: sim</div>
                                            @endif
                                        </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Memories --}}
        @if($memories->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Memórias Operacionais</h2>
                    <a href="{{ route('admin.ai-memory.index', ['task_id' => $agentTask->id]) }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
                </div>
                <div class="space-y-2">
                    @foreach($memories as $mem)
                        <div class="flex items-start gap-3 text-sm">
                            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded shrink-0">{{ $mem->memory_type_label }}</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ \Str::limit($mem->getEffectiveContent(), 120) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</x-layouts.app>
