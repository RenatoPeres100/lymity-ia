<x-layouts.app title="{{ $blogPost->title }}">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('client.seo.blog.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;">← Meu Blog</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @if($blogPost->subtitle)
            <div style="font-size:.875rem;color:#6366f1;font-weight:500;margin-bottom:.5rem;">{{ $blogPost->subtitle }}</div>
            @endif
            <h1 style="font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1.3;margin-bottom:1rem;">{{ $blogPost->title }}</h1>

            @if($blogPost->excerpt)
            <p style="font-size:1rem;color:#64748b;line-height:1.6;padding-bottom:1rem;border-bottom:1px solid #f1f5f9;margin-bottom:1.5rem;">{{ $blogPost->excerpt }}</p>
            @endif

            <div style="font-size:.9375rem;color:#374151;line-height:1.8;">
                {!! $blogPost->content !!}
            </div>
        </div>
    </div>

    <div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
            <h3 style="font-size:.875rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">Informações do Post</h3>
            @php $sc = match($blogPost->status){'published'=>'#16a34a','pending_approval'=>'#ca8a04','approved'=>'#2563eb',default=>'#64748b'}; @endphp
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Status</div>
                    <span style="font-size:.75rem;font-weight:600;color:{{ $sc }};background:{{ $sc }}1a;padding:.2rem .5rem;border-radius:9999px;display:inline-block;margin-top:.25rem;">{{ $blogPost->status_label }}</span>
                </div>
                @if($blogPost->focus_keyword)
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Palavra-chave foco</div>
                    <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $blogPost->focus_keyword }}</div>
                </div>
                @endif
                @if($blogPost->read_time)
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Tempo de leitura</div>
                    <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">~{{ $blogPost->read_time }} min</div>
                </div>
                @endif
                <div>
                    <div style="font-size:.75rem;color:#64748b;font-weight:500;">Criado em</div>
                    <div style="font-size:.875rem;color:#1e293b;margin-top:.25rem;">{{ $blogPost->created_at->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        @if($blogPost->seo_title || $blogPost->seo_description)
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.875rem;font-weight:600;color:#1e293b;margin-bottom:1rem;">SEO</h3>
            @if($blogPost->seo_title)
            <div style="margin-bottom:.75rem;">
                <div style="font-size:.75rem;color:#64748b;font-weight:500;">Meta título</div>
                <div style="font-size:.8125rem;color:#374151;margin-top:.25rem;">{{ $blogPost->seo_title }}</div>
            </div>
            @endif
            @if($blogPost->seo_description)
            <div>
                <div style="font-size:.75rem;color:#64748b;font-weight:500;">Meta descrição</div>
                <div style="font-size:.8125rem;color:#374151;margin-top:.25rem;">{{ $blogPost->seo_description }}</div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

</x-layouts.app>
