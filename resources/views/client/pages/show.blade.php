<x-layouts.app :title="$page->title">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('client.pages.index') }}" class="hover:text-slate-700">Páginas</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">{{ $page->title }}</span>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900">{{ $page->title }}</h2>
                @php $sc = ['draft'=>'badge-gray','pending_approval'=>'badge-yellow','approved'=>'badge-blue','published'=>'badge-green']; @endphp
                <span class="badge {{ $sc[$page->status] ?? 'badge-gray' }}">{{ $page->status_label }}</span>
            </div>

            @if($page->content)
            <div class="prose-content text-sm text-slate-700 leading-relaxed">
                {!! nl2br(e($page->content)) !!}
            </div>
            @else
            <p class="text-slate-400 text-sm italic">Sem conteúdo ainda.</p>
            @endif
        </div>

        @if($page->seo_title || $page->seo_description)
        <div class="card p-5">
            <h3 class="font-semibold text-slate-700 mb-3 text-sm">SEO</h3>
            @if($page->seo_title)
            <div class="mb-2">
                <div class="text-xs text-slate-400 mb-1">Título</div>
                <div class="text-sm text-slate-700">{{ $page->seo_title }}</div>
            </div>
            @endif
            @if($page->seo_description)
            <div>
                <div class="text-xs text-slate-400 mb-1">Descrição</div>
                <div class="text-sm text-slate-700">{{ $page->seo_description }}</div>
            </div>
            @endif
        </div>
        @endif
    </div>

    <div class="space-y-5">
        <div class="card p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Informações</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Tipo</span><span class="text-slate-700">{{ $page->page_type_label }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Slug</span><span class="text-slate-700">/{{ $page->slug }}</span></div>
                @if($page->published_at)
                <div class="flex justify-between"><span class="text-slate-400">Publicado</span><span class="text-slate-700">{{ $page->published_at->format('d/m/Y') }}</span></div>
                @endif
                @if($page->approver)
                <div class="flex justify-between"><span class="text-slate-400">Aprovado por</span><span class="text-slate-700">{{ $page->approver->name }}</span></div>
                @endif
            </div>
        </div>

        @if($page->status === 'pending_approval')
        <div class="card p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Revisão</h3>
            <p class="text-xs text-slate-500">Esta página está aguardando sua aprovação. Revise o conteúdo e decida.</p>
            <form method="POST" action="{{ route('client.pages.approve', $page) }}">
                @csrf
                <button type="submit" class="btn btn-success w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Aprovar Página
                </button>
            </form>
            <form method="POST" action="{{ route('client.pages.reject', $page) }}">
                @csrf
                <button type="submit" class="btn btn-danger w-full justify-center"
                    onclick="return confirm('Reprovar a página?')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Reprovar
                </button>
            </form>
        </div>
        @endif

        <a href="{{ route('client.pages.index') }}" class="btn btn-secondary w-full justify-center">
            Voltar para páginas
        </a>
    </div>

</div>

</x-layouts.app>
