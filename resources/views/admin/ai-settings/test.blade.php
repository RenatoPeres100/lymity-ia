<x-layouts.app>
    <x-slot name="title">Testar Conexão IA</x-slot>

    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai-settings.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Testar Provider de IA</h1>
                <p class="text-sm text-gray-500">Verifique a conexão e gere uma resposta de teste</p>
            </div>
        </div>

        {{-- Connection result (after test) --}}
        @if (isset($result))
            <div class="rounded-xl border p-5 {{ $result['success'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                <div class="flex items-start gap-3">
                    <span class="text-xl">{{ $result['success'] ? '✅' : '❌' }}</span>
                    <div>
                        <div class="font-semibold {{ $result['success'] ? 'text-green-800' : 'text-red-800' }}">
                            {{ $result['success'] ? 'Conexão bem-sucedida' : 'Falha na conexão' }}
                        </div>
                        <div class="text-sm mt-1 {{ $result['success'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $result['message'] }}
                        </div>
                        @if ($result['latency_ms'])
                            <div class="text-xs text-gray-500 mt-1">Latência: {{ $result['latency_ms'] }}ms</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Generation result --}}
        @if (isset($generationResult))
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Resultado da Geração</h3>

                @if ($generationResult['success'])
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-4 text-sm">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✅ Sucesso</span>
                            <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded font-mono text-xs">
                                {{ $generationResult['provider'] }} / {{ $generationResult['model'] }}
                            </span>
                            @if ($generationResult['total_tokens'])
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    🔢 {{ number_format($generationResult['total_tokens']) }} tokens
                                </span>
                            @endif
                            @if ($generationResult['estimated_cost'] !== null)
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">
                                    💰 ${{ number_format($generationResult['estimated_cost'], 6) }} USD
                                </span>
                            @endif
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 mb-1 uppercase tracking-wide">Resposta</div>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-sm text-gray-800 dark:text-gray-200 max-h-64 overflow-y-auto whitespace-pre-wrap font-mono text-xs">{{ mb_substr($generationResult['response'], 0, 1500) }}{{ strlen($generationResult['response']) > 1500 ? '...' : '' }}</div>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
                        ❌ {{ $generationResult['error_message'] }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Test form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Configuração Atual</h2>

            <div class="grid grid-cols-2 gap-3 mb-5 text-sm">
                <div>
                    <span class="text-gray-500">Provider:</span>
                    <span class="ml-2 font-mono font-medium">{{ config('ai.provider', 'mock') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Modelo:</span>
                    <span class="ml-2 font-mono font-medium">{{ $model }}</span>
                </div>
                <div>
                    <span class="text-gray-500">API Key:</span>
                    @if ($hasApiKey)
                        <span class="ml-2 text-green-600 font-medium">Configurada ✅</span>
                    @else
                        <span class="ml-2 text-yellow-600 font-medium">Não configurada ⚠️</span>
                    @endif
                </div>
                <div>
                    <span class="text-gray-500">Real Habilitado:</span>
                    <span class="ml-2 {{ config('ai.real_enabled') ? 'text-green-600' : 'text-yellow-600' }} font-medium">
                        {{ config('ai.real_enabled') ? 'Sim ✅' : 'Não ⚠️' }}
                    </span>
                </div>
            </div>

            @if (!$hasApiKey && config('ai.provider', 'mock') !== 'mock')
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-3 text-sm mb-4">
                    ⚠️ Provider real configurado mas sem API key. Configure <code class="bg-yellow-100 px-1 rounded">AI_API_KEY</code> no <code>.env</code> do servidor.
                </div>
            @endif

            <form action="{{ route('admin.ai-settings.test.run') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Prompt de Teste (opcional)
                    </label>
                    <textarea name="test_prompt" rows="3"
                              placeholder="Deixe em branco para usar prompt padrão..."
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">{{ old('test_prompt') }}</textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" name="action" value="connection"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        🔌 Testar Conexão
                    </button>
                    <button type="submit" name="action" value="generation" formaction="{{ route('admin.ai-settings.test.run') }}"
                            onclick="this.form.elements['run_generation'].value='1'"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                        ✨ Testar Geração
                    </button>
                </div>
                <input type="hidden" name="run_generation" value="0">
            </form>
        </div>

        {{-- Security note --}}
        <div class="text-xs text-gray-400 bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
            🔒 A API key nunca é exibida nesta tela nem registrada em logs. Apenas tokens consumidos e custo estimado são registrados em <a href="{{ route('admin.ai-usage.index') }}" class="underline">AI Usage</a>.
        </div>
    </div>
</x-layouts.app>
