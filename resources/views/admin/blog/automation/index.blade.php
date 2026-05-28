<x-layouts.app title="Blog Automation — Gemini">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Blog Automation</h1>
            <p class="text-sm text-gray-500 mt-1">Pipeline de geração de conteúdo com Gemini (texto)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog.briefs.index') }}" class="btn-secondary text-sm">Briefings</a>
            <a href="{{ route('admin.blog.approvals.index') }}" class="btn-secondary text-sm">Aprovações</a>
            <a href="{{ route('admin.blog.pipeline.index') }}" class="btn-secondary text-sm">Pipeline</a>
            <a href="{{ route('admin.blog.ai-logs.index') }}" class="btn-secondary text-sm">Logs IA</a>
        </div>
    </div>

    {{-- Image disabled banner --}}
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-amber-700">
            <strong>Geração de imagem está desativada nesta fase.</strong> O foco atual é texto do blog.
        </p>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
            @foreach($errors->all() as $e)
                <p class="text-sm text-red-700">{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Coluna esquerda — Status --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Gemini Status --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Status Gemini</h3>
                </div>
                <div class="card-body space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Provider</span>
                        <span class="font-medium {{ $geminiConfigured ? 'text-green-600' : 'text-red-500' }}">
                            {{ $geminiConfigured ? 'Google Gemini' : 'Não configurado' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Modelo</span>
                        <span class="font-mono text-xs">{{ $geminiModel }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fallback</span>
                        <span class="font-mono text-xs">{{ $fallbackModel }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Text Only Mode</span>
                        <span class="{{ $textOnlyMode ? 'text-green-600' : 'text-red-500' }}">
                            {{ $textOnlyMode ? 'Ativo' : 'Desativado' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Image Pipeline</span>
                        <span class="{{ !$imagePipeline ? 'text-green-600' : 'text-red-500' }}">
                            {{ $imagePipeline ? 'Ativo (desativar)' : 'Desativado ✓' }}
                        </span>
                    </div>
                    @if(!$geminiConfigured)
                        <p class="mt-2 text-xs text-red-600 border-t border-red-100 pt-2">
                            Gemini não configurado. Verifique GEMINI_API_KEY e AI_PROVIDER=google no .env.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Uso / Limites --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Uso e Limites</h3>
                </div>
                <div class="card-body space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Hoje</span>
                        <span class="{{ $dailyUsage['exceeded'] ? 'text-red-600' : 'text-gray-900' }} font-medium">
                            {{ $dailyUsage['count'] }} / {{ $dailyUsage['limit'] }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full"
                             style="width: {{ $dailyUsage['limit'] > 0 ? min(100, round($dailyUsage['count']/$dailyUsage['limit']*100)) : 0 }}%"></div>
                    </div>
                    <div class="flex justify-between mt-2">
                        <span class="text-gray-500">Custo mensal est.</span>
                        <span class="font-medium {{ $monthlyUsage['exceeded'] ? 'text-red-600' : 'text-gray-900' }}">
                            ${{ number_format($monthlyUsage['estimated_cost_usd'], 4) }} / ${{ $monthlyUsage['budget_usd'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Brand Context --}}
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Brand Context</h3>
                    <a href="{{ route('admin.agency.brand-context.index') }}" class="text-xs text-blue-600 hover:underline">Editar</a>
                </div>
                <div class="card-body text-sm">
                    @if($brandCtx && $brandCtx->isComplete())
                        <div class="flex items-center gap-2 text-green-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="font-medium">{{ $brandCtx->brand_name }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($brandCtx->positioning, 80) }}</p>
                    @elseif($brandCtx)
                        <p class="text-amber-600 text-xs">Contexto incompleto. <a href="{{ route('admin.agency.brand-context.index') }}" class="underline">Completar</a></p>
                    @else
                        <p class="text-red-600 text-xs">Não configurado. <a href="{{ route('admin.agency.brand-context.index') }}" class="underline">Configurar agora</a></p>
                    @endif
                </div>
            </div>

            {{-- Agentes --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Agentes IA</h3>
                </div>
                <div class="card-body space-y-2 text-sm">
                    @foreach([['label'=>'Blog Planner IA','agent'=>$planner],['label'=>'Blog Writer IA','agent'=>$writer]] as $item)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700">{{ $item['label'] }}</span>
                            @if($item['agent'] && $item['agent']->status === 'active')
                                <span class="badge badge-green text-xs">Ativo</span>
                            @else
                                <span class="badge badge-danger text-xs">Inativo</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Coluna direita — Ações --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Gerar Briefing --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Gerar Briefing com Blog Planner IA</h3>
                    <p class="text-xs text-gray-500 mt-0.5">O Planner vai criar pauta, estrutura, palavras-chave e CTA.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.blog.automation.generate-brief') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="form-label">Tópico <span class="text-red-500">*</span></label>
                                <input type="text" name="topic" class="form-input"
                                    placeholder="Ex: Como a inteligência artificial está mudando o marketing"
                                    required maxlength="300">
                            </div>
                            <div>
                                <label class="form-label">Palavra-chave principal</label>
                                <input type="text" name="keyword" class="form-input"
                                    placeholder="Ex: agência digital com IA" maxlength="150">
                            </div>
                            <div>
                                <label class="form-label">Objetivo</label>
                                <input type="text" name="goal" class="form-input"
                                    placeholder="Ex: educar e posicionar como autoridade" maxlength="500">
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            @if(!$geminiConfigured)
                                <p class="text-xs text-red-600">Configure o Gemini antes de gerar.</p>
                                <button type="submit" disabled class="btn-primary opacity-50 cursor-not-allowed">Gerar Briefing</button>
                            @elseif(!$brandCtx || !$brandCtx->isComplete())
                                <p class="text-xs text-amber-600">Configure o Brand Context antes de gerar.</p>
                                <button type="submit" disabled class="btn-primary opacity-50 cursor-not-allowed">Gerar Briefing</button>
                            @else
                                <p class="text-xs text-gray-400">Usa 1 chamada à API Gemini (~$0.001)</p>
                                <button type="submit" class="btn-primary">Gerar Briefing com IA</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Logs recentes --}}
            @if($recentLogs->isNotEmpty())
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Últimos Logs de IA</h3>
                    <a href="{{ route('admin.blog.ai-logs.index') }}" class="text-xs text-blue-600 hover:underline">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Tarefa</th>
                                <th class="px-4 py-2 text-left">Modelo</th>
                                <th class="px-4 py-2 text-right">Tokens</th>
                                <th class="px-4 py-2 text-right">Custo est.</th>
                                <th class="px-4 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-700">{{ $log->task_type ?? '—' }}</td>
                                <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $log->model ?? '—' }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">
                                    {{ $log->input_tokens ? number_format(($log->input_tokens ?? 0) + ($log->output_tokens ?? 0)) : '—' }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-700">
                                    {{ $log->estimated_cost_usd ? '$'.number_format($log->estimated_cost_usd, 5) : '—' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="badge {{ $log->status === 'success' ? 'badge-green' : ($log->status === 'error' ? 'badge-danger' : 'badge-blue') }} text-xs">
                                        {{ $log->status ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

</x-layouts.app>
