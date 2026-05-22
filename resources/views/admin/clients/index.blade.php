<x-layouts.app title="Clientes">

<div class="mb-6 flex items-start justify-between flex-wrap gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Clientes</h2>
        <p class="text-sm text-slate-500 mt-1">Gerencie os clientes atendidos pela agência.</p>
    </div>
    <button onclick="alert('Criação de clientes será liberada na Fase 2.')"
        class="btn btn-primary opacity-75 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Novo cliente
        <span class="text-xs bg-blue-800/50 rounded px-1.5 py-0.5 ml-1">Fase 2</span>
    </button>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Segmento</th>
                <th>Website</th>
                <th>Instagram</th>
                <th>Status</th>
                <th>Empresa</th>
                <th>Cadastro</th>
                <th class="text-right">Gestão</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr>
                <td>
                    <div class="font-medium text-slate-800">{{ $client->name }}</div>
                    @if($client->legal_name && $client->legal_name !== $client->name)
                    <div class="text-xs text-slate-400">{{ $client->legal_name }}</div>
                    @endif
                </td>
                <td>
                    @if($client->segment)
                    <span class="badge badge-blue">{{ $client->segment }}</span>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td>
                    @if($client->website)
                    <a href="{{ $client->website }}" target="_blank" class="text-blue-500 hover:underline text-sm truncate max-w-xs block">
                        {{ $client->website }}
                    </a>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td>
                    @if($client->instagram)
                    <span class="text-sm text-slate-500">{{ $client->instagram }}</span>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $client->status === 'active' ? 'badge-green' : 'badge-gray' }}">
                        {{ $client->status === 'active' ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="text-sm text-slate-500">{{ $client->company?->name ?? '—' }}</td>
                <td class="text-xs text-slate-400">{{ $client->created_at->format('d/m/Y') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('admin.clients.brand.show', $client) }}" class="btn btn-xs btn-secondary" title="Marca">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688z"/></svg>
                        </a>
                        <a href="{{ route('admin.clients.website.show', $client) }}" class="btn btn-xs btn-secondary" title="Website">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10z"/></svg>
                        </a>
                        <a href="{{ route('admin.clients.blog.index', $client) }}" class="btn btn-xs btn-secondary" title="Blog">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
                        </a>
                        <a href="{{ route('admin.clients.logs.index', $client) }}" class="btn btn-xs btn-secondary" title="Logs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>
                    <div class="text-sm font-medium">Nenhum cliente encontrado</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($clients->hasPages())
    <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 text-sm text-slate-500">
        <span>{{ $clients->firstItem() }}–{{ $clients->lastItem() }} de {{ $clients->total() }} clientes</span>
        {{ $clients->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
