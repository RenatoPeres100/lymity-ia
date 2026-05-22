<x-layouts.guest title="Lymity AI Agency — Agência Digital Inteligente">

<div class="home-bg">

    {{-- Nav --}}
    <nav class="home-nav">
        <a href="/" class="flex items-center gap-2.5 no-underline">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-800 rounded-lg flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <span class="text-white font-semibold text-base">Lymity AI Agency</span>
        </a>

        <a href="{{ route('login') }}" class="btn btn-primary text-sm">
            Acessar plataforma →
        </a>
    </nav>

    {{-- Hero --}}
    <section class="text-center px-6 pt-20 pb-16 max-w-3xl mx-auto">
        <div class="hero-eyebrow">
            ✦ Plataforma SaaS para Agências Digitais
        </div>

        <h1 class="hero-title">
            Sua agência digital<br>
            operada por <span>Inteligência Artificial</span>
        </h1>

        <p class="text-lg text-slate-400 leading-relaxed mb-10 max-w-2xl mx-auto">
            Funcionários IA especializados, criação de conteúdo automatizada, gestão de clientes, campanhas de tráfego e SEO — tudo em uma plataforma.
        </p>

        <div class="flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('login') }}" class="btn btn-primary px-8 py-3.5 text-base font-semibold rounded-xl">
                Acessar plataforma
            </a>
            <a href="#recursos" class="btn btn-secondary bg-white/5 text-slate-300 border-white/10 hover:bg-white/10 px-8 py-3.5 text-base rounded-xl">
                Ver recursos
            </a>
        </div>
    </section>

    {{-- Stats bar --}}
    <div class="flex items-center justify-center gap-10 flex-wrap px-6 pb-16 text-center">
        @foreach([['8', 'Funcionários IA'], ['∞', 'Conteúdos gerados'], ['100%', 'Aprovação humana'], ['24/7', 'Operação contínua']] as [$num, $label])
        <div>
            <div class="text-3xl font-bold text-white mb-1">{{ $num }}</div>
            <div class="text-sm text-slate-500">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Features --}}
    <section id="recursos" class="max-w-5xl mx-auto px-6 pb-20">
        <h2 class="text-center text-2xl font-bold text-white mb-3">Recursos da plataforma</h2>
        <p class="text-center text-slate-400 mb-10 text-sm">Uma operação completa de agência digital, automatizada.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="feature-card">
                <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2z"/>
                        <path d="M12 8v4l3 3"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-slate-100 mb-1.5">Funcionários IA</div>
                <div class="text-xs text-slate-500 leading-relaxed">Social Media, Copywriter, SEO, Tráfego, Designer, SDR e mais.</div>
            </div>

            <div class="feature-card">
                <div class="w-10 h-10 rounded-xl bg-purple-500/15 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-slate-100 mb-1.5">Conteúdo e SEO</div>
                <div class="text-xs text-slate-500 leading-relaxed">Posts, blogs, artigos e otimização para busca gerados automaticamente.</div>
            </div>

            <div class="feature-card">
                <div class="w-10 h-10 rounded-xl bg-orange-500/15 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-slate-100 mb-1.5">Tráfego Pago</div>
                <div class="text-xs text-slate-500 leading-relaxed">Apoio em campanhas Google Ads e Meta Ads com análise inteligente.</div>
            </div>

            <div class="feature-card">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-slate-100 mb-1.5">Aprovações e Logs</div>
                <div class="text-xs text-slate-500 leading-relaxed">Controle total. Toda ação sensível requer aprovação humana.</div>
            </div>

        </div>
    </section>

    {{-- Footer --}}
    <footer class="text-center py-8 border-t border-white/5 text-slate-600 text-xs">
        © {{ date('Y') }} Lymity AI Agency — Fase 1 · Base SaaS
    </footer>

</div>

</x-layouts.guest>
