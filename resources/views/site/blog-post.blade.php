<x-layouts.public :title="$post->title" :meta-description="$post->meta_description ?? $post->excerpt ?? Str::limit(strip_tags($post->content), 160)">

<article>

<section style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);padding:80px 32px 64px;">
    <div class="container" style="max-width:760px;">
        @if($post->category)
        <div style="margin-bottom:16px;">
            <a href="{{ route('blog') }}" style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b8fff;text-decoration:none;">← Blog</a>
            <span style="color:#334155;margin:0 8px;">/</span>
            <span style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b8fff;">{{ $post->category->name }}</span>
        </div>
        @endif
        <h1 style="font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:800;color:#fff;line-height:1.2;letter-spacing:-0.025em;margin-bottom:16px;">{{ $post->title }}</h1>
        @if($post->excerpt)
        <p style="color:#94a3b8;font-size:1rem;line-height:1.75;margin-bottom:24px;">{{ $post->excerpt }}</p>
        @endif
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            @if($post->published_at)
            <span style="font-size:.8rem;color:#64748b;">{{ $post->published_at->format('d \d\e F \d\e Y') }}</span>
            @endif
            <span style="color:#1e3a5f;">·</span>
            <span style="font-size:.8rem;color:#64748b;">{{ $post->read_time }} min de leitura</span>
            @if($post->tags && count($post->tags))
            <span style="color:#1e3a5f;">·</span>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                @foreach($post->tags as $tag)
                <span style="background:rgba(74,108,247,.15);color:#6b8fff;font-size:.7rem;font-weight:600;padding:2px 10px;border-radius:20px;border:1px solid rgba(74,108,247,.25);">{{ $tag }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:760px;">
        <div style="font-size:1rem;color:#374151;line-height:1.85;" class="prose-content">
            {!! $post->content !!}
        </div>

        @if($post->tags && count($post->tags))
        <div style="margin-top:48px;padding-top:24px;border-top:1px solid #e2e8f0;">
            <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:10px;">Tags</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($post->tags as $tag)
                <span style="background:#f1f5f9;color:#475569;font-size:.8rem;font-weight:500;padding:4px 14px;border-radius:20px;border:1px solid #e2e8f0;">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <div style="margin-top:48px;padding:32px;background:#f8fafc;border-radius:16px;border:1px solid #e2e8f0;text-align:center;">
            <h3 style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-bottom:8px;">Gostou do conteúdo?</h3>
            <p style="font-size:.875rem;color:#64748b;margin-bottom:20px;">Descubra como a Lymity pode transformar sua operação digital com IA e estratégia integrada.</p>
            <a href="{{ route('contato') }}" class="pub-btn-primary">Solicitar diagnóstico gratuito →</a>
        </div>
    </div>
</section>

@if($relatedPosts->isNotEmpty())
<section class="section" style="background:#f8fafc;">
    <div class="container">
        <h2 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:24px;">Artigos relacionados</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
            @foreach($relatedPosts as $related)
            <a href="{{ route('blog.show', $related->slug) }}" style="text-decoration:none;">
                <div class="pub-card" style="padding:24px;cursor:pointer;">
                    @if($related->category)
                    <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#4a6cf7;margin-bottom:6px;">{{ $related->category->name }}</div>
                    @endif
                    <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;line-height:1.4;margin-bottom:8px;">{{ $related->title }}</h3>
                    <span style="font-size:.78rem;color:#94a3b8;">{{ $related->published_at?->format('d/m/Y') }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

</article>

<style>
.prose-content h2{font-size:1.4rem;font-weight:700;color:#0f172a;margin:2em 0 .75em;}
.prose-content h3{font-size:1.15rem;font-weight:600;color:#1e293b;margin:1.75em 0 .65em;}
.prose-content p{margin-bottom:1.25em;}
.prose-content ul,.prose-content ol{padding-left:1.5rem;margin-bottom:1.25em;}
.prose-content li{margin-bottom:.4em;}
.prose-content strong{font-weight:700;color:#0f172a;}
.prose-content blockquote{border-left:3px solid #4a6cf7;padding-left:1rem;margin:1.5em 0;color:#475569;font-style:italic;}
.prose-content code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.875em;color:#0f172a;}
.prose-content pre{background:#0f1117;padding:20px;border-radius:10px;overflow-x:auto;margin:1.5em 0;}
.prose-content pre code{background:transparent;color:#e2e8f0;padding:0;}
.prose-content a{color:#4a6cf7;text-decoration:underline;}
.prose-content hr{border:none;border-top:1px solid #e2e8f0;margin:2em 0;}
</style>

</x-layouts.public>
