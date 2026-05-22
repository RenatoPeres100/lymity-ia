<x-layouts.app :title="'Editar Página — ' . $page->title">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <a href="{{ route('admin.clients.pages.index', $client) }}" class="hover:text-slate-700">{{ $client->name }} — Páginas</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Editar</span>
</div>

<form method="POST" action="{{ route('admin.clients.pages.update', [$client, $page]) }}">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 space-y-5">
            <div class="card p-5">
                <h3 class="font-semibold text-slate-800 mb-4">Conteúdo</h3>
                <div class="space-y-4">
                    <div>
                        <label class="label">Título <span class="text-red-400">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="input">
                        @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Conteúdo</label>
                        <textarea name="content" rows="12" class="input font-mono text-sm resize-y">{{ old('content', $page->content) }}</textarea>
                    </div>
                    <div>
                        <label class="label">Meta Título (SEO)</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" class="input">
                    </div>
                    <div>
                        <label class="label">Meta Descrição (SEO)</label>
                        <textarea name="seo_description" rows="2" class="input resize-none">{{ old('seo_description', $page->seo_description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h3 class="font-semibold text-slate-800 mb-4">Configurações</h3>
                <div class="space-y-4">
                    <div>
                        <label class="label">Tipo de Página</label>
                        <select name="page_type" class="input">
                            @foreach(['home'=>'Home','about'=>'Sobre','services'=>'Serviços','contact'=>'Contato','blog'=>'Blog','landing'=>'Landing Page','other'=>'Outro'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('page_type', $page->page_type) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            @foreach(['draft'=>'Rascunho','pending_approval'=>'Aguardando Aprovação','approved'=>'Aprovado','published'=>'Publicado','archived'=>'Arquivado'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $page->status) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($page->published_at)
                    <div class="text-xs text-slate-400">
                        Publicado em: {{ $page->published_at->format('d/m/Y H:i') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="card p-5 flex flex-col gap-3">
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                    Salvar Alterações
                </button>
                <a href="{{ route('admin.clients.pages.index', $client) }}" class="btn btn-secondary w-full justify-center">Cancelar</a>
            </div>
        </div>

    </div>
</form>

</x-layouts.app>
