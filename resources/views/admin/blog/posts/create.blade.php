<x-layouts.app title="Novo Post">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Novo Post</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.blog.pipeline.index') }}" style="color:#6b8fff;text-decoration:none;">Blog Posts</a> / Criar</p>
    </div>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;">
    @foreach($errors->all() as $error)
    <div style="color:#f87171;font-size:.8rem;padding:2px 0;">• {{ $error }}</div>
    @endforeach
</div>
@endif

<form action="{{ route('admin.blog.posts.store') }}" method="POST">
    @csrf
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="table-wrapper" style="padding:24px;">
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Título *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Título do post"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.9rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="gerado-automaticamente"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.9rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                    <div style="font-size:.72rem;color:#475569;margin-top:4px;">Deixe vazio para gerar automaticamente do título.</div>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Resumo</label>
                    <textarea name="excerpt" rows="2" placeholder="Breve descrição para listagens e SEO"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.9rem;outline:none;resize:vertical;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('excerpt') }}</textarea>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Conteúdo *</label>
                    <textarea name="content" rows="16" required placeholder="Conteúdo completo do artigo (HTML suportado)"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;resize:vertical;box-sizing:border-box;font-family:monospace;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('content') }}</textarea>
                </div>
            </div>

            <div class="table-wrapper" style="padding:24px;">
                <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">SEO</div>
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Meta Description</label>
                    <textarea name="seo_description" rows="2" placeholder="Descrição para mecanismos de busca (máx. 160 caracteres)"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;resize:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('meta_description') }}</textarea>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Tags</label>
                    <input type="text" name="tags" value="{{ old('tags') }}" placeholder="tag1, tag2, tag3"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.9rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                    <div style="font-size:.72rem;color:#475569;margin-top:4px;">Separadas por vírgula.</div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="table-wrapper" style="padding:24px;">
                <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">Publicação</div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Status *</label>
                    <select name="status" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;">
                        <option value="draft" {{ old('status','draft') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="pending_approval" {{ old('status') === 'pending_approval' ? 'selected' : '' }}>Aguardando Aprovação</option>
                        <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Aprovado</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publicado</option>
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Data de publicação</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Categoria</label>
                    <select name="category_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;">
                        <option value="">Sem categoria</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-wrapper" style="padding:24px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                        style="width:16px;height:16px;accent-color:#4a6cf7;">
                    <div>
                        <div style="font-size:.875rem;font-weight:600;color:#334155;">Post em destaque</div>
                        <div style="font-size:.75rem;color:#64748b;">Exibir primeiro na listagem.</div>
                    </div>
                </label>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                <button type="submit" class="btn-primary" style="width:100%;text-align:center;cursor:pointer;border:none;">Salvar Post</button>
                <a href="{{ route('admin.blog.pipeline.index') }}" style="text-align:center;font-size:.875rem;color:#64748b;text-decoration:none;padding:10px;">Cancelar</a>
            </div>
        </div>

    </div>
</form>

</x-layouts.app>
