<x-layouts.app>
    <x-slot name="title">Configurações de IA</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configurações de IA</h1>
                <p class="text-sm text-gray-500 mt-1">Provider, limites e controle de custo</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.ai-settings.test') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    🔌 Testar Conexão
                </a>
                <a href="{{ route('admin.ai-usage.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700 transition">
                    📊 Ver Consumo
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Provider Status --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Provider Ativo</div>
                <div class="flex items-center gap-2 mt-1">
                    @if ($provider === 'mock')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">🤖 Mock</span>
                    @elseif ($provider === 'openai')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">✅ OpenAI</span>
                    @elseif ($provider === 'claude')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">✅ Claude</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">{{ $provider }}</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-2">Configure via <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">AI_PROVIDER</code> no .env</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Modelo</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1 font-mono text-sm">{{ $model }}</div>
                <div class="text-xs text-gray-400 mt-2">Configure via <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">AI_MODEL</code> no .env</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">API Key</div>
                <div class="flex items-center gap-2 mt-1">
                    @if ($hasApiKey)
                        <span class="text-green-600 font-medium text-sm">✅ Configurada</span>
                        <span class="text-xs text-gray-400">(não exibida por segurança)</span>
                    @else
                        <span class="text-yellow-600 font-medium text-sm">⚠️ Não configurada</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-2">Configure via <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">AI_API_KEY</code> no .env</div>
            </div>
        </div>

        {{-- Usage Today --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Chamadas Hoje</div>
                <div class="text-2xl font-bold mt-1 {{ $usage['daily_limit_exceeded'] ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                    {{ $usage['total_calls_today'] }}
                </div>
                <div class="text-xs text-gray-400">Reais: {{ $usage['daily_real_calls'] }}/{{ $usage['daily_limit'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Custo Mês (USD)</div>
                <div class="text-2xl font-bold mt-1 {{ $usage['monthly_budget_exceeded'] ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                    ${{ number_format($usage['monthly_cost_usd'], 4) }}
                </div>
                <div class="text-xs text-gray-400">Limite: ${{ number_format($usage['monthly_budget_usd'], 2) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Falhas Hoje</div>
                <div class="text-2xl font-bold mt-1 {{ $usage['failed_calls_today'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $usage['failed_calls_today'] }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Provider Real</div>
                <div class="text-sm font-medium mt-2">
                    @if ($realEnabled)
                        <span class="text-green-600">✅ Habilitado</span>
                    @else
                        <span class="text-yellow-600">⚠️ Desabilitado</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-1">AI_REAL_ENABLED no .env</div>
            </div>
        </div>

        @if ($usage['daily_limit_exceeded'])
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                ⚠️ <strong>Limite diário atingido.</strong> Chamadas reais bloqueadas por hoje. Provider mock continua disponível.
            </div>
        @endif

        @if ($usage['monthly_budget_exceeded'])
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                ⚠️ <strong>Orçamento mensal excedido.</strong> Atualize <code>AI_MONTHLY_BUDGET_LIMIT</code> no .env para continuar.
            </div>
        @endif

        {{-- Limits Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Limites de Uso</h2>
            <form action="{{ route('admin.ai-settings.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf @method('POST')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Limite Diário de Tarefas Reais
                    </label>
                    <input type="number" name="daily_task_limit" min="1" max="1000"
                           value="{{ old('daily_task_limit', $dailyLimit) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Chamadas ao provider real por dia (0 = ilimitado)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Orçamento Mensal (USD)
                    </label>
                    <input type="number" name="monthly_budget_limit" min="0" max="10000" step="0.01"
                           value="{{ old('monthly_budget_limit', $monthlyLimit) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Custo máximo estimado por mês em USD (0 = ilimitado)</p>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        Salvar Limites
                    </button>
                </div>
            </form>
        </div>

        {{-- Configuration Reference --}}
        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Configuração via .env</h2>
            <p class="text-xs text-gray-500 mb-3">Para usar um provider real, edite o <code>.env</code> do servidor:</p>
            <pre class="bg-gray-800 text-green-400 rounded-lg p-4 text-xs overflow-x-auto leading-relaxed">AI_PROVIDER=openai          # mock | openai | claude
AI_API_KEY=sk-...           # Nunca commitar no git
AI_MODEL=gpt-4o-mini        # ou claude-haiku-4-5-20251001
AI_MAX_TOKENS=1200
AI_TEMPERATURE=0.7
AI_MONTHLY_BUDGET_LIMIT=100
AI_DAILY_TASK_LIMIT=50
AI_REAL_ENABLED=true        # Habilita provider real</pre>
            <p class="text-xs text-gray-400 mt-2">⚠️ A API key nunca é exibida na tela nem salva em logs.</p>
        </div>
    </div>
</x-layouts.app>
