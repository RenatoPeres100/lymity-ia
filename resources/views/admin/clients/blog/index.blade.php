<x-layouts.app :title="'Blog do Cliente — ' . $client->name">

<div class="mb-6 flex items-start justify-between flex-wrap gap-4">
    <div>
        <div class="flex items-center gap-3 text-sm text-slate-500 mb-1">
            <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
            <span>/</span>
            <span class="text-slate-700 font-medium">{{ $client->name }}</span>
            <span>/</span>
            <span class="text-slate-900 font-semibold">Blog</span>
        </div>
        <h2 class="text-xl font-bold text-slate-900">Blog do Cliente</h2>
    </div>
    <a href="{{ route('admin.clients.blog.create', $client) }}" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Novo Post
    </a>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Publicado</th>
                <th class="text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            <tr>
                <td>
                    <div class="font-medium text-slate-800">{{ $post->title }}</div>
                    <div class="text-xs text-slate-400">/blog/{{ $post->slug }}</div>
                </td>
                <td>
                    @if($post->category)
                    <span class="badge badge-blue">{{ $post->category->name }}</span>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td>
                    @php
                        $sc = ['draft'=>'badge-gray','pending_approval'=>'badge-yellow','approved'=>'badge-blue','published'=>'badge-green'];
                    @endphp
                    <span class="badge {{ $sc[$post->status] ?? 'badge-gray' }}">{{ $post->status_label }}</span>
                </td>
                <td class="text-xs text-slate-400">{{ $post->published_at?->format('d/m/Y') ?? '—' }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-2">
                        @if($post->status === 'pending_approval')
                        <form method="POST" action="{{ route('admin.clients.blog.approve', [$client, $post]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success" title="Aprovar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.clients.blog.reject', [$client, $post]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-danger" title="Reprovar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </form>
                        @endif
                        @if($post->status === 'approved')
                        <form method="POST" action="{{ route('admin.clients.blog.publish', [$client, $post]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-primary" title="Publicar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('admin.clients.blog.edit', [$client, $post]) }}" class="btn btn-xs btn-secondary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.clients.blog.destroy', [$client, $post]) }}"
                            onsubmit="return confirm('Excluir post?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-12 text-center text-slate-400">
                    <div class="text-sm font-medium">Nenhum post criado</div>
                    <a href="{{ route('admin.clients.blog.create', $client) }}" class="inline-flex mt-3 text-sm text-blue-500 hover:underline">Criar primeiro post</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($posts->hasPages())
    <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 text-sm text-slate-500">
        <span>{{ $posts->firstItem() }}–{{ $posts->lastItem() }} de {{ $posts->total() }}</span>
        {{ $posts->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
