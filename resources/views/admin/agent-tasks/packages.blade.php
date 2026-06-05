<x-layouts.app title="Pacotes Gerados — {{ $agentTask->title }}">

    <div class="max-w-6xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agent-tasks.show', $agentTask) }}" class="text-gray-500 hover:text-gray-700 text-sm">← {{ $agentTask->title }}</a>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pacotes de Conteúdo</h1>
        </div>

        @if($packages->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-10 text-center text-gray-500">
                Nenhum pacote gerado ainda.
            </div>
        @else
            <div class="space-y-4">
                @foreach($packages as $package)
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $package->title }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $package->package_type }} • {{ $package->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            @php $sc = $package->status_color; @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $sc === 'green' ? 'bg-green-100 text-green-800' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-800' : ($sc === 'red' ? 'bg-red-100 text-red-800' : ($sc === 'blue' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'))) }}">
                                {{ $package->status_label }}
                            </span>
                        </div>

                        @if($package->summary)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $package->summary }}</p>
                        @endif

                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                            @if($package->approvalRequest)
                                <a href="{{ route('admin.approvals.show', $package->approval_request_id) }}"
                                   class="text-indigo-600 hover:underline">
                                    Ver Aprovação #{{ $package->approval_request_id }}
                                </a>
                            @endif
                            @if($package->visual_payload)
                                @php $vs = $package->getVisualStatus(); @endphp
                                <span class="{{ $vs === 'generated' ? 'text-green-600' : ($vs === 'pending_configuration' ? 'text-amber-600' : 'text-gray-500') }}">
                                    Imagem: {{ $vs === 'generated' ? 'Gerada' : ($vs === 'pending_configuration' ? 'Provider não configurado' : $vs) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $packages->links() }}</div>
        @endif
    </div>

</x-layouts.app>
