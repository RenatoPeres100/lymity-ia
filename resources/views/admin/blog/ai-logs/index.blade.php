<x-layouts.app title="Logs de IA — Blog">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Logs de IA — Blog</h1>
            <p class="text-sm text-gray-500 mt-1">Registros de geração de briefings e artigos com Gemini</p>
        </div>
        <a href="{{ route('admin.blog.automation.index') }}" class="btn-secondary text-sm">← Automation</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <p class="text-xs text-gray-400 uppercase mb-1">Chamadas hoje</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCallsToday }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-xs text-gray-400 uppercase mb-1">Custo estimado mês</p>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($totalCostMonth, 4) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-xs text-gray-400 uppercase mb-1">Total logs</p>
                <p class="text-2xl font-bold text-gray-900">{{ $logs->total() }}</p>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header"><h3 class="font-semibold text-gray-900">Logs de Tarefas IA</h3></div>
        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="py-8 text-center text-gray-400 text-sm">Nenhum log encontrado.</div>
            @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tarefa</th>
                        <th class="px-4 py-3 text-left">Modelo</th>
                        <th class="px-4 py-3 text-right">Input Tokens</th>
                        <th class="px-4 py-3 text-right">Output Tokens</th>
                        <th class="px-4 py-3 text-right">Custo est.</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Criado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $log->task_type ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $log->model ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ $log->input_tokens ? number_format($log->input_tokens) : '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ $log->output_tokens ? number_format($log->output_tokens) : '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ $log->estimated_cost_usd ? '$'.number_format($log->estimated_cost_usd,6) : '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ match($log->status) {
                                'success' => 'badge-green',
                                'error' => 'badge-danger',
                                default => 'badge-blue'
                            } }} text-xs">{{ $log->status ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $log->created_at?->format('d/m H:i') }}</td>
                    </tr>
                    @if($log->error_message)
                    <tr class="bg-red-50">
                        <td colspan="7" class="px-4 py-2 text-xs text-red-600">{{ $log->error_message }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>

    @if($usageRecords->isNotEmpty())
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-gray-900">Registros de Uso (AI Usage Records)</h3></div>
        <div class="card-body p-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Provider</th>
                        <th class="px-4 py-3 text-left">Modelo</th>
                        <th class="px-4 py-3 text-left">Tarefa</th>
                        <th class="px-4 py-3 text-right">Tokens</th>
                        <th class="px-4 py-3 text-right">Custo est.</th>
                        <th class="px-4 py-3 text-left">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($usageRecords as $rec)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $rec->provider }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $rec->model }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $rec->task_type ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format(($rec->input_tokens??0)+($rec->output_tokens??0)) }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($rec->estimated_cost_usd??0,6) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $rec->created_at?->format('d/m H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</x-layouts.app>
