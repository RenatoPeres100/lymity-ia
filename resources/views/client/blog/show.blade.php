<x-layouts.app :title="$blogPost->title">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('client.blog.index') }}" class="hover:text-slate-700">Blog</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">{{ Str::limit($blogPost->title, 50) }}</span>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">
        @if($blogPost->featured_image)
        <img src="{{ $blogPost->featured_image }}" alt="{{ $blogPost->title }}"
            class="w-full h-56 object-cover rounded-2xl">
        @endif

        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-xl font-bold text-slate-900">{{ $blogPost->title }}</h1>
                @php $sc = ['draft'=>'badge-gray','pending_approval'=>'badge-yellow','approved'=>'badge-blue','published'=>'badge-green']; @endphp
                <span class="badge {{ $sc[$blogPost->status] ?? 'badge-gray' }}">{{ $blogPost->status_label }}</span>
            </div>

            @if($blogPost->seo_description)
            <p class="text-slate-500 text-sm mb-5 italic">{{ $blogPost->seo_description }}</p>
            @endif

            <div class="prose-content text-slate-700 leading-relaxed text-sm">
                {!! nl2br(e($blogPost->content)) !!}
            </div>

            @if($blogPost->tags && is_array($blogPost->tags) && count($blogPost->tags))
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($blogPost->tags as $tag)
                <span class="text-xs bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">#{{ $tag }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-5">
        <div class="card p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Informações</h3>
            <div class="space-y-2 text-sm">
                @if($blogPost->category)
                <div class="flex justify-between"><span class="text-slate-400">Categoria</span><span class="text-slate-700">{{ $blogPost->category->name }}</span></div>
                @endif
                @if($blogPost->published_at)
                <div class="flex justify-between"><span class="text-slate-400">Publicado</span><span class="text-slate-700">{{ $blogPost->published_at->format('d/m/Y') }}</span></div>
                @endif
                @if($blogPost->is_featured)
                <div class="flex justify-between"><span class="text-slate-400">Destaque</span><span class="badge badge-yellow">Sim</span></div>
                @endif
            </div>
        </div>

        @if($blogPost->status === 'pending_approval')
        <div class="card p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Revisão</h3>
            <p class="text-xs text-slate-500">Este post está aguardando sua aprovação. Leia com atenção antes de aprovar.</p>
            <form method="POST" action="{{ route('client.blog.approve', $blogPost) }}">
                @csrf
                <button type="submit" class="btn btn-success w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Aprovar Post
                </button>
            </form>
            <form method="POST" action="{{ route('client.blog.reject', $blogPost) }}">
                @csrf
                <button type="submit" class="btn btn-danger w-full justify-center"
                    onclick="return confirm('Reprovar o post?')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Reprovar
                </button>
            </form>
        </div>
        @endif

        <a href="{{ route('client.blog.index') }}" class="btn btn-secondary w-full justify-center">
            Voltar para o blog
        </a>
    </div>

</div>

</x-layouts.app>
