<x-layouts.app title="Editar Memória">

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai-memory.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Memórias</a>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Editar Memória</h1>
        </div>

        <form method="POST" action="{{ route('admin.ai-memory.update', $aiMemory) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo *</label>
                    <select name="memory_type" required class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                        @foreach(['brand_rule' => 'Regra de Marca', 'task_rule' => 'Regra de Tarefa', 'approved_pattern' => 'Padrão Aprovado', 'rejected_pattern' => 'Padrão Rejeitado', 'feedback' => 'Feedback', 'content_preference' => 'Preferência de Conteúdo', 'visual_preference' => 'Preferência Visual', 'performance_insight' => 'Insight de Performance', 'warning' => 'Aviso', 'general' => 'Geral'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('memory_type', $aiMemory->memory_type) === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
                    <input type="text" name="title" value="{{ old('title', $aiMemory->title) }}" required
                           class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conteúdo *</label>
                    <textarea name="content" rows="5" required
                              class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">{{ old('content', $aiMemory->content) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Peso (1–10)</label>
                        <input type="number" name="weight" value="{{ old('weight', $aiMemory->weight) }}" min="1" max="10"
                               class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relevância (0–100)</label>
                        <input type="number" name="relevance_score" value="{{ old('relevance_score', $aiMemory->relevance_score) }}" min="0" max="100"
                               class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                    Salvar
                </button>
                <a href="{{ route('admin.ai-memory.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
