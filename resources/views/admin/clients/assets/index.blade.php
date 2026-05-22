<x-layouts.app :title="'Assets — ' . $client->name">

<div class="mb-6 flex items-start justify-between flex-wrap gap-4">
    <div>
        <div class="flex items-center gap-3 text-sm text-slate-500 mb-1">
            <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
            <span>/</span>
            <span class="text-slate-700 font-medium">{{ $client->name }}</span>
            <span>/</span>
            <span class="text-slate-900 font-semibold">Assets</span>
        </div>
        <h2 class="text-xl font-bold text-slate-900">Arquivos e Assets</h2>
    </div>
    <a href="{{ route('admin.clients.assets.create', $client) }}" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Novo Asset
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
                <th>Nome</th>
                <th>Tipo</th>
                <th>Origem</th>
                <th>URL</th>
                <th>Cadastrado</th>
                <th class="text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            <tr>
                <td>
                    <div class="font-medium text-slate-800">{{ $asset->name }}</div>
                    @if($asset->notes)
                    <div class="text-xs text-slate-400">{{ Str::limit($asset->notes, 60) }}</div>
                    @endif
                </td>
                <td><span class="badge badge-blue">{{ $asset->type_label }}</span></td>
                <td><span class="badge badge-gray">{{ $asset->source_label }}</span></td>
                <td>
                    @if($asset->url)
                    <a href="{{ $asset->url }}" target="_blank" class="text-blue-500 hover:underline text-sm truncate max-w-xs block">
                        {{ Str::limit($asset->url, 40) }}
                    </a>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="text-xs text-slate-400">{{ $asset->created_at->format('d/m/Y') }}</td>
                <td class="text-right">
                    <form method="POST" action="{{ route('admin.clients.assets.destroy', [$client, $asset]) }}"
                        onsubmit="return confirm('Excluir asset?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-12 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                    <div class="text-sm font-medium">Nenhum asset cadastrado</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
