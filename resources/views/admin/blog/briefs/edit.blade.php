<x-layouts.app title="Editar Briefing">

    <div class="mb-6">
        <a href="{{ route('admin.blog.briefs.show', $contentBrief) }}" class="text-sm text-blue-600 hover:underline">← Ver briefing</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Editar Briefing</h1>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
            @foreach($errors->all() as $e)<p class="text-sm text-red-700">{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="card max-w-2xl">
        <form method="POST" action="{{ route('admin.blog.briefs.update', $contentBrief) }}">
            @csrf @method('PUT')
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $contentBrief->title) }}" required>
                </div>
                <div>
                    <label class="form-label">Tópico <span class="text-red-500">*</span></label>
                    <input type="text" name="topic" class="form-input" value="{{ old('topic', $contentBrief->topic) }}" required>
                </div>
                <div>
                    <label class="form-label">Objetivo</label>
                    <textarea name="goal" class="form-input" rows="2">{{ old('goal', $contentBrief->goal) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Público-Alvo</label>
                    <input type="text" name="audience" class="form-input" value="{{ old('audience', $contentBrief->audience) }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Keyword Principal</label>
                        <input type="text" name="primary_keyword" class="form-input" value="{{ old('primary_keyword', $contentBrief->primary_keyword) }}">
                    </div>
                    <div>
                        <label class="form-label">Keywords Secundárias</label>
                        <input type="text" name="secondary_keywords" class="form-input"
                            value="{{ old('secondary_keywords', implode(', ', $contentBrief->secondary_keywords ?? [])) }}"
                            placeholder="Separadas por vírgula">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Funil</label>
                        <select name="funnel_stage" class="form-input">
                            @foreach(['awareness','consideration','conversion','retention'] as $stage)
                            <option value="{{ $stage }}" {{ $contentBrief->funnel_stage === $stage ? 'selected' : '' }}>{{ ucfirst($stage) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Intenção</label>
                        <select name="search_intent" class="form-input">
                            @foreach(['informational','commercial','transactional','navigational'] as $intent)
                            <option value="{{ $intent }}" {{ $contentBrief->search_intent === $intent ? 'selected' : '' }}>{{ ucfirst($intent) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">CTA Sugerido</label>
                    <input type="text" name="cta_suggestion" class="form-input" value="{{ old('cta_suggestion', $contentBrief->cta_suggestion) }}">
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <a href="{{ route('admin.blog.briefs.show', $contentBrief) }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </div>

</x-layouts.app>
