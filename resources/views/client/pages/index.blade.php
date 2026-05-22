<x-layouts.app title="Minhas Páginas">

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Páginas do Website</h2>
    <p class="text-sm text-slate-500 mt-1">Revise e aprove as páginas criadas para você.</p>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

@if($pages->isEmpty())
<div class="card p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
    <p class="text-slate-500 text-sm">Nenhuma página criada ainda.</p>
</div>
@else

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($pages as $page)
    @php
        $statusColors = ['draft'=>'badge-gray','pending_approval'=>'badge-yellow','approved'=>'badge-blue','published'=>'badge-green','archived'=>'badge-gray'];
    @endphp
    <div class="card p-5 flex flex-col gap-4">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h3 class="font-semibold text-slate-800">{{ $page->title }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">/{{ $page->slug }}</p>
            </div>
            <span class="badge {{ $statusColors[$page->status] ?? 'badge-gray' }} shrink-0">{{ $page->status_label }}</span>
        </div>

        @if($page->content)
        <p class="text-sm text-slate-500 leading-relaxed">{{ Str::limit(strip_tags($page->content), 120) }}</p>
        @endif

        <div class="flex items-center justify-between text-xs text-slate-400">
            <span>Tipo: {{ $page->page_type_label }}</span>
            <span>{{ $page->updated_at->format('d/m/Y') }}</span>
        </div>

        <div class="flex items-center gap-2 mt-auto pt-2 border-t border-slate-100">
            <a href="{{ route('client.pages.show', $page) }}" class="btn btn-xs btn-secondary flex-1 justify-center">
                Ver detalhes
            </a>
            @if($page->status === 'pending_approval')
            <form method="POST" action="{{ route('client.pages.approve', $page) }}" class="inline">
                @csrf
                <button type="submit" class="btn btn-xs btn-success" title="Aprovar">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </form>
            <form method="POST" action="{{ route('client.pages.reject', $page) }}" class="inline">
                @csrf
                <button type="submit" class="btn btn-xs btn-danger" title="Reprovar">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

@endif

</x-layouts.app>
