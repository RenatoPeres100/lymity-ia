<x-layouts.app title="Revisar: {{ $post?->title }}">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.blog.approvals.index') }}" class="text-sm text-blue-600 hover:underline">← Aprovações</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $post?->title }}</h1>
        </div>
        <span class="badge {{ match($approvalRequest->status) {
            'pending' => 'badge-yellow',
            'approved' => 'badge-green',
            'rejected' => 'badge-danger',
            default => 'badge-blue'
        } }}">{{ $approvalRequest->status }}</span>
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
                <div class="card-header"><h3 class="font-semibold text-gray-900 text-sm">Metadados</h3></div>
                <div class="card-body space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 uppercase">SEO Title</span>
                        <p class="text-gray-800">{{ $post?->seo_title ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase">SEO Description</span>
                        <p class="text-gray-700 text-xs">{{ $post?->seo_description ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase">Keyword principal</span>
                        <p class="font-mono text-gray-800">{{ $post?->focus_keyword ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase">Agente</span>
                        <p class="text-gray-800">{{ $approvalRequest->aiEmployee?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase">Modelo</span>
                        <p class="font-mono text-xs text-gray-500">{{ $approvalRequest->payload['model'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase">Tokens</span>
                        <p class="text-gray-800">
                            {{ number_format(($approvalRequest->payload['input_tokens'] ?? 0) + ($approvalRequest->payload['output_tokens'] ?? 0)) }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 uppercase">Custo estimado</span>
                        <p class="text-gray-800">{{ isset($approvalRequest->payload['estimated_cost_usd']) ? '$'.number_format($approvalRequest->payload['estimated_cost_usd'],5) : '—' }}</p>
                    </div>
                </div>
            </div>

            @if($approvalRequest->status === 'pending')
            <div class="card border-green-200">
                <div class="card-header bg-green-50"><h3 class="font-semibold text-green-900 text-sm">Aprovar</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.blog.approvals.approve', $approvalRequest) }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full">Aprovar Artigo</button>
                    </form>
                </div>
            </div>

            <div class="card border-red-200">
                <div class="card-header bg-red-50"><h3 class="font-semibold text-red-900 text-sm">Reprovar</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.blog.approvals.reject', $approvalRequest) }}">
                        @csrf
                        <textarea name="reason" class="form-input text-sm mb-3" rows="2" placeholder="Motivo (opcional)"></textarea>
                        <button type="submit" class="btn-danger w-full">Reprovar</button>
                    </form>
                </div>
            </div>
            @endif

            @if($post && $post->canBeScheduled())
            <div class="card border-blue-200">
                <div class="card-header bg-blue-50">
                    <h3 class="font-semibold text-blue-900 text-sm">
                        {{ $post->isScheduled() ? 'Editar Agendamento' : 'Agendar Publicação' }}
                    </h3>
                    @if($post->isScheduled())
                    <p class="text-xs text-blue-600 mt-0.5">
                        Atual: {{ $post->scheduled_at->format('d/m/Y H:i') }}
                    </p>
                    @endif
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.blog.posts.schedule-new', $post) }}">
                        @csrf
                        <label class="form-label text-xs">Nova data e hora</label>
                        <input type="datetime-local" name="scheduled_at" class="form-input mb-3"
                            min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}"
                            value="{{ $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : '' }}"
                            required>
                        <button type="submit" class="btn-primary w-full">
                            {{ $post->isScheduled() ? 'Salvar Agendamento' : 'Agendar' }}
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($post)
            <div class="card">
                <div class="card-body text-sm space-y-2">
                    <a href="{{ route('admin.blog.posts.edit', $post) }}" class="text-blue-600 hover:underline block">Editar artigo</a>
                    <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}"
                          onsubmit="return confirm('Excluir permanentemente este artigo? Esta ação não pode ser desfeita.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-sm">Excluir artigo</button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Article preview --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-900">Preview do Artigo</h3>
                    @if($post?->excerpt)
                    <p class="text-sm text-gray-500 mt-1">{{ $post->excerpt }}</p>
                    @endif
                </div>
                <div class="card-body">
                    @if($post?->content)
                    <div class="prose prose-sm max-w-none text-gray-800 overflow-auto max-h-[600px]">
                        {!! $post->content !!}
                    </div>
                    @else
                    <p class="text-gray-400 text-sm">Sem conteúdo.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
