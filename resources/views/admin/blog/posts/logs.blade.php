<x-layouts.app title="Logs — {{ $blogPost->title }}">

    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.blog.posts.show', $blogPost) }}" class="text-sm text-gray-500 hover:text-gray-700">← Post</a>
            <span class="text-gray-300">/</span>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Logs do Post</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $blogPost->title }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Status atual: <strong>{{ $blogPost->status_label }}</strong></p>
        </div>

        <div class="space-y-2">
            @forelse($logs as $log)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->action }}</span>
                        <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $log->description }}</p>
                    @if($log->metadata && count($log->metadata) > 1)
                        <div class="mt-1 text-xs text-gray-400 font-mono bg-gray-50 dark:bg-gray-900 rounded px-2 py-1">
                            {{ json_encode(array_diff_key($log->metadata, ['blog_post_id' => '', 'status' => ''])) }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 text-sm">Nenhum log registrado para este post.</div>
            @endforelse
        </div>
    </div>

</x-layouts.app>
