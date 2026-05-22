<x-layouts.app :title="'Nova Página — ' . $client->name">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <a href="{{ route('admin.clients.pages.index', $client) }}" class="hover:text-slate-700">{{ $client->name }} — Páginas</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Nova Página</span>
</div>

<form method="POST" action="{{ route('admin.clients.pages.store', $client) }}">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 space-y-5">
            <div class="card p-5">
                <h3 class="font-semibold text-slate-800 mb-4">Conteúdo</h3>
                <div class="space-y-4">
                    <div>
                        <label class="label">Título <span class="text-red-400">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="input" placeholder="Ex.: Página Inicial">
                        @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Slug <span class="text-xs text-slate-400">(opcional — gerado automaticamente)</span></label>
                        <input type="text" name="slug" value="{{ old('slug') }}"
                            class="input" placeholder="pagina-inicial">
                    </div>
                    <div>
                        <label class="label">Conteúdo</label>
                        <textarea name="content" rows="12" class="input font-mono text-sm resize-y"
                            placeholder="Conteúdo HTML ou texto da página...">{{ old('content') }}</textarea>
                    </div>
                    <div>
                        <label class="label">Meta Título (SEO)</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title') }}"
                            class="input" placeholder="Título para mecanismos de busca">
                    </div>
                    <div>
                        <label class="label">Meta Descrição (SEO)</label>
                        <textarea name="seo_description" rows="2" class="input resize-none"
                            placeholder="Descrição para mecanismos de busca (máx. 160 caracteres)">{{ old('seo_description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h3 class="font-semibold text-slate-800 mb-4">Configurações</h3>
                <div class="space-y-4">
                    <div>
                        <label class="label">Tipo de Página <span class="text-red-400">*</span></label>
                        <select name="page_type" class="input" required>
                            <option value="home" @selected(old('page_type') === 'home')>Home</option>
                            <option value="about" @selected(old('page_type') === 'about')>Sobre</option>
                            <option value="services" @selected(old('page_type') === 'services')>Serviços</option>
                            <option value="contact" @selected(old('page_type') === 'contact')>Contato</option>
                            <option value="blog" @selected(old('page_type') === 'blog')>Blog</option>
                            <option value="landing" @selected(old('page_type') === 'landing')>Landing Page</option>
                            <option value="other" @selected(old('page_type') === 'other')>Outro</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="draft" @selected(old('status') === 'draft')>Rascunho</option>
                            <option value="pending_approval" @selected(old('status') === 'pending_approval')>Aguardando Aprovação</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card p-5 flex flex-col gap-3">
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                    Criar Página
                </button>
                <a href="{{ route('admin.clients.pages.index', $client) }}" class="btn btn-secondary w-full justify-center">Cancelar</a>
            </div>
        </div>

    </div>
</form>

</x-layouts.app>
