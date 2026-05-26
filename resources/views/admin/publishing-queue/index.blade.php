<x-layouts.app title="Fila de Publicação">

    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-4 text-sm">
            <span class="px-3 py-1.5 rounded-full bg-yellow-100 text-yellow-800 font-medium">
                {{ $totalPending }} pendentes
            </span>
            <span class="px-3 py-1.5 rounded-full bg-blue-100 text-blue-800 font-medium">
                {{ $totalScheduled }} agendados
            </span>
        </div>
        <a href="{{ route('admin.operation') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            ← Voltar à Operação
        </a>
    </div>

    {{-- Social Posts --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-900 dark:text-white">Posts Instagram / Social</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-750 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Conteúdo</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Agendado</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Criado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($socialPending as $post)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-5 py-3 max-w-xs">
                                <p class="text-gray-900 dark:text-white font-medium truncate">{{ Str::limit(($post->main_caption ?? $post->title ?? $post->content) ?? '(sem legenda)', 70) }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ optional($post->client)->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs bg-pink-100 text-pink-800">
                                    {{ $post->post_type ?? 'post' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ $post->scheduled_at ? $post->scheduled_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $post->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $post->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $post->created_at->format('d/m H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">Nenhum post social na fila</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($socialPending->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $socialPending->appends(['blog_page' => request('blog_page')])->links() }}
            </div>
        @endif
    </div>

    {{-- Blog Posts --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-900 dark:text-white">Posts de Blog</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-750 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Título</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Criado</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Publicado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($blogPending as $post)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-5 py-3 max-w-md">
                                <p class="text-gray-900 dark:text-white font-medium truncate">{{ $post->title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $post->status === 'pending_review' ? 'bg-yellow-100 text-yellow-700'
                                     : ($post->status === 'scheduled' ? 'bg-blue-100 text-blue-700'
                                     : 'bg-gray-100 text-gray-600') }}">
                                    {{ $post->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $post->created_at->format('d/m H:i') }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ $post->published_at ? $post->published_at->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-400">Nenhum post de blog na fila</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($blogPending->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $blogPending->appends(['social_page' => request('social_page')])->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
