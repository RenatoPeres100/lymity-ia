<x-layouts.app title="Pipeline do Blog">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pipeline do Blog</h1>
            <p class="text-sm text-gray-500 mt-0.5">Esteira operacional de publicação do blog da Lymity</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog.pipeline.generate-ai') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Gerar com Blog Writer IA
            </a>
            <a href="{{ route('admin.blog.posts.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 transition">
                + Criar Manual
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Pipeline columns --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

        {{-- 1. Rascunhos --}}
        @include('admin.blog.pipeline._column', [
            'columnTitle' => 'Rascunhos',
            'colorClass'  => 'bg-gray-100 dark:bg-gray-700',
            'badgeClass'  => 'bg-gray-200 text-gray-700',
            'posts'       => $drafts,
            'emptyText'   => 'Nenhum rascunho',
        ])

        {{-- 2. Aguardando aprovação --}}
        @include('admin.blog.pipeline._column', [
            'columnTitle' => 'Aguardando Aprovação',
            'colorClass'  => 'bg-yellow-50 dark:bg-yellow-900/20',
            'badgeClass'  => 'bg-yellow-100 text-yellow-800',
            'posts'       => $pendingApproval,
            'emptyText'   => 'Nenhum aguardando',
        ])

        {{-- 3. Aprovados --}}
        @include('admin.blog.pipeline._column', [
            'columnTitle' => 'Aprovados',
            'colorClass'  => 'bg-blue-50 dark:bg-blue-900/20',
            'badgeClass'  => 'bg-blue-100 text-blue-800',
            'posts'       => $approved,
            'emptyText'   => 'Nenhum aprovado',
        ])

        {{-- 4. Agendados --}}
        @include('admin.blog.pipeline._column', [
            'columnTitle' => 'Agendados',
            'colorClass'  => 'bg-purple-50 dark:bg-purple-900/20',
            'badgeClass'  => 'bg-purple-100 text-purple-800',
            'posts'       => $scheduled,
            'emptyText'   => 'Nenhum agendado',
        ])

        {{-- 5. Publicados --}}
        @include('admin.blog.pipeline._column', [
            'columnTitle' => 'Publicados',
            'colorClass'  => 'bg-green-50 dark:bg-green-900/20',
            'badgeClass'  => 'bg-green-100 text-green-800',
            'posts'       => $published,
            'emptyText'   => 'Nenhum publicado',
        ])

        {{-- 6. Falhas --}}
        @include('admin.blog.pipeline._column', [
            'columnTitle' => 'Falhas',
            'colorClass'  => 'bg-red-50 dark:bg-red-900/20',
            'badgeClass'  => 'bg-red-100 text-red-800',
            'posts'       => $failed,
            'emptyText'   => 'Sem falhas',
        ])

    </div>

    {{-- Modals --}}
    @include('admin.blog.pipeline._modals')

</x-layouts.app>
