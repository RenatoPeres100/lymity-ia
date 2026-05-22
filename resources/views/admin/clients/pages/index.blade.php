<x-layouts.app :title="'Páginas — ' . $client->name">

<div class="mb-6 flex items-start justify-between flex-wrap gap-4">
    <div>
        <div class="flex items-center gap-3 text-sm text-slate-500 mb-1">
            <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
            <span>/</span>
            <a href="{{ route('admin.clients.website.show', $client) }}" class="hover:text-slate-700">{{ $client->name }}</a>
            <span>/</span>
            <span class="text-slate-900 font-semibold">Páginas</span>
        </div>
        <h2 class="text-xl font-bold text-slate-900">Páginas do Website</h2>
    </div>
    <a href="{{ route('admin.clients.pages.create', $client) }}" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nova Página
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
                <th>Tipo</th>
                <th>Status</th>
                <th>Criado por</th>
                <th>Aprovado por</th>
                <th>Atualizado</th>
                <th class="text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pages as $page)
            <tr>
                <td>
                    <div class="font-medium text-slate-800">{{ $page->title }}</div>
                    <div class="text-xs text-slate-400">/{{ $page->slug }}</div>
                </td>
                <td><span class="badge badge-blue">{{ $page->page_type_label }}</span></td>
                <td>
                    @php
                        $statusColors = [
                            'draft'            => 'badge-gray',
                            'pending_approval' => 'badge-yellow',
                            'approved'         => 'badge-blue',
                            'published'        => 'badge-green',
                            'archived'         => 'badge-gray',
                        ];
                    @endphp
                    <span class="badge {{ $statusColors[$page->status] ?? 'badge-gray' }}">
                        {{ $page->status_label }}
                    </span>
                </td>
                <td class="text-sm text-slate-500">{{ $page->creator?->name ?? '—' }}</td>
                <td class="text-sm text-slate-500">{{ $page->approver?->name ?? '—' }}</td>
                <td class="text-xs text-slate-400">{{ $page->updated_at->format('d/m/Y') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-2">
                        @if($page->status === 'pending_approval')
                        <form method="POST" action="{{ route('admin.clients.pages.approve', [$client, $page]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success" title="Aprovar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.clients.pages.reject', [$client, $page]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-danger" title="Reprovar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </form>
                        @endif
                        @if($page->status === 'approved')
                        <form method="POST" action="{{ route('admin.clients.pages.publish', [$client, $page]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-primary" title="Publicar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('admin.clients.pages.edit', [$client, $page]) }}" class="btn btn-xs btn-secondary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.clients.pages.destroy', [$client, $page]) }}"
                            onsubmit="return confirm('Excluir página?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                    <div class="text-sm font-medium">Nenhuma página encontrada</div>
                    <a href="{{ route('admin.clients.pages.create', $client) }}" class="inline-flex mt-3 text-sm text-blue-500 hover:underline">Criar primeira página</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
