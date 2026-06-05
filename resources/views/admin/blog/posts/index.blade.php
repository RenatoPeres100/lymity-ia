<x-layouts.app title="Blog Posts">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Blog Posts</h1>
        <p style="font-size:.85rem;color:#64748b;">Gerencie artigos publicados e rascunhos.</p>
    </div>
    <a href="{{ route('admin.blog-posts.create') }}" class="btn-primary">+ Novo Post</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <th style="padding:12px 16px;width:40px;"><input type="checkbox" id="bulk-select-all"></th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Título</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Categoria</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Publicado em</th>
                <th style="text-align:right;padding:12px 16px;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            <tr data-bulk-row style="border-bottom:1px solid #f1f5f9;transition:background .15s;cursor:pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:14px 16px;"><input type="checkbox" class="bulk-cb" value="{{ $post->id }}"></td>
                <td style="padding:14px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#334155;margin-bottom:2px;">{{ $post->title }}</div>
                    <div style="font-size:.75rem;color:#475569;">{{ $post->slug }}</div>
                </td>
                <td style="padding:14px 16px;font-size:.8rem;color:#94a3b8;">{{ $post->category?->name ?? '—' }}</td>
                <td style="padding:14px 16px;">
                    @php
                        $statusColors = [
                            'draft'            => ['bg'=>'#1e293b','color'=>'#94a3b8','label'=>'Rascunho'],
                            'pending_approval' => ['bg'=>'#451a03','color'=>'#fb923c','label'=>'Aguardando'],
                            'approved'         => ['bg'=>'#0c2a3b','color'=>'#22d3ee','label'=>'Aprovado'],
                            'published'        => ['bg'=>'#052e16','color'=>'#4ade80','label'=>'Publicado'],
                            'archived'         => ['bg'=>'#1e1b4b','color'=>'#818cf8','label'=>'Arquivado'],
                        ];
                        $s = $statusColors[$post->status] ?? $statusColors['draft'];
                    @endphp
                    <span style="background:{{ $s['bg'] }};color:{{ $s['color'] }};font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;">{{ $s['label'] }}</span>
                </td>
                <td style="padding:14px 16px;font-size:.8rem;color:#64748b;">{{ $post->published_at?->format('d/m/Y') ?? '—' }}</td>
                <td style="padding:14px 16px;text-align:right;">
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('admin.blog-posts.edit', $post) }}" style="font-size:.78rem;color:#6b8fff;font-weight:600;text-decoration:none;">Editar</a>
                        <form action="{{ route('admin.blog-posts.destroy', $post) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir este post?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:.78rem;color:#f87171;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:48px 16px;text-align:center;color:#475569;font-size:.875rem;">Nenhum post encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($posts->hasPages())
<div style="margin-top:20px;">{{ $posts->links() }}</div>
@endif

@if(Route::has('admin.blog-posts.bulk-delete'))
<x-bulk-actions :action="route('admin.blog-posts.bulk-delete')" />
@elseif(Route::has('admin.blog.posts.bulk-delete'))
<x-bulk-actions :action="route('admin.blog.posts.bulk-delete')" />
@endif

</x-layouts.app>
