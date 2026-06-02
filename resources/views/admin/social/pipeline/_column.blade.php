<div class="{{ $colorClass }} rounded-xl p-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-sm text-gray-700 dark:text-gray-300">{{ $columnTitle }}</h3>
        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $posts->count() }}</span>
    </div>
    <div class="space-y-3">
        @forelse($posts as $post)
            @php
                $imageValid    = $post->image_validation_status === 'valid' && !empty($post->public_image_url);
                $imageOutdated = !$imageValid && !empty($post->image_generated_from_caption_hash)
                    && $post->image_generated_from_caption_hash !== md5(implode('|', [
                        $post->title ?? '', $post->main_caption ?? '', $post->objective ?? '', $post->content_type ?? '',
                    ]));
                $hasCaption = !empty($post->main_caption);
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">

                {{-- Image preview --}}
                @if(!empty($post->public_image_url))
                    <div class="mb-2 relative">
                        <img src="{{ $post->public_image_url }}" alt="preview"
                             class="w-full h-24 object-cover rounded-md {{ $imageOutdated ? 'opacity-60' : '' }}">
                        @if($imageOutdated)
                            <div class="absolute inset-0 flex items-center justify-center bg-yellow-500/20 rounded-md">
                                <span class="text-xs font-semibold text-yellow-800 bg-yellow-100 px-2 py-0.5 rounded">Desatualizada</span>
                            </div>
                        @elseif($imageValid)
                            <span class="absolute top-1 right-1 text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded">✓ válida</span>
                        @else
                            <span class="absolute top-1 right-1 text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded">inválida</span>
                        @endif
                    </div>
                @endif

                <div class="flex items-start justify-between gap-2 mb-2">
                    <a href="{{ route('admin.social.posts.show', $post) }}"
                       class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 leading-snug line-clamp-2">
                        {{ $post->title ?: 'Post sem título' }}
                    </a>
                </div>

                <div class="text-xs text-gray-500 space-y-0.5 mb-3">
                    @if($post->objective)
                        <div>🎯 {{ Str::limit($post->objective, 40) }}</div>
                    @endif
                    @if($post->content_type)
                        <div>📌 {{ $post->content_type }}</div>
                    @endif
                    @if($post->creative_format)
                        <div>🎨 {{ $post->creative_format }}</div>
                    @endif
                    @if($post->aiEmployee)
                        <div>🤖 {{ $post->aiEmployee->name }}</div>
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
                    @if(!$imageValid && $post->status === 'approved')
                        <div class="text-orange-600">⚠️ Imagem inválida — não pode agendar</div>
                    @endif
                </div>

                {{-- Image actions --}}
                <div class="flex flex-wrap gap-1 mb-2">
                    @if(!empty($post->public_image_url))
                        <form method="POST" action="{{ route('admin.social.posts.validate-image', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-blue-50 text-blue-700 hover:bg-blue-100">
                                Validar imagem
                            </button>
                        </form>
                        <a href="{{ route('admin.social.posts.edit', $post) }}"
                           class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600 hover:bg-gray-200">
                            Trocar imagem
                        </a>
                    @else
                        @if($hasCaption)
                            <form method="POST" action="{{ route('admin.social.posts.generate-image', $post) }}">
                                @csrf
                                <button class="px-2 py-1 text-xs rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                                    ✨ Gerar imagem IA
                                </button>
                            </form>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-orange-50 text-orange-600 border border-orange-200"
                                  title="Crie o texto antes de gerar imagem">
                                Sem legenda
                            </span>
                        @endif
                    @endif
                    @if($imageOutdated && $hasCaption)
                        <form method="POST" action="{{ route('admin.social.posts.generate-image', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                ↺ Regen. imagem
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Main actions --}}
                <div class="flex flex-wrap gap-1">
                    <a href="{{ route('admin.social.posts.edit', $post) }}"
                       class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200">
                        Editar
                    </a>

                    @if(in_array($post->status, ['draft', 'rejected']) && $hasCaption)
                        <form method="POST" action="{{ route('admin.social.posts.send-approval', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                Enviar aprovação
                            </button>
                        </form>
                    @endif

                    @if($post->status === 'pending_approval')
                        <form method="POST" action="{{ route('admin.social.posts.approve', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 hover:bg-green-200">
                                Aprovar
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.social.posts.reject', $post) }}">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 hover:bg-red-200"
                                    onclick="return confirm('Reprovar este post?')">
                                Reprovar
                            </button>
                        </form>
                    @endif

                    @if($post->status === 'approved' && $imageValid && $hasCaption)
                        <button onclick="openScheduleModal({{ $post->id }}, '{{ $post->scheduled_at ? $post->scheduled_at->format('Y-m-d H:i:s') : '' }}')"
                                class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800 hover:bg-purple-200">
                            Agendar
                        </button>
                    @endif

                    @if(in_array($post->status, ['approved', 'scheduled']))
                        <form method="POST" action="{{ route('admin.social.posts.publish-now', $post) }}"
                              onsubmit="return confirm('Publicar no Instagram agora?')">
                            @csrf
                            <button class="px-2 py-1 text-xs rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                                Publicar agora
                            </button>
                        </form>
                    @endif

                    @if($post->permalink)
                        <a href="{{ $post->permalink }}" target="_blank"
                           class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 hover:bg-green-200">
                            Ver ↗
                        </a>
                    @endif

                    <form method="POST" action="{{ route('admin.social.posts.destroy', $post) }}"
                          onsubmit="return confirm('Excluir post «{{ addslashes($post->title ?? 'sem título') }}»?')">
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
