<x-layouts.app title="Nova Tarefa Operacional">

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agent-tasks.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Tarefas</a>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Nova Tarefa Operacional</h1>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.agent-tasks.store') }}" class="space-y-6">
            @csrf
            @include('admin.agent-tasks._form', ['task' => null])
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                    Criar Tarefa
                </button>
                <a href="{{ route('admin.agent-tasks.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
