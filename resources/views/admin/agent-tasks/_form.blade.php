@php
    $t = $task;
    $daysOptions = ['monday' => 'Seg', 'tuesday' => 'Ter', 'wednesday' => 'Qua', 'thursday' => 'Qui', 'friday' => 'Sex', 'saturday' => 'Sáb', 'sunday' => 'Dom'];
    $selectedDays = old('days_of_week', $t?->days_of_week ?? []);
@endphp

{{-- Section 1: Identificação --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800 dark:text-white text-sm uppercase tracking-wide">1. Identificação</h2>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
        <input type="text" name="title" value="{{ old('title', $t?->title) }}" required
               class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white"
               placeholder="Ex: Criar posts semanais no blog da Lymity IA">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Funcionário IA</label>
            <select name="ai_employee_id" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                <option value="">Sem funcionário vinculado</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(old('ai_employee_id', $t?->ai_employee_id) == $emp->id)>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo da Tarefa *</label>
            <select name="task_type" required class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                <option value="blog_post_recurring" @selected(old('task_type', $t?->task_type) === 'blog_post_recurring')>Blog Post Recorrente</option>
                <option value="instagram_post_recurring" @selected(old('task_type', $t?->task_type) === 'instagram_post_recurring')>Post Instagram Recorrente</option>
                <option value="instagram_carousel_recurring" @selected(old('task_type', $t?->task_type) === 'instagram_carousel_recurring')>Carrossel Instagram Recorrente</option>
                <option value="copy_review_recurring" @selected(old('task_type', $t?->task_type) === 'copy_review_recurring')>Revisão de Copy</option>
                <option value="custom" @selected(old('task_type', $t?->task_type) === 'custom')>Custom</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Canal</label>
            <select name="content_channel" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                <option value="">Selecione</option>
                <option value="blog" @selected(old('content_channel', $t?->content_channel) === 'blog')>Blog</option>
                <option value="instagram" @selected(old('content_channel', $t?->content_channel) === 'instagram')>Instagram</option>
                <option value="linkedin" @selected(old('content_channel', $t?->content_channel) === 'linkedin')>LinkedIn</option>
                <option value="facebook" @selected(old('content_channel', $t?->content_channel) === 'facebook')>Facebook</option>
                <option value="internal" @selected(old('content_channel', $t?->content_channel) === 'internal')>Interno</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
            <select name="status" required class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                <option value="active" @selected(old('status', $t?->status ?? 'active') === 'active')>Ativa</option>
                <option value="paused" @selected(old('status', $t?->status) === 'paused')>Pausada</option>
                <option value="disabled" @selected(old('status', $t?->status) === 'disabled')>Desativada</option>
            </select>
        </div>
    </div>
</div>

{{-- Section 2: Instruções --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800 dark:text-white text-sm uppercase tracking-wide">2. Instruções Operacionais</h2>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
        <textarea name="description" rows="2"
                  class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white"
                  placeholder="Breve descrição do objetivo">{{ old('description', $t?->description) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instruções Detalhadas para a IA</label>
        <textarea name="operational_instructions" rows="5"
                  class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white"
                  placeholder="Explique detalhadamente o que a IA deve fazer em cada execução...">{{ old('operational_instructions', $t?->operational_instructions) }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Quanto mais detalhe, melhor o resultado. Inclua tópicos, abordagem, público, tom desejado.</p>
    </div>
</div>

{{-- Section 3: Pesquisa --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800 dark:text-white text-sm uppercase tracking-wide">3. Pesquisa Externa</h2>

    <div class="flex items-center gap-3">
        <input type="hidden" name="requires_external_research" value="0">
        <input type="checkbox" name="requires_external_research" value="1" id="requires_research"
               @checked(old('requires_external_research', $t?->requires_external_research))
               class="rounded border-gray-300">
        <label for="requires_research" class="text-sm text-gray-700 dark:text-gray-300">Permitir pesquisa externa antes de gerar</label>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temas para pesquisa</label>
            <input type="text" name="external_research_topics" value="{{ old('external_research_topics', $t?->external_research_topics) }}"
                   class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white"
                   placeholder="Ex: IA, automação, marketing digital">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recência (dias)</label>
            <input type="number" name="external_research_recency_days" value="{{ old('external_research_recency_days', $t?->external_research_recency_days ?? 7) }}"
                   min="1" max="90" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Se pesquisa indisponível</label>
        <select name="if_research_unavailable" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
            <option value="ask_human" @selected(old('if_research_unavailable', $t?->if_research_unavailable ?? 'ask_human') === 'ask_human')>Pedir input humano</option>
            <option value="generate_evergreen" @selected(old('if_research_unavailable', $t?->if_research_unavailable) === 'generate_evergreen')>Gerar conteúdo evergreen</option>
            <option value="block_execution" @selected(old('if_research_unavailable', $t?->if_research_unavailable) === 'block_execution')>Bloquear execução</option>
        </select>
    </div>
</div>

{{-- Section 4: Visual / Imagem --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800 dark:text-white text-sm uppercase tracking-wide">4. Visual / Imagem</h2>

    <div class="flex items-center gap-3">
        <input type="hidden" name="requires_image" value="0">
        <input type="checkbox" name="requires_image" value="1" id="requires_image"
               @checked(old('requires_image', $t?->requires_image))
               class="rounded border-gray-300">
        <label for="requires_image" class="text-sm text-gray-700 dark:text-gray-300">Requer geração de imagem</label>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de imagem</label>
            <select name="image_type" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                <option value="">Selecione</option>
                <option value="featured_image" @selected(old('image_type', $t?->image_type) === 'featured_image')>Imagem Destacada (Blog)</option>
                <option value="feed_image" @selected(old('image_type', $t?->image_type) === 'feed_image')>Imagem Feed (Instagram)</option>
                <option value="carousel_slides" @selected(old('image_type', $t?->image_type) === 'carousel_slides')>Slides de Carrossel</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Qtd. slides (carrossel)</label>
            <input type="number" name="carousel_slides_count" value="{{ old('carousel_slides_count', $t?->carousel_slides_count ?? 5) }}"
                   min="2" max="20" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diretrizes visuais</label>
        <textarea name="image_instructions" rows="2"
                  class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white"
                  placeholder="Estilo, cores, elementos, mood...">{{ old('image_instructions', $t?->image_instructions) }}</textarea>
    </div>
</div>

{{-- Section 5: Recorrência --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800 dark:text-white text-sm uppercase tracking-wide">5. Recorrência</h2>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frequência *</label>
            <select name="frequency" required class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
                <option value="manual" @selected(old('frequency', $t?->frequency ?? 'manual') === 'manual')>Manual</option>
                <option value="daily" @selected(old('frequency', $t?->frequency) === 'daily')>Diária</option>
                <option value="weekly" @selected(old('frequency', $t?->frequency) === 'weekly')>Semanal</option>
                <option value="monthly" @selected(old('frequency', $t?->frequency) === 'monthly')>Mensal</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horário</label>
            <input type="time" name="time_of_day" value="{{ old('time_of_day', \Str::before($t?->time_of_day ?? '09:00', ':') . ':' . (explode(':', $t?->time_of_day ?? '09:00')[1] ?? '00')) }}"
                   class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
            <input type="text" name="timezone" value="{{ old('timezone', $t?->timezone ?? 'America/Sao_Paulo') }}"
                   class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dias da semana (semanal)</label>
        <div class="flex flex-wrap gap-3">
            @foreach($daysOptions as $value => $label)
                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="days_of_week[]" value="{{ $value }}"
                           @checked(in_array($value, $selectedDays))
                           class="rounded border-gray-300">
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantidade por execução</label>
            <input type="number" name="content_quantity_per_run" value="{{ old('content_quantity_per_run', $t?->content_quantity_per_run ?? 1) }}"
                   min="1" max="10" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Máx execuções por dia</label>
            <input type="number" name="max_runs_per_day" value="{{ old('max_runs_per_day', $t?->max_runs_per_day ?? 1) }}"
                   min="1" max="20" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-900 dark:text-white">
        </div>
    </div>
</div>

{{-- Section 6: Aprovação --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800 dark:text-white text-sm uppercase tracking-wide">6. Aprovação e Publicação</h2>

    <div class="space-y-3">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="requires_approval" value="0">
            <input type="checkbox" name="requires_approval" value="1" id="requires_approval"
                   @checked(old('requires_approval', $t?->requires_approval ?? true))
                   class="rounded border-gray-300">
            <span class="text-sm text-gray-700 dark:text-gray-300">Requer aprovação antes de publicar (recomendado)</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="auto_schedule_after_approval" value="0">
            <input type="checkbox" name="auto_schedule_after_approval" value="1" id="auto_schedule"
                   @checked(old('auto_schedule_after_approval', $t?->auto_schedule_after_approval ?? true))
                   class="rounded border-gray-300">
            <span class="text-sm text-gray-700 dark:text-gray-300">Agendar automaticamente após aprovação</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="auto_publish_after_approval" value="0">
            <input type="checkbox" name="auto_publish_after_approval" value="1" id="auto_publish"
                   @checked(old('auto_publish_after_approval', $t?->auto_publish_after_approval ?? false))
                   class="rounded border-gray-300">
            <span class="text-sm text-gray-700 dark:text-gray-300">Publicar automaticamente após aprovação (sem agendamento)</span>
        </label>
    </div>

    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
        <strong>Regra de segurança:</strong> Se "Requer aprovação" estiver marcado, nenhum conteúdo publicará sem passar pela fila de aprovação, independentemente das outras configurações.
    </div>
</div>
