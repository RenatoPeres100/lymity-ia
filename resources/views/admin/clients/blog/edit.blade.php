<x-layouts.app :title="'Editar Post — ' . $post->title">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <a href="{{ route('admin.clients.blog.index', $client) }}" class="hover:text-slate-700">{{ $client->name }} — Blog</a>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Editar</span>
</div>

<form method="POST" action="{{ route('admin.clients.blog.update', [$client, $post]) }}">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 space-y-5">
            <div class="card p-5 space-y-4">
                <div>
                    <label class="label">Título <span class="text-red-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="input">
                </div>
                <div>
                    <label class="label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" class="input">
                </div>
                <div>
                    <label class="label">Conteúdo</label>
                    <textarea name="content" rows="14" class="input resize-y font-mono text-sm">{{ old('content', $post->content) }}</textarea>
                </div>
                <div>
                    <label class="label">Descrição SEO</label>
                    <textarea name="seo_description" rows="2" class="input resize-none">{{ old('seo_description', $post->seo_description) }}</textarea>
                </div>
                <div>
                    <label class="label">Tags</label>
                    <input type="text" name="tags" value="{{ old('tags', is_array($post->tags) ? implode(', ', $post->tags) : $post->tags) }}" class="input">
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card p-5 space-y-4">
                <h3 class="font-semibold text-slate-800">Configurações</h3>
                <div>
                    <label class="label">Categoria</label>
                    <select name="category_id" class="input">
                        <option value="">Sem categoria</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $post->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        @foreach(['draft'=>'Rascunho','pending_approval'=>'Aguardando Aprovação','approved'=>'Aprovado','published'=>'Publicado'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $post->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Imagem de Capa (URL)</label>
                    <input type="url" name="featured_image" value="{{ old('featured_image', $post->featured_image) }}" class="input">
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured)) class="w-4 h-4 rounded border-slate-300">
                    <span class="text-sm text-slate-700">Destaque</span>
                </label>
            </div>

            <div class="card p-5 flex flex-col gap-3">
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg>
                    Salvar Alterações
                </button>
                <a href="{{ route('admin.clients.blog.index', $client) }}" class="btn btn-secondary w-full justify-center">Cancelar</a>
            </div>
        </div>

    </div>
</form>

</x-layouts.app>
