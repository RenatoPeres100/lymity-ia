<x-layouts.app :title="'Novo Item — Base de Conhecimento'">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <a href="{{ route('admin.clients.knowledge-base.index', $client) }}" class="hover:text-slate-700">{{ $client->name }} — Base de Conhecimento</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Novo Item</span>
</div>

<form method="POST" action="{{ route('admin.clients.knowledge-base.store', $client) }}">
    @csrf

    <div class="max-w-3xl space-y-5">
        <div class="card p-6 space-y-4">
            <div>
                <label class="label">Título <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required class="input">
                @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">Conteúdo <span class="text-red-400">*</span></label>
                <textarea name="content" rows="10" required class="input resize-y font-mono text-sm"
                    placeholder="Informações, documentos, FAQs...">{{ old('content') }}</textarea>
                @error('content')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Fonte</label>
                    <input type="text" name="source" value="{{ old('source') }}" class="input" placeholder="Ex.: Site, documento, entrevista...">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        <option value="active" @selected(old('status') === 'active')>Ativo</option>
                        <option value="draft" @selected(old('status') === 'draft')>Rascunho</option>
                        <option value="archived" @selected(old('status') === 'archived')>Arquivado</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.clients.knowledge-base.index', $client) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg>
                    Criar Item
                </button>
            </div>
        </div>
    </div>
</form>

</x-layouts.app>
