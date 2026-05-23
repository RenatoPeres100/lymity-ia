<x-layouts.app title="Meu Blog SEO">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Meu Blog SEO</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Posts criados pela Lymity para o seu blog</p>
    </div>
    <a href="{{ route('client.seo.blog.approvals') }}" style="font-size:.875rem;color:#6366f1;border:1px solid #6366f1;padding:.4rem .75rem;border-radius:.5rem;text-decoration:none;">Aprovações</a>
</div>

<form method="GET" style="display:flex;gap:.75rem;margin-bottom:1rem;">
    <select name="status" style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .75rem;font-size:.875rem;color:#374151;">
        <option value="">Todos os status</option>
        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Aguardando aprovação</option>
        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprovado</option>
        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
    </select>
    <button type="submit" style="background:#6366f1;color:#fff;padding:.4rem 1rem;border-radius:.5rem;font-size:.875rem;border:none;cursor:pointer;">Filtrar</button>
    <a href="{{ route('client.seo.blog.index') }}" style="color:#64748b;font-size:.875rem;padding:.4rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;text-decoration:none;">Limpar</a>
</form>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;">
    @forelse($posts as $post)
    @php
        $sc = match($post->status){'published'=>'#16a34a','pending_approval'=>'#ca8a04','approved'=>'#2563eb','draft'=>'#64748b',default=>'#64748b'};
    @endphp
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;display:flex;flex-direction:column;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.75rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;line-height:1.4;">{{ Str::limit($post->title, 60) }}</h3>
            <span style="font-size:.6875rem;font-weight:600;color:{{ $sc }};background:{{ $sc }}1a;padding:.2rem .5rem;border-radius:9999px;flex-shrink:0;">{{ $post->status_label }}</span>
        </div>
        @if($post->excerpt)
        <p style="font-size:.8125rem;color:#64748b;line-height:1.5;flex:1;margin-bottom:.75rem;">{{ Str::limit($post->excerpt, 120) }}</p>
        @endif
        @if($post->focus_keyword)
        <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.75rem;">Foco: {{ $post->focus_keyword }}</div>
        @endif
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:.75rem;border-top:1px solid #f1f5f9;">
            <div style="font-size:.75rem;color:#94a3b8;">{{ $post->created_at->format('d/m/Y') }}</div>
            <a href="{{ route('client.seo.blog.show', $post) }}" style="font-size:.75rem;color:#6366f1;text-decoration:none;font-weight:500;">Ver post →</a>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:3rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;">
        <p style="font-size:.875rem;color:#94a3b8;">Nenhum post encontrado.</p>
    </div>
    @endforelse
</div>

@if($posts->hasPages())
<div style="margin-top:1rem;">{{ $posts->withQueryString()->links() }}</div>
@endif

</x-layouts.app>
