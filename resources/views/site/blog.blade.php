<x-layouts.public title="Blog" meta-description="Artigos sobre marketing digital, IA, tráfego pago, SEO, automação e estratégias de crescimento para empresas.">

<section class="section" style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);min-height:36vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px 64px;">
        <div style="max-width:640px;">
            <div class="section-label" style="background:rgba(74,108,247,.1);border-color:rgba(74,108,247,.25);">Blog</div>
            <h1 style="font-size:clamp(2rem,4vw,3rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.025em;margin-bottom:16px;">
                Estratégia, IA e crescimento digital.
            </h1>
            <p style="color:#94a3b8;font-size:1rem;line-height:1.75;">Conteúdo para quem quer crescer de forma consistente — com dados, tecnologia e execução.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        @if($posts->isNotEmpty())

        {{-- Featured post --}}
        @php $featured = $posts->first(); @endphp
        <div class="pub-card" style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center;padding:40px;margin-bottom:40px;">
            <div style="background:linear-gradient(135deg,#eff6ff,#f0fdf4);border-radius:12px;height:240px;display:flex;align-items:center;justify-content:center;">
                <span style="font-size:4rem;">{{ $featured->category?->icon ?? '📝' }}</span>
            </div>
            <div>
                @if($featured->category)
                <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#4a6cf7;margin-bottom:8px;">{{ $featured->category->name }}</div>
                @endif
                <h2 style="font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1.3;margin-bottom:12px;">{{ $featured->title }}</h2>
                <p style="font-size:.875rem;color:#64748b;line-height:1.75;margin-bottom:20px;">{{ $featured->excerpt ?? Str::limit(strip_tags($featured->content), 160) }}</p>
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
                    <span style="font-size:.78rem;color:#94a3b8;">{{ $featured->published_at?->format('d/m/Y') }}</span>
                    <span style="font-size:.78rem;color:#94a3b8;">{{ $featured->read_time }} min de leitura</span>
                </div>
                <a href="{{ route('blog.show', $featured->slug) }}" class="pub-btn-primary" style="display:inline-block;">Ler artigo →</a>
            </div>
        </div>

        {{-- Post grid --}}
        @if($posts->count() > 1)
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;">
            @foreach($posts->skip(1) as $post)
            <a href="{{ route('blog.show', $post->slug) }}" style="text-decoration:none;">
                <div class="pub-card" style="padding:28px;cursor:pointer;transition:transform .2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                    <div style="background:linear-gradient(135deg,#f8fafc,#eff6ff);border-radius:10px;height:120px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                        <span style="font-size:2.4rem;">{{ $post->category?->icon ?? '📝' }}</span>
                    </div>
                    @if($post->category)
                    <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#4a6cf7;margin-bottom:6px;">{{ $post->category->name }}</div>
                    @endif
                    <h3 style="font-size:.95rem;font-weight:700;color:#0f172a;line-height:1.4;margin-bottom:8px;">{{ $post->title }}</h3>
                    <p style="font-size:.8rem;color:#64748b;line-height:1.65;margin-bottom:16px;">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 110) }}</p>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:.75rem;color:#94a3b8;">{{ $post->published_at?->format('d/m/Y') }}</span>
                        <span style="font-size:.75rem;color:#94a3b8;">{{ $post->read_time }} min</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Pagination --}}
        @if($posts->hasPages())
        <div style="margin-top:48px;display:flex;justify-content:center;">
            {{ $posts->links() }}
        </div>
        @endif

        @else
        <div style="text-align:center;padding:80px 0;">
            <div style="font-size:2.5rem;margin-bottom:16px;">✍️</div>
            <h2 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:8px;">Artigos em breve</h2>
            <p style="color:#64748b;max-width:400px;margin:0 auto;">Estamos preparando conteúdo de qualidade. Volte em breve.</p>
        </div>
        @endif
    </div>
</section>

</x-layouts.public>
