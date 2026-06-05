<x-layouts.app title="Uso e Consumo de IA">

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Uso e Consumo de IA</h1>
                <p class="text-sm text-gray-500 mt-0.5">Controle de tokens, custos e limites de uso da IA.</p>
            </div>
        </div>

        {{-- Limits cards --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-2">Uso Diário</div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold {{ $daily['exceeded'] ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                        {{ $daily['count'] }}
                    </span>
                    <span class="text-gray-500 text-sm mb-0.5">/ {{ $daily['limit'] }} requests</span>
                </div>
                @if($daily['exceeded'])
                    <div class="text-xs text-red-600 mt-1">⚠ Limite atingido</div>
                @else
                    <div class="text-xs text-gray-500 mt-1">Restam {{ $daily['remaining'] }} requests hoje</div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-2">Orçamento Mensal</div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold {{ $monthly['exceeded'] ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                        ${{ number_format($monthly['estimated_cost_usd'], 2) }}
                    </span>
                    <span class="text-gray-500 text-sm mb-0.5">/ ${{ number_format($monthly['budget_usd'], 2) }}</span>
                </div>
                @if($monthly['exceeded'])
                    <div class="text-xs text-red-600 mt-1">⚠ Orçamento excedido</div>
                @else
                    <div class="text-xs text-gray-500 mt-1">Restam ${{ number_format($monthly['remaining_usd'], 2) }}</div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-2">Records Este Mês</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $records->total() }}</div>
                <div class="text-xs text-gray-500 mt-1">Execuções registradas</div>
            </div>
        </div>

        {{-- By employee --}}
        @if($byEmployee->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Por Funcionário IA (este mês)</h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Funcionário</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Requests</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tokens Entrada</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tokens Saída</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Custo Est.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($byEmployee as $row)
                            <tr>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row->aiEmployee?->name ?? 'Desconhecido' }}</td>
                                <td class="px-4 py-2 text-gray-600 text-xs">{{ number_format($row->requests) }}</td>
                                <td class="px-4 py-2 text-gray-600 text-xs">{{ number_format($row->total_input ?? 0) }}</td>
                                <td class="px-4 py-2 text-gray-600 text-xs">{{ number_format($row->total_output ?? 0) }}</td>
                                <td class="px-4 py-2 text-gray-600 text-xs">${{ number_format($row->total_cost ?? 0, 4) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <select name="period" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="month" @selected(request('period', 'month') === 'month')>Este mês</option>
                <option value="week" @selected(request('period') === 'week')>Esta semana</option>
                <option value="today" @selected(request('period') === 'today')>Hoje</option>
            </select>
            <select name="employee" class="text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                <option value="">Todos os funcionários</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee') == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg dark:bg-gray-700">Filtrar</button>
        </form>

        {{-- Records table --}}
        @if($records->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500 text-sm">
                Nenhum registro encontrado.
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Funcionário</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tarefa</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Provider/Modelo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tokens</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Custo Est.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($records as $rec)
                            <tr>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $rec->created_at?->format('d/m H:i') }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ $rec->aiEmployee?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600 max-w-xs truncate">{{ $rec->agentTask?->title ?? $rec->task_type ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ $rec->provider }}/{{ \Str::limit($rec->model, 20) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $rec->input_tokens ? number_format(($rec->input_tokens ?? 0) + ($rec->output_tokens ?? 0)) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $rec->estimated_cost_usd ? '$' . number_format($rec->estimated_cost_usd, 5) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $records->links() }}</div>
        @endif
    </div>

</x-layouts.app>
