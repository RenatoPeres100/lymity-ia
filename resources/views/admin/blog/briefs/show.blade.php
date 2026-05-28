<x-layouts.app title="Briefing: {{ $contentBrief->title }}">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.blog.briefs.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-1">
                ← Briefings
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $contentBrief->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $contentBrief->topic }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog.briefs.edit', $contentBrief) }}" class="btn-secondary text-sm">Editar</a>
            @if($contentBrief->isUsable())
            <form method="POST" action="{{ route('admin.blog.briefs.generate-draft', $contentBrief) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm">Gerar Artigo com IA</button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
            @foreach($errors->all() as $e)<p class="text-sm text-red-700">{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Meta --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="card">
                <div class="card-body space-y-3 text-sm">
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Status</span>
                        <div class="mt-1">
                            <span class="badge {{ match($contentBrief->status) {
                                'generated','approved' => 'badge-green',
                                'used' => 'badge-blue',
                                'archived' => 'badge-gray',
                                default => 'badge-yellow'
                            } }}">{{ $contentBrief->status_label }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Keyword Principal</span>
                        <p class="mt-1 font-mono text-gray-800">{{ $contentBrief->primary_keyword ?? '—' }}</p>
                    </div>
                    @if($contentBrief->secondary_keywords)
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Keywords Secundárias</span>
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($contentBrief->secondary_keywords as $kw)
                                <span class="badge badge-blue text-xs">{{ $kw }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Funil</span>
                        <p class="mt-1 text-gray-800">{{ $contentBrief->funnel_stage }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Intenção</span>
                        <p class="mt-1 text-gray-800">{{ $contentBrief->search_intent }}</p>
                    </div>
                    @if($contentBrief->generatedByAgent)
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Agente</span>
                        <p class="mt-1 text-gray-800">{{ $contentBrief->generatedByAgent->name }}</p>
                    </div>
                    @endif
                    @if(isset($contentBrief->metadata['estimated_cost_usd']))
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Custo estimado</span>
                        <p class="mt-1 text-gray-800">${{ number_format($contentBrief->metadata['estimated_cost_usd'], 5) }}</p>
                    </div>
                    @endif
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Criado em</span>
                        <p class="mt-1 text-gray-800">{{ $contentBrief->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="card-footer flex gap-2">
                    @if($contentBrief->status !== 'approved')
                    <form method="POST" action="{{ route('admin.blog.briefs.approve', $contentBrief) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn-primary w-full text-sm">Aprovar Briefing</button>
                    </form>
                    @endif
                    @if($contentBrief->status !== 'archived')
                    <form method="POST" action="{{ route('admin.blog.briefs.archive', $contentBrief) }}">
                        @csrf
                        <button type="submit" class="btn-secondary text-sm">Arquivar</button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Artigos gerados --}}
            @if($contentBrief->blogPosts->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900 text-sm">Artigos gerados</h3>
                </div>
                <div class="card-body space-y-2">
                    @foreach($contentBrief->blogPosts as $p)
                    <a href="{{ route('admin.blog.posts.show', $p) }}" class="block text-sm text-blue-600 hover:underline">
                        {{ $p->title }} <span class="badge badge-blue text-xs ml-1">{{ $p->status }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Details --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-gray-900">Briefing Completo</h3></div>
                <div class="card-body space-y-4 text-sm">
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Objetivo</span>
                        <p class="mt-1 text-gray-700">{{ $contentBrief->goal ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Público-Alvo</span>
                        <p class="mt-1 text-gray-700">{{ $contentBrief->audience ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">CTA Sugerido</span>
                        <p class="mt-1 text-gray-700">{{ $contentBrief->cta_suggestion ?? '—' }}</p>
                    </div>
                </div>
            </div>

            @if($contentBrief->outline)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-gray-900">Estrutura (Outline)</h3></div>
                <div class="card-body space-y-3">
                    @foreach($contentBrief->outline as $i => $section)
                    <div class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center">{{ $i+1 }}</span>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $section['heading'] ?? '' }}</p>
                            @if(!empty($section['notes']))
                            <p class="text-xs text-gray-500 mt-0.5">{{ $section['notes'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

</x-layouts.app>
