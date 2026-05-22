<x-layouts.app :title="'Logs — ' . $client->name">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <span class="text-slate-700 font-medium">{{ $client->name }}</span>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Logs de Atividade</span>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Ação</th>
                <th>Módulo</th>
                <th>Descrição</th>
                <th>Usuário</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-xs text-slate-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @php
                        $actionColors = [
                            'created'  => 'badge-green',
                            'updated'  => 'badge-blue',
                            'deleted'  => 'badge-danger',
                            'approved' => 'badge-green',
                            'rejected' => 'badge-yellow',
                            'published'=> 'badge-blue',
                        ];
                    @endphp
                    <span class="badge {{ $actionColors[$log->action] ?? 'badge-gray' }}">{{ $log->action }}</span>
                </td>
                <td class="text-xs text-slate-500">{{ $log->module ?: '—' }}</td>
                <td class="text-sm text-slate-700 max-w-sm">{{ $log->description }}</td>
                <td class="text-sm text-slate-500">{{ $log->user?->name ?? 'Sistema' }}</td>
                <td class="text-xs text-slate-400">{{ $log->ip_address ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-12 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <div class="text-sm font-medium">Nenhum log registrado</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
    <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 text-sm text-slate-500">
        <span>{{ $logs->firstItem() }}–{{ $logs->lastItem() }} de {{ $logs->total() }}</span>
        {{ $logs->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
