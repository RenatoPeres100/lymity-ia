<x-layouts.app title="Execuções: {{ $routine->title }}">

    <div class="max-w-5xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agents.routines.show', $routine) }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Execuções da Rotina</h1>
                <p class="text-sm text-gray-500">{{ $routine->title }} — {{ $routine->aiEmployee?->name }}</p>
            </div>
        </div>

        @if($runs->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-10 text-center text-gray-400 text-sm">
                Nenhuma execução registrada para esta rotina.
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Início</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fim</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Duração</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Resultado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($runs as $run)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $run->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
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
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDotClass }} {{ $run->status === 'running' ? 'animate-pulse' : '' }}"></span>
                                    {{ $run->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $run->started_at?->format('H:i:s') ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $run->finished_at?->format('H:i:s') ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $run->duration ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs max-w-sm">
                                @if($run->output_summary)
                                    <span class="text-gray-700 dark:text-gray-300">{{ Str::limit($run->output_summary, 100) }}</span>
                                @elseif($run->error_message)
                                    <span class="text-red-600">{{ Str::limit($run->error_message, 120) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($runs->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $runs->links() }}
                </div>
                @endif
            </div>
        @endif

    </div>

</x-layouts.app>
