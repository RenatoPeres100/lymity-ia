<x-layouts.app title="Nova Categoria">

<div style="margin-bottom:28px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:4px;">Nova Categoria</h1>
    <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.blog-categories.index') }}" style="color:#6b8fff;text-decoration:none;">Categorias</a> / Criar</p>
</div>

@if($errors->any())
<div style="background:#2d0a0a;border:1px solid #7f1d1d;border-radius:10px;padding:14px 18px;margin-bottom:20px;">
    @foreach($errors->all() as $error)
    <div style="color:#f87171;font-size:.8rem;padding:2px 0;">• {{ $error }}</div>
    @endforeach
</div>
@endif

<div style="max-width:560px;">
    <form action="{{ route('admin.blog-categories.store') }}" method="POST">
        @csrf
        <div class="table-wrapper" style="padding:28px;display:flex;flex-direction:column;gap:18px;">
            <div>
                <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Nome *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nome da categoria"
                    style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 14px;color:#e2e8f0;font-size:.9rem;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="gerado-automaticamente"
                    style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 14px;color:#e2e8f0;font-size:.9rem;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Ícone (emoji)</label>
                <input type="text" name="icon" value="{{ old('icon') }}" placeholder="📝"
                    style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 14px;color:#e2e8f0;font-size:.9rem;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Descrição</label>
                <textarea name="description" rows="3" placeholder="Descrição breve da categoria"
                    style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 14px;color:#e2e8f0;font-size:.875rem;outline:none;resize:vertical;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('description') }}</textarea>
            </div>
            <div style="display:flex;gap:12px;padding-top:4px;">
                <button type="submit" class="btn-primary" style="cursor:pointer;border:none;">Salvar Categoria</button>
                <a href="{{ route('admin.blog-categories.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;padding:10px 16px;">Cancelar</a>
            </div>
        </div>
    </form>
</div>

</x-layouts.app>
