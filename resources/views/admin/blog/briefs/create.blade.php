<x-layouts.app title="Novo Briefing Manual">

    <div class="mb-6">
        <a href="{{ route('admin.blog.briefs.index') }}" class="text-sm text-blue-600 hover:underline">← Briefings</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Novo Briefing Manual</h1>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
            @foreach($errors->all() as $e)<p class="text-sm text-red-700">{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="card max-w-2xl">
        <form method="POST" action="{{ route('admin.blog.briefs.store') }}">
            @csrf
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" required>
                </div>
                <div>
                    <label class="form-label">Tópico <span class="text-red-500">*</span></label>
                    <input type="text" name="topic" class="form-input" value="{{ old('topic') }}" required>
                </div>
                <div>
                    <label class="form-label">Objetivo</label>
                    <textarea name="goal" class="form-input" rows="2">{{ old('goal') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Público-Alvo</label>
                    <input type="text" name="audience" class="form-input" value="{{ old('audience') }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Keyword Principal</label>
                        <input type="text" name="primary_keyword" class="form-input" value="{{ old('primary_keyword') }}">
                    </div>
                    <div>
                        <label class="form-label">Keywords Secundárias</label>
                        <input type="text" name="secondary_keywords" class="form-input" value="{{ old('secondary_keywords') }}"
                            placeholder="Separadas por vírgula">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Funil</label>
                        <select name="funnel_stage" class="form-input">
                            <option value="awareness">Awareness</option>
                            <option value="consideration">Consideration</option>
                            <option value="conversion">Conversion</option>
                            <option value="retention">Retention</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Intenção</label>
                        <select name="search_intent" class="form-input">
                            <option value="informational">Informational</option>
                            <option value="commercial">Commercial</option>
                            <option value="transactional">Transactional</option>
                            <option value="navigational">Navigational</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">CTA Sugerido</label>
                    <input type="text" name="cta_suggestion" class="form-input" value="{{ old('cta_suggestion') }}">
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <a href="{{ route('admin.blog.briefs.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Criar Briefing</button>
            </div>
        </form>
    </div>

</x-layouts.app>
