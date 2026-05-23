<x-layouts.app title="Blog SEO — Todos os Posts">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Blog SEO — Todos os Posts</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Posts da agência e de clientes gerados com IA</p>
    </div>
    <a href="{{ route('admin.seo.blog.generate') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">Gerar Post com IA</a>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('error') }}</div>
@endif

{{-- Filters --}}
<form method="GET" style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;">
    <select name="type" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .75rem;font-size:.875rem;color:#374151;">
        <option value="">Todos os tipos</option>
        <option value="agency" {{ request('type') == 'agency' ? 'selected' : '' }}>Agência</option>
        <option value="client" {{ request('type') == 'client' ? 'selected' : '' }}>Cliente</option>
    </select>
    <select name="status" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .75rem;font-size:.875rem;color:#374151;">
        <option value="">Todos os status</option>
        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Aguardando aprovação</option>
        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprovado</option>
        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
    </select>
    <select name="client_id" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .75rem;font-size:.875rem;color:#374151;">
        <option value="">Todos os clientes</option>
        @foreach($clients as $client)
        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
        @endforeach
    </select>
    <button type="submit" style="background:#6366f1;color:#fff;padding:.4rem 1rem;border-radius:.5rem;font-size:.875rem;border:none;cursor:pointer;">Filtrar</button>
    <a href="{{ route('admin.seo.blog.index') }}" style="color:#64748b;font-size:.875rem;padding:.4rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;text-decoration:none;">Limpar</a>
</form>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Título</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Tipo</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Palavra-chave</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Autor</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Status</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;">Data</th>
                <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            @php
                $sc = match($post->status){
                    'published'=>'#16a34a','pending_approval'=>'#ca8a04','approved'=>'#2563eb',
                    'draft'=>'#64748b','archived'=>'#94a3b8',default=>'#64748b'
                };
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:.75rem 1rem;max-width:240px;">
                    <div style="font-size:.875rem;font-weight:500;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $post->title }}</div>
                    @if($post->client)
                    <div style="font-size:.75rem;color:#94a3b8;">{{ $post->client->name }}</div>
                    @endif
                </td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $post->type === 'agency' ? 'Agência' : 'Cliente' }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $post->focus_keyword ? Str::limit($post->focus_keyword, 25) : '—' }}</td>
                <td style="padding:.75rem 1rem;font-size:.875rem;color:#64748b;">{{ $post->author?->name ?? 'IA' }}</td>
                <td style="padding:.75rem 1rem;">
                    <span style="font-size:.6875rem;font-weight:600;color:{{ $sc }};background:{{ $sc }}1a;padding:.2rem .5rem;border-radius:9999px;">{{ $post->status_label }}</span>
                </td>
                <td style="padding:.75rem 1rem;font-size:.75rem;color:#94a3b8;">{{ $post->created_at->format('d/m/Y') }}</td>
                <td style="padding:.75rem 1rem;text-align:right;white-space:nowrap;">
                    @if(in_array($post->status, ['draft', 'rejected']))
                    <form method="POST" action="{{ route('admin.seo.blog.send-approval', $post) }}" style="display:inline;">
                        @csrf
                        <button type="submit" style="font-size:.75rem;color:#ca8a04;background:none;border:none;cursor:pointer;margin-right:.5rem;">Enviar para aprovação</button>
                    </form>
                    @endif
                    @if($post->status === 'approved')
                    <form method="POST" action="{{ route('admin.seo.blog.publish', $post) }}" style="display:inline;" onsubmit="return confirm('Publicar post?')">
                        @csrf
                        <button type="submit" style="font-size:.75rem;color:#16a34a;background:none;border:none;cursor:pointer;margin-right:.5rem;">Publicar</button>
                    </form>
                    @endif
                    <a href="{{ route('admin.blog-posts.edit', $post) }}" style="font-size:.75rem;color:#6366f1;">Editar</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhum post encontrado. <a href="{{ route('admin.seo.blog.generate') }}" style="color:#6366f1;">Gerar com IA</a></td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($posts->hasPages())
    <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">{{ $posts->withQueryString()->links() }}</div>
    @endif
</div>

</x-layouts.app>
