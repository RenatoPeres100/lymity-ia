<x-layouts.app :title="'Novo Asset — ' . $client->name">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <a href="{{ route('admin.clients.assets.index', $client) }}" class="hover:text-slate-700">{{ $client->name }} — Assets</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Novo Asset</span>
</div>

<form method="POST" action="{{ route('admin.clients.assets.store', $client) }}">
    @csrf

    <div class="max-w-2xl">
        <div class="card p-6 space-y-4">
            <div>
                <label class="label">Nome <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="input" placeholder="Ex.: Logo Principal">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">Descrição</label>
                <textarea name="notes" rows="2" class="input resize-none" placeholder="Descrição do arquivo...">{{ old('notes') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Tipo <span class="text-red-400">*</span></label>
                    <select name="type" class="input" required>
                        <option value="">Selecione...</option>
                        @foreach(['image'=>'Imagem','video'=>'Vídeo','document'=>'Documento','logo'=>'Logo','brand_file'=>'Arquivo de Marca','other'=>'Outro'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Origem <span class="text-red-400">*</span></label>
                    <select name="source" class="input" required>
                        <option value="">Selecione...</option>
                        @foreach(['upload'=>'Upload','google_drive'=>'Google Drive','generated'=>'Gerado por IA','external'=>'Externo'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('source') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="label">URL do Arquivo</label>
                <input type="url" name="path" value="{{ old('path') }}" class="input" placeholder="https://...">
            </div>
            <div>
                <label class="label">URL Externa</label>
                <input type="url" name="external_url" value="{{ old('external_url') }}" class="input" placeholder="Link direto para o asset">
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.clients.assets.index', $client) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg>
                    Cadastrar Asset
                </button>
            </div>
        </div>
    </div>
</form>

</x-layouts.app>
