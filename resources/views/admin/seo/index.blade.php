<x-layouts.app title="SEO — Dashboard">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">SEO — Dashboard</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Visão geral das ações de SEO e blog com IA</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.seo.blog.generate') }}" style="background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Gerar Post IA</a>
        <a href="{{ route('admin.seo.keywords.create') }}" style="background:#0ea5e9;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500;text-decoration:none;">+ Palavra-chave</a>
    </div>
</div>

@if(session('success'))
<div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
@endif

{{-- Stats Grid --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Palavras-chave</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['keywords'] }}</div>
        <a href="{{ route('admin.seo.keywords.index') }}" style="font-size:.75rem;color:#6366f1;">Ver todas →</a>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Clusters SEO</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['clusters'] }}</div>
        <a href="{{ route('admin.seo.clusters.index') }}" style="font-size:.75rem;color:#6366f1;">Ver todos →</a>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Planos de Conteúdo</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['plans'] }}</div>
        <a href="{{ route('admin.seo.content-plans.index') }}" style="font-size:.75rem;color:#6366f1;">Ver todos →</a>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Auditorias SEO</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['audits'] }}</div>
        <a href="{{ route('admin.seo.audits.index') }}" style="font-size:.75rem;color:#6366f1;">Ver todas →</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Total de Posts</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['blog_posts'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #dcfce7;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#16a34a;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Publicados</div>
        <div style="font-size:2rem;font-weight:700;color:#15803d;margin-top:.5rem;">{{ $stats['published'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #fef9c3;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#ca8a04;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Aguardando Aprovação</div>
        <div style="font-size:2rem;font-weight:700;color:#a16207;margin-top:.5rem;">{{ $stats['pending'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="font-size:.75rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Rascunhos</div>
        <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-top:.5rem;">{{ $stats['draft'] }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
    {{-- Recent Blog Posts --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;">Posts Recentes</h3>
            <a href="{{ route('admin.seo.blog.index') }}" style="font-size:.75rem;color:#6366f1;">Ver todos</a>
        </div>
        @forelse($recentPosts as $post)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.625rem 0;border-bottom:1px solid #f1f5f9;">
            <div style="flex:1;min-width:0;margin-right:.75rem;">
                <div style="font-size:.875rem;font-weight:500;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $post->title }}</div>
                <div style="font-size:.75rem;color:#94a3b8;">{{ $post->author?->name ?? 'IA' }} · {{ $post->type === 'agency' ? 'Agência' : ($post->client?->name ?? 'Cliente') }}</div>
            </div>
            @php
                $sc = match($post->status){
                    'published'=>'#16a34a','pending_approval'=>'#ca8a04','approved'=>'#2563eb',
                    'draft'=>'#64748b',default=>'#64748b'
                };
            @endphp
            <span style="font-size:.6875rem;font-weight:600;color:{{ $sc }};background:{{ $sc }}1a;padding:.2rem .5rem;border-radius:9999px;">{{ $post->status_label }}</span>
        </div>
        @empty
        <p style="font-size:.875rem;color:#94a3b8;text-align:center;padding:1rem 0;">Nenhum post ainda.</p>
        @endforelse
    </div>

    {{-- Recent Audits --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 style="font-size:.9375rem;font-weight:600;color:#1e293b;">Auditorias Recentes</h3>
            <a href="{{ route('admin.seo.audits.index') }}" style="font-size:.75rem;color:#6366f1;">Ver todas</a>
        </div>
        @forelse($recentAudits as $audit)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.625rem 0;border-bottom:1px solid #f1f5f9;">
            <div style="flex:1;min-width:0;margin-right:.75rem;">
                <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $audit->title }}</div>
                <div style="font-size:.75rem;color:#94a3b8;">{{ $audit->client?->name ?? 'Agência' }} · Score: {{ $audit->score ?? '—' }}</div>
            </div>
            <a href="{{ route('admin.seo.audits.show', $audit) }}" style="font-size:.75rem;color:#6366f1;">Ver</a>
        </div>
        @empty
        <p style="font-size:.875rem;color:#94a3b8;text-align:center;padding:1rem 0;">Nenhuma auditoria ainda.</p>
        @endforelse
    </div>
</div>

</x-layouts.app>
