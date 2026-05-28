<div class="{{ $colorClass }} rounded-xl p-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-sm text-gray-700 dark:text-gray-300">{{ $columnTitle }}</h3>
        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $posts->count() }}</span>
    </div>
    <div class="space-y-3">
        @forelse($posts as $post)
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <a href="{{ route('admin.blog.posts.show', $post) }}"
                       class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 leading-snug line-clamp-2">
                        {{ $post->title }}
                    </a>
                </div>

                <div class="text-xs text-gray-500 space-y-0.5 mb-3">
                    @if($post->focus_keyword)
                        <div>🔑 {{ $post->focus_keyword }}</div>
                    @endif
                    @if($post->author)
                        <div>👤 {{ $post->author->name }}</div>
                    @endif
                    @if($post->approver)
                        <div>✅ Aprovado por {{ $post->approver->name }}</div>
                    @endif
                    @if($post->scheduled_at)
                        <div>📅 {{ $post->scheduled_at->format('d/m H:i') }}</div>
                    @endif
                    @if($post->published_at)
                        <div>🟢 Pub. {{ $post->published_at->format('d/m H:i') }}</div>
                    @endif
                    @if($post->publication_error)
                        <div class="text-red-600 truncate">⚠️ {{ Str::limit($post->publication_error, 60) }}</div>
                    @endif
                    @if($post->aiEmployee)
                        <div>🤖 {{ $post->aiEmployee->name }}</div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-1">
                    <a href="{{ route('admin.blog.posts.edit', $post) }}"
                       class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200">
                        Editar
                    </a>

                    @if($post->canSubmitForApproval())
                        <form method="POST" action="{{ route('admin.blog.posts.submit-approval', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                Enviar aprovação
                            </button>
                        </form>
                    @endif

                    @if($post->canBeApproved())
                        <form method="POST" action="{{ route('admin.blog.posts.approve', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 hover:bg-green-200">
                                Aprovar
                            </button>
                        </form>
                        <button onclick="openRejectModal({{ $post->id }})"
                                class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 hover:bg-red-200">
                            Reprovar
                        </button>
                    @endif

                    @if($post->canBeScheduled())
                        <button onclick="openScheduleModal({{ $post->id }})"
                                class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800 hover:bg-purple-200">
                            Agendar
                        </button>
                        <form method="POST" action="{{ route('admin.blog.posts.publish-now', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200"
                                    onclick="return confirm('Publicar agora?')">
                                Publicar agora
                            </button>
                        </form>
                    @endif

                    @if(!$post->isArchived() && !$post->isDraft())
                        <form method="POST" action="{{ route('admin.blog.posts.archive', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-500 hover:bg-gray-200">
                                Arquivar
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.blog.posts.logs', $post) }}"
                       class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-500 hover:bg-gray-200">
                        Logs
                    </a>

                    @if($post->isPublished())
                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                           class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 hover:bg-green-200">
                            Ver post ↗
                        </a>
                    @endif

                    <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}"
                          onsubmit="return confirm('Excluir permanentemente o post «{{ addslashes($post->title) }}»? Esta ação não pode ser desfeita.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-2 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100 border border-red-200">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-xs text-center text-gray-400 py-4">{{ $emptyText }}</p>
        @endforelse
    </div>
</div>
