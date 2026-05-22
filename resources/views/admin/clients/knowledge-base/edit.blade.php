<x-layouts.app :title="'Editar — ' . $entry->title">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <a href="{{ route('admin.clients.knowledge-base.index', $client) }}" class="hover:text-slate-700">{{ $client->name }} — Base de Conhecimento</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Editar</span>
</div>

<form method="POST" action="{{ route('admin.clients.knowledge-base.update', [$client, $entry]) }}">
    @csrf @method('PUT')

    <div class="max-w-3xl space-y-5">
        <div class="card p-6 space-y-4">
            <div>
                <label class="label">Título <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title', $entry->title) }}" required class="input">
            </div>
            <div>
                <label class="label">Conteúdo <span class="text-red-400">*</span></label>
                <textarea name="content" rows="10" required class="input resize-y font-mono text-sm">{{ old('content', $entry->content) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Fonte</label>
                    <input type="text" name="source" value="{{ old('source', $entry->source) }}" class="input">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        @foreach(['active'=>'Ativo','draft'=>'Rascunho','archived'=>'Arquivado'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $entry->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.clients.knowledge-base.index', $client) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg>
                    Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</form>

</x-layouts.app>
