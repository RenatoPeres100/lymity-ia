<x-layouts.app title="SEO — Minha Conta">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">SEO — Minha Conta</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Visão geral das estratégias e resultados de SEO</p>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Palavras-chave</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['keywords'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Posts do Blog</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['blog_posts'] }}</div>
        <a href="{{ route('client.seo.blog.index') }}" style="font-size:.75rem;color:#6366f1;">Ver posts →</a>
    </div>
    <div style="background:#fff;border:1px solid #dcfce7;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#16a34a;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Publicados</div>
        <div style="font-size:2rem;font-weight:700;color:#15803d;margin-top:.5rem;">{{ $stats['published'] }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
    {{-- Recent Posts --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;">Posts Recentes</h3>
            <a href="{{ route('client.seo.blog.index') }}" style="font-size:.75rem;color:#6366f1;">Ver todos</a>
        </div>
        @forelse($recentPosts as $post)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.625rem 0;border-bottom:1px solid #f1f5f9;">
            <div style="flex:1;min-width:0;margin-right:.75rem;">
                <a href="{{ route('client.seo.blog.show', $post) }}" style="font-size:.875rem;font-weight:500;color:#1e293b;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">{{ $post->title }}</a>
                <div style="font-size:.75rem;color:#94a3b8;">{{ $post->created_at->format('d/m/Y') }}</div>
            </div>
            @php $sc = match($post->status){'published'=>'#16a34a','pending_approval'=>'#ca8a04',default=>'#64748b'}; @endphp
            <span style="font-size:.6875rem;font-weight:600;color:{{ $sc }};background:{{ $sc }}1a;padding:.2rem .5rem;border-radius:9999px;flex-shrink:0;">{{ $post->status_label }}</span>
        </div>
        @empty
        <p style="font-size:.875rem;color:#94a3b8;text-align:center;padding:1rem 0;">Nenhum post ainda.</p>
        @endforelse
    </div>

    {{-- Audits --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;">Auditorias SEO</h3>
        </div>
        @forelse($recentAudits as $audit)
        <div style="padding:.625rem 0;border-bottom:1px solid #f1f5f9;">
            <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $audit->title }}</div>
            <div style="display:flex;align-items:center;gap:1rem;margin-top:.25rem;">
                <div style="font-size:.75rem;color:#64748b;">Score: <strong>{{ $audit->score ?? '—' }}/100</strong></div>
                <div style="font-size:.75rem;color:#64748b;">{{ $audit->recommendations->count() }} recomendações</div>
            </div>
        </div>
        @empty
        <p style="font-size:.875rem;color:#94a3b8;text-align:center;padding:1rem 0;">Nenhuma auditoria disponível.</p>
        @endforelse
    </div>
</div>

</x-layouts.app>
