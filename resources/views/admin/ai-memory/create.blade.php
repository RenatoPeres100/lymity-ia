<x-layouts.app title="Nova Memória IA">

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai-memory.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Memórias</a>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Nova Memória IA</h1>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.ai-memory.store') }}" class="space-y-4">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo *</label>
                    <select name="memory_type" required class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                        <option value="brand_rule">Regra de Marca</option>
                        <option value="task_rule">Regra de Tarefa</option>
                        <option value="approved_pattern">Padrão Aprovado</option>
                        <option value="rejected_pattern">Padrão Rejeitado</option>
                        <option value="feedback">Feedback</option>
                        <option value="content_preference">Preferência de Conteúdo</option>
                        <option value="visual_preference">Preferência Visual</option>
                        <option value="performance_insight">Insight de Performance</option>
                        <option value="warning">Aviso</option>
                        <option value="general">Geral</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white"
                           placeholder="Ex: Posts aprovados conectam IA com operação real">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conteúdo *</label>
                    <textarea name="content" rows="4" required
                              class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white"
                              placeholder="Descreva o padrão, regra, feedback ou insight...">{{ old('content') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Funcionário IA</label>
                        <select name="ai_employee_id" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                            <option value="">Geral</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" @selected(old('ai_employee_id') == $emp->id)>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tarefa Vinculada</label>
                        <select name="agent_task_id" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                            <option value="">Geral</option>
                            @foreach($tasks as $t)
                                <option value="{{ $t->id }}" @selected(old('agent_task_id') == $t->id)>{{ $t->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Peso (1–10)</label>
                        <input type="number" name="weight" value="{{ old('weight', 1) }}" min="1" max="10"
                               class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relevância (0–100)</label>
                        <input type="number" name="relevance_score" value="{{ old('relevance_score', 50) }}" min="0" max="100"
                               class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                    Criar Memória
                </button>
                <a href="{{ route('admin.ai-memory.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
