<x-layouts.app title="Aprovações de Blog">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Aprovações de Blog</h1>
            <p class="text-sm text-gray-500 mt-1">Artigos gerados por IA aguardando revisão e aprovação</p>
        </div>
        <a href="{{ route('admin.blog.automation.index') }}" class="btn-secondary text-sm">← Automation</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
            @foreach($errors->all() as $e)<p class="text-sm text-red-700">{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if($approvals->isEmpty())
                <div class="py-12 text-center text-gray-400">
                    <p>Nenhuma aprovação pendente de blog.</p>
                    <a href="{{ route('admin.blog.automation.index') }}" class="mt-2 inline-block text-blue-600 hover:underline text-sm">Gerar novo artigo</a>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Artigo</th>
                            <th class="px-4 py-3 text-left">Agente</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Custo est.</th>
                            <th class="px-4 py-3 text-left">Solicitado</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($approvals as $approval)
                        @php $post = $approval->approvable @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.blog.approvals.show', $approval) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                    {{ $post?->title ?? 'Post removido' }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $post?->focus_keyword }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $approval->aiEmployee?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ match($approval->status) {
                                    'pending' => 'badge-yellow',
                                    'approved' => 'badge-green',
                                    'rejected' => 'badge-danger',
                                    default => 'badge-blue'
                                } }} text-xs">{{ $approval->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-gray-500">
                                {{ isset($approval->payload['estimated_cost_usd']) ? '$'.number_format($approval->payload['estimated_cost_usd'],5) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $approval->created_at->format('d/m H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.blog.approvals.show', $approval) }}" class="btn-sm btn-primary">Revisar</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-gray-100">{{ $approvals->links() }}</div>
            @endif
        </div>
    </div>

</x-layouts.app>
