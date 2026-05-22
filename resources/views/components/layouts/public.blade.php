<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lymity AI Agency' }} | Operação de crescimento com IA</title>
    @if(!empty($metaDescription))
    <meta name="description" content="{{ $metaDescription }}">
    @endif
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Public site overrides */
        body { background: #fff; color: #0f172a; }

        .pub-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            height: 68px;
            display: flex; align-items: center;
            transition: box-shadow 0.2s ease;
        }
        .pub-nav.scrolled { box-shadow: 0 2px 20px rgba(0,0,0,0.08); }
        .pub-nav-inner {
            max-width: 1200px; margin: 0 auto; width: 100%;
            padding: 0 32px;
            display: flex; align-items: center; justify-content: space-between; gap: 32px;
        }
        .pub-logo {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; flex-shrink: 0;
        }
        .pub-logo-icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #4a6cf7, #2d44aa);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
        }
        .pub-logo-text { font-size: 1rem; font-weight: 700; color: #0f172a; }
        .pub-logo-text span { color: #4a6cf7; }

        .pub-menu {
            display: flex; align-items: center; gap: 4px; flex: 1; justify-content: center;
        }
        .pub-menu a {
            font-size: 0.875rem; font-weight: 500; color: #475569;
            padding: 7px 12px; border-radius: 8px;
            text-decoration: none; transition: all 0.15s;
            white-space: nowrap;
        }
        .pub-menu a:hover { color: #0f172a; background: #f1f5f9; }
        .pub-menu a.active { color: #4a6cf7; background: #eff6ff; }

        .pub-cta {
            display: flex; align-items: center; gap: 10px; flex-shrink: 0;
        }
        .pub-btn-login {
            font-size: 0.8rem; font-weight: 500; color: #64748b;
            padding: 7px 14px; border-radius: 8px; border: 1px solid #e2e8f0;
            text-decoration: none; transition: all 0.15s;
        }
        .pub-btn-login:hover { border-color: #cbd5e1; color: #334155; }
        .pub-btn-cta {
            font-size: 0.875rem; font-weight: 600; color: #fff;
            background: #4a6cf7;
            padding: 9px 20px; border-radius: 9px;
            text-decoration: none; transition: all 0.15s;
            white-space: nowrap;
        }
        .pub-btn-cta:hover { background: #3a58d6; }

        .pub-hamburger {
            display: none; background: none; border: none;
            cursor: pointer; padding: 6px; color: #475569;
        }

        .pub-content { padding-top: 68px; }

        .pub-footer {
            background: #0f1117;
            color: #64748b;
            padding: 64px 32px 32px;
        }
        .pub-footer-inner { max-width: 1200px; margin: 0 auto; }
        .pub-footer-grid {
            display: grid;
            grid-template-columns: 1.5fr repeat(3, 1fr);
            gap: 48px;
            padding-bottom: 48px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 32px;
        }
        .pub-footer-brand {}
        .pub-footer-brand-name { color: #fff; font-weight: 700; font-size: 1rem; margin-bottom: 10px; }
        .pub-footer-brand-desc { font-size: 0.82rem; line-height: 1.7; max-width: 280px; }
        .pub-footer-col-title {
            color: #e2e8f0; font-weight: 600; font-size: 0.82rem; margin-bottom: 14px;
            letter-spacing: 0.02em;
        }
        .pub-footer-col a {
            display: block; color: #64748b; font-size: 0.82rem;
            text-decoration: none; margin-bottom: 9px; transition: color 0.15s;
        }
        .pub-footer-col a:hover { color: #94a3b8; }
        .pub-footer-bottom {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
            font-size: 0.78rem;
        }
        .pub-footer-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(74,108,247,0.1); border: 1px solid rgba(74,108,247,0.2);
            color: #6b8fff; font-size: 0.72rem; font-weight: 600;
            padding: 4px 12px; border-radius: 20px; letter-spacing: 0.04em;
        }

        /* Section helpers */
        .section { padding: 80px 32px; }
        .section-sm { padding: 56px 32px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section-label {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #4a6cf7;
            background: #eff6ff; border: 1px solid #bfdbfe;
            padding: 5px 14px; border-radius: 20px; margin-bottom: 18px;
        }
        .section-title {
            font-size: clamp(1.6rem, 3vw, 2.5rem);
            font-weight: 800; color: #0f172a; line-height: 1.2;
            letter-spacing: -0.02em; margin-bottom: 14px;
        }
        .section-subtitle {
            font-size: 1.05rem; color: #64748b; line-height: 1.7;
            max-width: 600px; margin-bottom: 48px;
        }
        .section-dark { background: #0f1117; }
        .section-dark .section-title { color: #fff; }
        .section-dark .section-subtitle { color: #64748b; }
        .section-dark .section-label {
            background: rgba(74,108,247,0.1); border-color: rgba(74,108,247,0.2);
        }

        /* Pub card */
        .pub-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 28px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .pub-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .pub-card-dark {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px; padding: 28px;
            transition: border-color 0.2s, background 0.2s;
        }
        .pub-card-dark:hover {
            border-color: rgba(74,108,247,0.3);
            background: rgba(74,108,247,0.04);
        }

        /* Pub buttons */
        .pub-btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #4a6cf7; color: #fff; font-weight: 600;
            padding: 13px 28px; border-radius: 10px; font-size: 0.95rem;
            text-decoration: none; transition: all 0.15s; border: none; cursor: pointer;
        }
        .pub-btn-primary:hover { background: #3a58d6; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(74,108,247,0.3); }
        .pub-btn-outline {
            display: inline-flex; align-items: center; gap: 8px;
            background: transparent; color: #334155;
            border: 1px solid #e2e8f0; font-weight: 500;
            padding: 13px 28px; border-radius: 10px; font-size: 0.95rem;
            text-decoration: none; transition: all 0.15s;
        }
        .pub-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; }
        .pub-btn-outline-light {
            display: inline-flex; align-items: center; gap: 8px;
            background: transparent; color: #e2e8f0;
            border: 1px solid rgba(255,255,255,0.15); font-weight: 500;
            padding: 13px 28px; border-radius: 10px; font-size: 0.95rem;
            text-decoration: none; transition: all 0.15s;
        }
        .pub-btn-outline-light:hover { border-color: rgba(255,255,255,0.3); background: rgba(255,255,255,0.05); }

        /* Mobile nav */
        .pub-mobile-menu {
            display: none; position: fixed; top: 68px; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.98); backdrop-filter: blur(20px);
            z-index: 99; padding: 24px; overflow-y: auto;
        }
        .pub-mobile-menu.open { display: block; }
        .pub-mobile-menu a {
            display: block; font-size: 1rem; font-weight: 500; color: #334155;
            padding: 14px 16px; border-radius: 10px; text-decoration: none;
            margin-bottom: 4px; transition: all 0.15s;
        }
        .pub-mobile-menu a:hover { background: #f1f5f9; color: #0f172a; }

        @media (max-width: 900px) {
            .pub-menu { display: none; }
            .pub-cta .pub-btn-login { display: none; }
            .pub-hamburger { display: block; }
            .pub-footer-grid { grid-template-columns: 1fr 1fr; gap: 32px; }
        }
        @media (max-width: 640px) {
            .pub-nav-inner { padding: 0 20px; }
            .section { padding: 56px 20px; }
            .section-sm { padding: 40px 20px; }
            .pub-footer { padding: 48px 20px 24px; }
            .pub-footer-grid { grid-template-columns: 1fr; gap: 28px; }
        }
    </style>
</head>
<body>

{{-- Navigation --}}
<nav class="pub-nav" id="pubNav">
    <div class="pub-nav-inner">
        <a href="{{ route('home') }}" class="pub-logo">
            <div class="pub-logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <span class="pub-logo-text">Lymity <span>AI</span></span>
        </a>

        <nav class="pub-menu">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Início</a>
            <a href="{{ route('sobre') }}" class="{{ request()->routeIs('sobre') ? 'active' : '' }}">Sobre</a>
            <a href="{{ route('servicos') }}" class="{{ request()->routeIs('servicos') ? 'active' : '' }}">Serviços</a>
            <a href="{{ route('plataforma') }}" class="{{ request()->routeIs('plataforma') ? 'active' : '' }}">Plataforma</a>
            <a href="{{ route('funcionarios-ia') }}" class="{{ request()->routeIs('funcionarios-ia') ? 'active' : '' }}">Funcionários IA</a>
            <a href="{{ route('cases') }}" class="{{ request()->routeIs('cases') ? 'active' : '' }}">Cases</a>
            <a href="{{ route('blog') }}" class="{{ request()->routeIs('blog*') ? 'active' : '' }}">Blog</a>
        </nav>

        <div class="pub-cta">
            <a href="{{ route('login') }}" class="pub-btn-login">Entrar</a>
            <a href="{{ route('contato') }}" class="pub-btn-cta">Solicitar diagnóstico</a>
            <button class="pub-hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
                <svg id="menuIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
                <svg id="menuIconClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>
</nav>

{{-- Mobile Menu --}}
<div class="pub-mobile-menu" id="mobileMenu">
    <a href="{{ route('home') }}">Início</a>
    <a href="{{ route('sobre') }}">Sobre</a>
    <a href="{{ route('servicos') }}">Serviços</a>
    <a href="{{ route('plataforma') }}">Plataforma</a>
    <a href="{{ route('funcionarios-ia') }}">Funcionários IA</a>
    <a href="{{ route('cases') }}">Cases</a>
    <a href="{{ route('blog') }}">Blog</a>
    <a href="{{ route('contato') }}">Contato</a>
    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col gap-2">
        <a href="{{ route('login') }}" class="text-center border border-slate-200 rounded-xl py-3 text-sm font-medium text-slate-600">Entrar na plataforma</a>
        <a href="{{ route('contato') }}" class="pub-btn-primary justify-center">Solicitar diagnóstico</a>
    </div>
</div>

{{-- Content --}}
<div class="pub-content">
    {{ $slot }}
</div>

{{-- Footer --}}
<footer class="pub-footer">
    <div class="pub-footer-inner">
        <div class="pub-footer-grid">
            <div class="pub-footer-brand">
                <div class="flex items-center gap-2 mb-4">
                    <div class="pub-logo-icon" style="width:30px;height:30px;border-radius:8px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <span class="pub-footer-brand-name">Lymity AI Agency</span>
                </div>
                <p class="pub-footer-brand-desc">Operação de crescimento digital com inteligência artificial. Tráfego, SEO, automação, conteúdo e dados em uma única plataforma.</p>
            </div>

            <div class="pub-footer-col">
                <div class="pub-footer-col-title">Empresa</div>
                <a href="{{ route('sobre') }}">Sobre</a>
                <a href="{{ route('plataforma') }}">Plataforma</a>
                <a href="{{ route('funcionarios-ia') }}">Funcionários IA</a>
                <a href="{{ route('cases') }}">Cases</a>
            </div>

            <div class="pub-footer-col">
                <div class="pub-footer-col-title">Serviços</div>
                <a href="{{ route('servicos') }}">Tráfego Pago</a>
                <a href="{{ route('servicos') }}">SEO</a>
                <a href="{{ route('servicos') }}">Automação</a>
                <a href="{{ route('servicos') }}">Conteúdo</a>
                <a href="{{ route('servicos') }}">Desenvolvimento</a>
            </div>

            <div class="pub-footer-col">
                <div class="pub-footer-col-title">Recursos</div>
                <a href="{{ route('blog') }}">Blog</a>
                <a href="{{ route('contato') }}">Contato</a>
                <a href="{{ route('login') }}">Área do cliente</a>
            </div>
        </div>

        <div class="pub-footer-bottom">
            <span>© {{ date('Y') }} Lymity AI Agency. Todos os direitos reservados.</span>
            <span class="pub-footer-badge">✦ Powered by Claude AI</span>
        </div>
    </div>
</footer>

<script>
// Scroll effect on nav
window.addEventListener('scroll', function() {
    document.getElementById('pubNav').classList.toggle('scrolled', window.scrollY > 20);
});

// Mobile menu
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const open = document.getElementById('menuIconOpen');
    const close = document.getElementById('menuIconClose');
    const isOpen = menu.classList.toggle('open');
    open.classList.toggle('hidden', isOpen);
    close.classList.toggle('hidden', !isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
}
</script>

</body>
</html>
