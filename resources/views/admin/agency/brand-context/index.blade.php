<x-layouts.app title="Contexto da Marca">

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Contexto da Marca</h1>
                <p class="text-sm text-gray-500 mt-0.5">Define a identidade, tom, serviços e diretrizes de conteúdo da Lymity IA para os agentes.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if(!$context)
            <div class="rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 p-4 text-sm text-amber-800 dark:text-amber-200">
                <strong>Atenção:</strong> O contexto da marca não foi configurado. Os agentes IA estão bloqueados para geração de conteúdo até que este formulário seja preenchido.
            </div>
        @elseif(!$context->isComplete())
            <div class="rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 p-4 text-sm text-amber-800 dark:text-amber-200">
                <strong>Atenção:</strong> Contexto incompleto. Preencha pelo menos: Nome da Marca, Posicionamento, Tom de Voz e Público-Alvo para liberar os agentes.
            </div>
        @else
            <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">
                Contexto da marca configurado. Agentes IA podem usar este contexto para gerar conteúdo (se o provider de IA estiver ativo).
            </div>
        @endif

        @php
            $isEdit = !!$context;
            $action = $isEdit
                ? route('admin.agency.brand-context.update')
                : route('admin.agency.brand-context.store');
        @endphp

        <form method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <h3 class="font-semibold text-gray-900 dark:text-white">Identidade da Marca</h3>

                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome da Marca <span class="text-red-500">*</span></label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $context?->brand_name) }}"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                               placeholder="Ex: Lymity IA" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Posicionamento <span class="text-red-500">*</span></label>
                        <textarea name="positioning" rows="3"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: Empresa de crescimento com IA, automação, conteúdo, tráfego e tecnologia aplicada a negócios.">{{ old('positioning', $context?->positioning) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tom de Voz <span class="text-red-500">*</span></label>
                        <textarea name="tone_of_voice" rows="2"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: estratégico, claro, especialista, moderno e orientado a resultado">{{ old('tone_of_voice', $context?->tone_of_voice) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Público-Alvo <span class="text-red-500">*</span></label>
                        <textarea name="target_audience" rows="2"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: empresas e empreendedores que querem crescer com tecnologia e IA">{{ old('target_audience', $context?->target_audience) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Principais Serviços</label>
                        <textarea name="main_services" rows="2"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: IA para conteúdo, blog, social media, automação, desenvolvimento, SEO">{{ old('main_services', $context?->main_services) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <h3 class="font-semibold text-gray-900 dark:text-white">Diretrizes de Conteúdo</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Termos Proibidos</label>
                        <textarea name="forbidden_terms" rows="3"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: agência comum, marketing genérico, promessa milagrosa">{{ old('forbidden_terms', $context?->forbidden_terms) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Termos Preferidos</label>
                        <textarea name="preferred_terms" rows="3"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: operação de crescimento, IA aplicada, aprovação humana, rastreabilidade">{{ old('preferred_terms', $context?->preferred_terms) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exemplos de CTA</label>
                        <textarea name="cta_examples" rows="3"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: Solicitar diagnóstico, Conhecer a operação, Falar com especialista">{{ old('cta_examples', $context?->cta_examples) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diretrizes de Conteúdo</label>
                        <textarea name="content_guidelines" rows="3"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: Conteúdos objetivos, profundos, estratégicos, sem promessas falsas">{{ old('content_guidelines', $context?->content_guidelines) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diretrizes Visuais</label>
                        <textarea name="visual_guidelines" rows="2"
                                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                  placeholder="Ex: Visual premium, limpo, tecnológico, com estética moderna e profissional">{{ old('visual_guidelines', $context?->visual_guidelines) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition">
                    {{ $isEdit ? 'Salvar Alterações' : 'Criar Contexto' }}
                </button>
            </div>
        </form>

    </div>

</x-layouts.app>
