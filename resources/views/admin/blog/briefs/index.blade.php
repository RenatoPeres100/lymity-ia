<x-layouts.app title="Briefings de Blog">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Briefings</h1>
            <p class="text-sm text-gray-500 mt-1">Pautas e briefings gerados pelo Blog Planner IA</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.blog.briefs.create') }}" class="btn-secondary text-sm">Novo manual</a>
            <a href="{{ route('admin.blog.automation.index') }}" class="btn-primary text-sm">Gerar com IA</a>
        </div>
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
            @if($briefs->isEmpty())
                <div class="py-12 text-center text-gray-400">
                    <p>Nenhum briefing encontrado.</p>
                    <a href="{{ route('admin.blog.automation.index') }}" class="mt-2 inline-block text-blue-600 hover:underline text-sm">Gerar o primeiro briefing com IA</a>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Título</th>
                            <th class="px-4 py-3 text-left">Keyword</th>
                            <th class="px-4 py-3 text-left">Funil</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Agente</th>
                            <th class="px-4 py-3 text-left">Criado</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($briefs as $brief)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.blog.briefs.show', $brief) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                    {{ $brief->title }}
                                </a>
                                <p class="text-xs text-gray-400">{{ Str::limit($brief->topic, 60) }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs font-mono">{{ $brief->primary_keyword ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge badge-blue text-xs">{{ $brief->funnel_stage }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge {{ match($brief->status) {
                                    'generated','approved' => 'badge-green',
                                    'used' => 'badge-blue',
                                    'archived' => 'badge-gray',
                                    default => 'badge-yellow'
                                } }} text-xs">{{ $brief->status_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $brief->generatedByAgent?->name ?? 'Manual' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $brief->created_at->format('d/m H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.blog.briefs.show', $brief) }}" class="btn-sm btn-secondary">Ver</a>
                                    @if($brief->isUsable())
                                    <form method="POST" action="{{ route('admin.blog.briefs.generate-draft', $brief) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-sm btn-primary">Gerar artigo</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $briefs->links() }}
                </div>
            @endif
        </div>
    </div>

</x-layouts.app>
