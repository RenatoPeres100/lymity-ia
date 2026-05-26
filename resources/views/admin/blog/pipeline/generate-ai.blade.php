<x-layouts.app title="Gerar Post com IA">

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.blog.pipeline.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Pipeline</a>
            <span class="text-gray-300">/</span>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Gerar Post com Blog Writer IA</h1>
        </div>

        {{-- Blocked: mock provider --}}
        @if($blocked)
            <div class="rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 p-6 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-amber-800 dark:text-amber-200 mb-1">Provider de IA não configurado</h3>
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            O sistema está rodando com <strong>AI_PROVIDER={{ $provider }}</strong> e <strong>AI_REAL_ENABLED={{ $realEnabled ? 'true' : 'false' }}</strong>.
                        </p>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-2">
                            Para gerar conteúdo real, configure no <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">.env</code>:
                        </p>
                        <pre class="mt-2 text-xs bg-amber-100 dark:bg-amber-900 rounded p-3 text-amber-900 dark:text-amber-100">AI_PROVIDER=openai
AI_API_KEY=sk-...
AI_REAL_ENABLED=true</pre>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-2">
                            Conteúdo mock/falso não será gerado no painel. Use "Criar Manual" para criar posts reais agora.
                        </p>
                        <a href="{{ route('admin.blog.posts.create') }}"
                           class="inline-block mt-3 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium">
                            Criar Post Manual
                        </a>
                    </div>
                </div>
            </div>
        @else
            {{-- Form: only shown when provider is real --}}
            @if($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">
                    O Blog Writer IA irá gerar um artigo completo com estrutura SEO, H2/H3, meta description e CTA. O resultado será salvo como rascunho aguardando sua revisão.
                </p>

                <form method="POST" action="{{ route('admin.blog.pipeline.generate-ai.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tema do artigo <span class="text-red-500">*</span></label>
                        <input type="text" name="tema" value="{{ old('tema') }}" required
                               placeholder="Ex: Como a IA está transformando o marketing digital"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Palavra-chave principal <span class="text-red-500">*</span></label>
                        <input type="text" name="keyword" value="{{ old('keyword') }}" required
                               placeholder="Ex: ia marketing digital"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Palavras-chave secundárias</label>
                        <input type="text" name="keywords_sec" value="{{ old('keywords_sec') }}"
                               placeholder="automação de marketing, agência digital, conteúdo IA"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tom</label>
                            <select name="tom" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                                <option value="profissional">Profissional</option>
                                <option value="conversacional">Conversacional</option>
                                <option value="tecnico">Técnico</option>
                                <option value="inspirador">Inspirador</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Público-alvo</label>
                            <input type="text" name="publico" value="{{ old('publico') }}"
                                   placeholder="Gestores de marketing, PMEs"
                                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Objetivo do artigo</label>
                        <input type="text" name="objetivo" value="{{ old('objetivo') }}"
                               placeholder="Ex: Educar sobre IA, gerar leads, fortalecer autoridade"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CTA desejado</label>
                        <input type="text" name="cta" value="{{ old('cta') }}"
                               placeholder="Ex: Fale com um especialista, Solicite uma demonstração"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm">
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                            Gerar Artigo com IA
                        </button>
                        <a href="{{ route('admin.blog.pipeline.index') }}"
                           class="px-5 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        @endif
    </div>

</x-layouts.app>
