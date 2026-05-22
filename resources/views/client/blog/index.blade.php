<x-layouts.app title="Meu Blog">

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Blog</h2>
    <p class="text-sm text-slate-500 mt-1">Posts criados para o seu blog. Revise e aprove antes da publicação.</p>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

@if($posts->isEmpty())
<div class="card p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
    <p class="text-slate-500 text-sm">Nenhum post criado ainda.</p>
</div>
@else

<div class="space-y-4">
    @foreach($posts as $post)
    @php
        $sc = ['draft'=>'badge-gray','pending_approval'=>'badge-yellow','approved'=>'badge-blue','published'=>'badge-green'];
    @endphp
    <div class="card p-5 flex items-start gap-5">
        @if($post->featured_image)
        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}"
            class="w-24 h-16 object-cover rounded-lg shrink-0">
        @endif
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3 mb-1">
                <h3 class="font-semibold text-slate-800">{{ $post->title }}</h3>
                <span class="badge {{ $sc[$post->status] ?? 'badge-gray' }} shrink-0">{{ $post->status_label }}</span>
            </div>
            @if($post->category)
            <span class="badge badge-blue text-xs mb-2">{{ $post->category->name }}</span>
            @endif
            @if($post->content)
            <p class="text-sm text-slate-500 leading-relaxed">{{ Str::limit(strip_tags($post->content), 160) }}</p>
            @endif
            <div class="flex items-center justify-between mt-3">
                <span class="text-xs text-slate-400">{{ $post->updated_at->format('d/m/Y') }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('client.blog.show', $post) }}" class="btn btn-xs btn-secondary">Ver post</a>
                    @if($post->status === 'pending_approval')
                    <form method="POST" action="{{ route('client.blog.approve', $post) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-success">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Aprovar
                        </button>
                    </form>
                    <form method="POST" action="{{ route('client.blog.reject', $post) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-danger">Reprovar</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($posts->hasPages())
<div class="mt-5">{{ $posts->links() }}</div>
@endif

@endif

</x-layouts.app>
