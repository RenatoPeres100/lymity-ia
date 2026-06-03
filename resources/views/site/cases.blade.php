<x-layouts.public title="Cases" meta-description="Resultados reais de clientes da Lymity: crescimento em tráfego, leads, receita e presença digital com IA e estratégia integrada.">

<section class="section" style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);min-height:40vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px 64px;">
        <div style="max-width:640px;">
            <div class="section-label" style="background:rgba(74,108,247,.1);border-color:rgba(74,108,247,.25);">Cases</div>
            <h1 style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.025em;margin-bottom:16px;">
                Resultados reais.<br>Empresas reais.
            </h1>
            <p style="color:#94a3b8;font-size:1rem;line-height:1.75;">Cada case documenta um processo de crescimento real — com contexto, estratégia, execução e métricas verificáveis.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        @if($cases->isNotEmpty())
        <div style="display:grid;gap:32px;">
            @foreach($cases as $case)
            <div class="pub-card" style="display:grid;grid-template-columns:1fr 2fr;gap:40px;align-items:start;padding:36px;">
                <div>
                    @if($case->client_name)
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#4a6cf7;margin-bottom:8px;">{{ $case->industry ?? 'Cliente' }}</div>
                    <h2 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:8px;">{{ $case->client_name }}</h2>
                    @endif
                    <h3 style="font-size:1.1rem;font-weight:600;color:#1e293b;margin-bottom:12px;">{{ $case->title }}</h3>
                    @if($case->results)
                    <div style="display:flex;flex-direction:column;gap:10px;margin-top:16px;">
                        @foreach($case->results as $metric => $value)
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;">
                            <div style="font-size:1.2rem;font-weight:800;color:#4a6cf7;">{{ $value }}</div>
                            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">{{ $metric }}</div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div>
                    <p style="font-size:.95rem;color:#475569;line-height:1.8;margin-bottom:20px;">{{ $case->challenge }}</p>
                    @if($case->solution)
                    <div style="margin-bottom:16px;">
                        <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:8px;">Solução</div>
                        <p style="font-size:.875rem;color:#64748b;line-height:1.75;">{{ $case->solution }}</p>
                    </div>
                    @endif
                    @if($case->testimonial)
                    <blockquote style="border-left:3px solid #4a6cf7;padding-left:16px;margin:20px 0 0;">
                        <p style="font-size:.875rem;color:#475569;line-height:1.75;font-style:italic;">"{{ $case->testimonial }}"</p>
                        @if($case->testimonial_author)
                        <cite style="font-size:.78rem;color:#94a3b8;font-style:normal;font-weight:600;">— {{ $case->testimonial_author }}</cite>
                        @endif
                    </blockquote>
                    @endif
                    @if($case->tags && is_array($case->tags) && count($case->tags))
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:20px;">
                        @foreach($case->tags as $tag)
                        <span style="background:#eff6ff;color:#4a6cf7;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;border:1px solid #bfdbfe;">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:80px 0;">
            <div style="font-size:2.5rem;margin-bottom:16px;">📊</div>
            <h2 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:8px;">Cases em breve</h2>
            <p style="color:#64748b;max-width:400px;margin:0 auto;">Estamos documentando os primeiros resultados. Volte em breve para ver nossa carteira de cases.</p>
        </div>
        @endif
    </div>
</section>

<section class="section" style="background:#f8fafc;">
    <div class="container" style="text-align:center;max-width:600px;">
        <h2 class="section-title">Seu negócio pode ser o próximo case.</h2>
        <p class="section-subtitle" style="margin:0 auto 32px;">Solicite um diagnóstico gratuito e veja o potencial de crescimento da sua operação digital.</p>
        <a href="{{ route('contato') }}" class="pub-btn-primary">Solicitar diagnóstico gratuito →</a>
    </div>
</section>

</x-layouts.public>
