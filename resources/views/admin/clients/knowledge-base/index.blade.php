<x-layouts.app :title="'Base de Conhecimento — ' . $client->name">

<div class="mb-6 flex items-start justify-between flex-wrap gap-4">
    <div>
        <div class="flex items-center gap-3 text-sm text-slate-500 mb-1">
            <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
            <span>/</span>
            <span class="text-slate-700 font-medium">{{ $client->name }}</span>
            <span>/</span>
            <span class="text-slate-900 font-semibold">Base de Conhecimento</span>
        </div>
        <h2 class="text-xl font-bold text-slate-900">Base de Conhecimento</h2>
    </div>
    <a href="{{ route('admin.clients.knowledge-base.create', $client) }}" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Novo Item
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
                <th>Fonte</th>
                <th>Status</th>
                <th>Atualizado</th>
                <th class="text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
            <tr>
                <td>
                    <div class="font-medium text-slate-800">{{ $entry->title }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ Str::limit(strip_tags($entry->content), 80) }}</div>
                </td>
                <td class="text-sm text-slate-500">{{ $entry->source ?: '—' }}</td>
                <td>
                    @php $sc = ['active'=>'badge-green','draft'=>'badge-gray','archived'=>'badge-gray']; @endphp
                    <span class="badge {{ $sc[$entry->status] ?? 'badge-gray' }}">{{ $entry->status_label }}</span>
                </td>
                <td class="text-xs text-slate-400">{{ $entry->updated_at->format('d/m/Y') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.clients.knowledge-base.edit', [$client, $entry]) }}" class="btn btn-xs btn-secondary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.clients.knowledge-base.destroy', [$client, $entry]) }}"
                            onsubmit="return confirm('Excluir item?')">
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
                    <div class="text-sm font-medium">Nenhum item na base de conhecimento</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
