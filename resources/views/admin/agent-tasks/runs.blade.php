<x-layouts.app title="Execuções — {{ $agentTask->title }}">

    <div class="max-w-6xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agent-tasks.show', $agentTask) }}" class="text-gray-500 hover:text-gray-700 text-sm">← {{ $agentTask->title }}</a>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Execuções</h1>
        </div>

        @if($runs->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-10 text-center text-gray-500">
                Nenhuma execução registrada.
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Iniciada</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Duração</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tokens</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Aprovação</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Erro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($runs as $run)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-gray-500">{{ $run->id }}</td>
                                <td class="px-4 py-3">
                                    @php $sc = $run->status_color; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs
                                        {{ $sc === 'green' ? 'bg-green-100 text-green-800' : ($sc === 'blue' ? 'bg-blue-100 text-blue-800' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-800' : ($sc === 'red' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'))) }}">
                                        {{ $run->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $run->started_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $run->duration ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">
                                    @if($run->input_tokens || $run->output_tokens)
                                        {{ number_format(($run->input_tokens ?? 0) + ($run->output_tokens ?? 0)) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if($run->approvalRequest)
                                        <a href="{{ route('admin.approvals.show', $run->approval_request_id) }}"
                                           class="text-indigo-600 hover:underline">
                                            #{{ $run->approval_request_id }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-red-600 text-xs max-w-xs truncate">
                                    {{ $run->error_message ? \Str::limit($run->error_message, 80) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $runs->links() }}</div>
        @endif
    </div>

</x-layouts.app>
