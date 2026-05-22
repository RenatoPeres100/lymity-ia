<x-layouts.app title="Categorias do Blog">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:4px;">Categorias do Blog</h1>
        <p style="font-size:.85rem;color:#64748b;">Organize os posts por categoria.</p>
    </div>
    <a href="{{ route('admin.blog-categories.create') }}" class="btn-primary">+ Nova Categoria</a>
</div>

@if(session('success'))
<div style="background:#0f2a1a;border:1px solid #166534;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#4ade80;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #1e293b;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Categoria</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Slug</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Posts</th>
                <th style="text-align:right;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
            <tr style="border-bottom:1px solid #0f172a;transition:background .15s;" onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='transparent'">
                <td style="padding:14px 16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        @if($category->icon)<span style="font-size:1.2rem;">{{ $category->icon }}</span>@endif
                        <div style="font-size:.875rem;font-weight:600;color:#e2e8f0;">{{ $category->name }}</div>
                    </div>
                </td>
                <td style="padding:14px 16px;font-size:.8rem;color:#475569;font-family:monospace;">{{ $category->slug }}</td>
                <td style="padding:14px 16px;font-size:.875rem;color:#94a3b8;">{{ $category->posts_count ?? $category->posts()->count() }}</td>
                <td style="padding:14px 16px;text-align:right;">
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('admin.blog-categories.edit', $category) }}" style="font-size:.78rem;color:#6b8fff;font-weight:600;text-decoration:none;">Editar</a>
                        <form action="{{ route('admin.blog-categories.destroy', $category) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir esta categoria?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:.78rem;color:#f87171;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding:48px 16px;text-align:center;color:#475569;font-size:.875rem;">Nenhuma categoria encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-layouts.app>
