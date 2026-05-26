<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">

    {{-- Agent --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agente IA <span class="text-red-500">*</span></label>
        <select name="ai_employee_id" required
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <option value="">Selecione o agente</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" @selected(old('ai_employee_id', $routine?->ai_employee_id) == $emp->id)>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Type --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Rotina <span class="text-red-500">*</span></label>
        <select name="routine_type" required
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <option value="">Selecione o tipo</option>
            <option value="social_post_creation" @selected(old('routine_type', $routine?->routine_type) === 'social_post_creation')>Criação de Post Social</option>
            <option value="blog_post_creation"   @selected(old('routine_type', $routine?->routine_type) === 'blog_post_creation')>Criação de Post Blog</option>
            <option value="copy_improvement"     @selected(old('routine_type', $routine?->routine_type) === 'copy_improvement')>Revisão de Copy</option>
            <option value="content_review"       @selected(old('routine_type', $routine?->routine_type) === 'content_review')>Revisão de Conteúdo</option>
        </select>
    </div>

    {{-- Title --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $routine?->title) }}" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
               placeholder="Ex: Criar post diário de Instagram">
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
        <textarea name="description" rows="2"
                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Detalhes sobre o que esta rotina faz">{{ old('description', $routine?->description) }}</textarea>
    </div>

    {{-- Frequency --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frequência <span class="text-red-500">*</span></label>
            <select name="frequency" required
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="daily"   @selected(old('frequency', $routine?->frequency) === 'daily')>Diário</option>
                <option value="weekly"  @selected(old('frequency', $routine?->frequency) === 'weekly')>Semanal</option>
                <option value="monthly" @selected(old('frequency', $routine?->frequency) === 'monthly')>Mensal</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horário</label>
            <input type="time" name="time_of_day" value="{{ old('time_of_day', $routine ? substr($routine->time_of_day ?? '08:00:00', 0, 5) : '08:00') }}"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
    </div>

    {{-- Days of week --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dias da Semana</label>
        @php
            $selectedDays = old('days_of_week', $routine?->days_of_week ?? []);
            $dayOptions = [
                'monday' => 'Seg', 'tuesday' => 'Ter', 'wednesday' => 'Qua',
                'thursday' => 'Qui', 'friday' => 'Sex', 'saturday' => 'Sáb', 'sunday' => 'Dom',
            ];
        @endphp
        <div class="flex flex-wrap gap-2">
            @foreach($dayOptions as $value => $label)
                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="days_of_week[]" value="{{ $value }}"
                           @checked(in_array($value, $selectedDays))
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Content quantity --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantidade de Conteúdo por Execução</label>
        <input type="number" name="content_quantity" value="{{ old('content_quantity', $routine?->content_quantity ?? 1) }}"
               min="1" max="10"
               class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
    </div>

    {{-- Flags --}}
    <div class="flex items-center gap-6">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" @checked(old('active', $routine?->active ?? true))
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700 dark:text-gray-300">Rotina ativa</span>
        </label>

        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="requires_approval" value="0">
            <input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $routine?->requires_approval ?? true))
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700 dark:text-gray-300">Requer aprovação humana</span>
        </label>
    </div>

</div>
