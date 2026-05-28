<x-layouts.app title="{{ $blogPost->title }}">

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.blog.pipeline.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Pipeline</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">{{ $blogPost->title }}</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.blog.posts.edit', $blogPost) }}"
                   class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200">
                    Editar
                </a>
                <a href="{{ route('admin.blog.posts.logs', $blogPost) }}"
                   class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200">
                    Logs
                </a>
                <form method="POST" action="{{ route('admin.blog.posts.destroy', $blogPost) }}"
                      onsubmit="return confirm('Excluir permanentemente este post? Esta ação não pode ser desfeita.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 text-sm rounded-lg bg-red-50 text-red-600 hover:bg-red-100 border border-red-200">
                        Excluir
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ $blogPost->title }}</h1>
                    @if($blogPost->subtitle)
                        <p class="text-gray-500 mb-4">{{ $blogPost->subtitle }}</p>
                    @endif
                    @if($blogPost->excerpt)
                        <p class="text-sm text-gray-600 dark:text-gray-400 italic border-l-2 border-indigo-400 pl-3 mb-4">{{ $blogPost->excerpt }}</p>
                    @endif
                    <div class="prose dark:prose-invert max-w-none text-sm">
                        {!! $blogPost->content !!}
                    </div>
                </div>

                {{-- Publication error --}}
                @if($blogPost->publication_error)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                        <h4 class="font-semibold text-red-700 dark:text-red-400 text-sm mb-1">Erro de publicação</h4>
                        <p class="text-sm text-red-600 dark:text-red-300">{{ $blogPost->publication_error }}</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- Status --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Status</h3>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                        {{ match($blogPost->status) {
                            'published'        => 'bg-green-100 text-green-800',
                            'approved'         => 'bg-blue-100 text-blue-800',
                            'scheduled'        => 'bg-purple-100 text-purple-800',
                            'pending_approval' => 'bg-yellow-100 text-yellow-800',
                            'failed'           => 'bg-red-100 text-red-800',
                            'rejected'         => 'bg-red-100 text-red-800',
                            default            => 'bg-gray-100 text-gray-700',
                        } }}">
                        {{ $blogPost->status_label }}
                    </span>
                    <div class="mt-3 space-y-1 text-xs text-gray-500">
                        @if($blogPost->author)
                            <div>Autor: {{ $blogPost->author->name }}</div>
                        @endif
                        @if($blogPost->approver)
                            <div>Aprovado por: {{ $blogPost->approver->name }}</div>
                        @endif
                        @if($blogPost->approved_at)
                            <div>Em: {{ $blogPost->approved_at->format('d/m/Y H:i') }}</div>
                        @endif
                        @if($blogPost->scheduled_at)
                            <div>Agendado: {{ $blogPost->scheduled_at->format('d/m/Y H:i') }}</div>
                        @endif
                        @if($blogPost->published_at)
                            <div>Publicado: {{ $blogPost->published_at->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Ações</h3>
                    <div class="space-y-2">

                        @if($blogPost->canSubmitForApproval())
                            <form method="POST" action="{{ route('admin.blog.posts.submit-approval', $blogPost) }}">
                                @csrf
                                <button class="w-full px-3 py-2 text-xs rounded-lg bg-yellow-100 text-yellow-800 hover:bg-yellow-200 text-left font-medium">
                                    Enviar para aprovação
                                </button>
                            </form>
                        @endif

                        @if($blogPost->canBeApproved())
                            <form method="POST" action="{{ route('admin.blog.posts.approve', $blogPost) }}">
                                @csrf
                                <button class="w-full px-3 py-2 text-xs rounded-lg bg-green-100 text-green-800 hover:bg-green-200 text-left font-medium">
                                    Aprovar
                                </button>
                            </form>
                            <button onclick="openRejectModal({{ $blogPost->id }})"
                                    class="w-full px-3 py-2 text-xs rounded-lg bg-red-100 text-red-800 hover:bg-red-200 text-left font-medium">
                                Reprovar
                            </button>
                        @endif

                        @if($blogPost->canBeScheduled())
                            <button onclick="openScheduleModal({{ $blogPost->id }})"
                                    class="w-full px-3 py-2 text-xs rounded-lg bg-purple-100 text-purple-800 hover:bg-purple-200 text-left font-medium">
                                Agendar publicação
                            </button>
                            <form method="POST" action="{{ route('admin.blog.posts.publish-now', $blogPost) }}">
                                @csrf
                                <button class="w-full px-3 py-2 text-xs rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-left font-medium"
                                        onclick="return confirm('Publicar agora?')">
                                    Publicar agora
                                </button>
                            </form>
                        @endif

                        @if(!$blogPost->isArchived())
                            <form method="POST" action="{{ route('admin.blog.posts.archive', $blogPost) }}">
                                @csrf
                                <button class="w-full px-3 py-2 text-xs rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 text-left">
                                    Arquivar
                                </button>
                            </form>
                        @endif

                        @if($blogPost->isPublished())
                            <a href="{{ route('blog.show', $blogPost->slug) }}" target="_blank"
                               class="block w-full px-3 py-2 text-xs rounded-lg bg-green-600 text-white hover:bg-green-700 text-left font-medium">
                                Ver post público ↗
                            </a>
                        @endif
                    </div>
                </div>

                {{-- SEO --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">SEO</h3>
                    <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                        @if($blogPost->seo_title)
                            <div><span class="font-medium">Title:</span> {{ $blogPost->seo_title }}</div>
                        @endif
                        @if($blogPost->seo_description)
                            <div><span class="font-medium">Meta:</span> {{ $blogPost->seo_description }}</div>
                        @endif
                        @if($blogPost->focus_keyword)
                            <div><span class="font-medium">Keyword:</span> {{ $blogPost->focus_keyword }}</div>
                        @endif
                        @if($blogPost->slug)
                            <div><span class="font-medium">Slug:</span> {{ $blogPost->slug }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.blog.pipeline._modals')

</x-layouts.app>
