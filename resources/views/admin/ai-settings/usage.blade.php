<x-layouts.app>
    <x-slot name="title">Consumo de IA</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-settings.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Consumo de IA</h1>
                    <p class="text-sm text-gray-500">Histórico de chamadas ao provider</p>
                </div>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Chamadas Hoje</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $summary['total_calls_today'] }}</div>
                <div class="text-xs text-gray-400">Reais: {{ $summary['daily_real_calls'] }}/{{ $summary['daily_limit'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Custo Mês (USD)</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($summary['monthly_cost_usd'], 4) }}</div>
                <div class="text-xs text-gray-400">Limite: ${{ number_format($summary['monthly_budget_usd'], 2) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Total (histórico)</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalCallsAllTime) }}</div>
                <div class="text-xs text-gray-400">${{ number_format($totalCostAllTime, 4) }} USD total</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Taxa de Sucesso</div>
                <div class="text-2xl font-bold {{ $summary['failed_calls_today'] > 0 ? 'text-yellow-600' : 'text-green-600' }} mt-1">{{ $successRate }}%</div>
                <div class="text-xs text-gray-400">Falhas hoje: {{ $summary['failed_calls_today'] }}</div>
            </div>
        </div>

        {{-- Provider info --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex flex-wrap gap-4 text-sm">
            <div><span class="text-gray-500">Provider ativo:</span> <span class="font-mono font-semibold">{{ config('ai.provider', 'mock') }}</span></div>
            <div><span class="text-gray-500">Modelo:</span> <span class="font-mono">{{ config('ai.model', '—') }}</span></div>
            <div><span class="text-gray-500">Real habilitado:</span>
                @if (config('ai.real_enabled'))
                    <span class="text-green-600 font-medium">Sim</span>
                @else
                    <span class="text-yellow-600 font-medium">Não (modo mock)</span>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Provider</label>
                <select name="provider" class="rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2 py-1.5 text-sm">
                    <option value="">Todos</option>
                    <option value="mock" {{ request('provider') === 'mock' ? 'selected' : '' }}>mock</option>
                    <option value="openai" {{ request('provider') === 'openai' ? 'selected' : '' }}>openai</option>
                    <option value="claude" {{ request('provider') === 'claude' ? 'selected' : '' }}>claude</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2 py-1.5 text-sm">
                    <option value="">Todos</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Sucesso</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Falha</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">De</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Até</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2 py-1.5 text-sm">
            </div>
            <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 transition">Filtrar</button>
            <a href="{{ route('admin.ai-usage.index') }}" class="px-3 py-1.5 border border-gray-300 text-gray-600 rounded text-sm hover:bg-gray-50 transition">Limpar</a>
        </form>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Data</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Provider/Modelo</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Funcionário IA</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Task</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Cliente</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="text-right px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Tokens</th>
                            <th class="text-right px-4 py-3 text-xs text-gray-500 uppercase tracking-wide">Custo USD</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($calls as $call)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $call->created_at?->format('d/m H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ $call->provider }}</span>
                                    @if ($call->model)
                                        <span class="text-xs text-gray-400 ml-1">/ {{ Str::limit($call->model, 20) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">
                                    {{ $call->aiEmployee?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs max-w-xs">
                                    @if ($call->aiTask)
                                        <a href="{{ route('admin.ai-tasks.show', $call->aiTask) }}" class="hover:underline text-indigo-600">
                                            #{{ $call->ai_task_id }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">Direto</span>
                                    @endif
                                    @if ($call->prompt_preview)
                                        <div class="text-gray-400 truncate max-w-[180px]">{{ $call->prompt_preview }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">
                                    {{ $call->client?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($call->status === 'success')
                                        <span class="text-green-600 text-xs font-medium">✅ Sucesso</span>
                                    @else
                                        <span class="text-red-600 text-xs font-medium" title="{{ $call->error_message }}">❌ Falha</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-gray-600 dark:text-gray-400 tabular-nums">
                                    {{ $call->total_tokens ? number_format($call->total_tokens) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-gray-600 dark:text-gray-400 tabular-nums">
                                    {{ $call->estimated_cost !== null ? '$' . number_format($call->estimated_cost, 6) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    Nenhuma chamada registrada ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($calls->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $calls->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
