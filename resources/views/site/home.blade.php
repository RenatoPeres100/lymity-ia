<x-layouts.public title="Lymity AI Agency" meta-description="Sua operação de crescimento digital com inteligência artificial. Tráfego pago, SEO, automação, conteúdo e funcionários IA em uma plataforma.">

{{-- Hero --}}
<section style="background:linear-gradient(160deg,#0f1117 0%,#0d1c54 60%,#0f1117 100%);min-height:92vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px;">
        <div style="max-width:760px;">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(74,108,247,0.12);border:1px solid rgba(74,108,247,0.25);color:#6b8fff;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:6px 16px;border-radius:20px;margin-bottom:28px;">
                ✦ Plataforma SaaS de Crescimento com IA
            </div>
            <h1 style="font-size:clamp(2.4rem,5vw,4.2rem);font-weight:800;color:#fff;line-height:1.1;letter-spacing:-0.03em;margin-bottom:22px;">
                Sua operação de crescimento,<br>
                <span style="background:linear-gradient(90deg,#6b8fff,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">agora com IA trabalhando todos os dias.</span>
            </h1>
            <p style="font-size:1.15rem;color:#94a3b8;line-height:1.75;margin-bottom:40px;max-width:600px;">
                Unimos tráfego, automação, SEO, conteúdo, dados e desenvolvimento em uma única operação — com funcionários IA especializados, aprovação humana e rastreabilidade total.
            </p>
            <div style="display:flex;gap:14px;flex-wrap:wrap;">
                <a href="{{ route('contato') }}" class="pub-btn-primary" style="padding:14px 32px;font-size:1rem;">
                    Solicitar diagnóstico gratuito →
                </a>
                <a href="{{ route('plataforma') }}" class="pub-btn-outline-light" style="padding:14px 28px;font-size:1rem;">
                    Ver a plataforma
                </a>
            </div>
        </div>

        {{-- floating cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-top:56px;max-width:800px;">
            @foreach([
                ['🤖','8 Funcionários IA','Social Media, Copywriter, SEO, Tráfego e mais'],
                ['✅','Aprovação obrigatória','Toda ação sensível exige aprovação humana'],
                ['📊','Dados centralizados','Um painel com tudo que importa'],
                ['🔄','Operação contínua','Seus processos rodando 24h/dia'],
            ] as [$icon,$title,$desc])
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:18px;backdrop-filter:blur(10px);">
                <div style="font-size:1.3rem;margin-bottom:8px;">{{ $icon }}</div>
                <div style="font-size:.85rem;font-weight:600;color:#e2e8f0;margin-bottom:4px;">{{ $title }}</div>
                <div style="font-size:.78rem;color:#64748b;line-height:1.5;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Diferenciais --}}
<section class="section" style="background:#f8fafc;">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px;">
            <div class="section-label">Por que a Lymity</div>
            <h2 class="section-title">Não criamos apenas campanhas.<br>Construímos sistemas de crescimento.</h2>
            <p class="section-subtitle" style="margin:0 auto;">A diferença entre uma agência comum e uma operação de crescimento está na integração. Aqui, cada peça trabalha em conjunto.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
            @foreach([
                ['🎯','Estratégia integrada','Não somos especialistas em uma só coisa. Integramos tráfego, SEO, conteúdo, automação e dados em um único sistema coerente.'],
                ['⚡','Execução com IA','Funcionários IA especializados executam tarefas operacionais com velocidade e consistência que times humanos não conseguem manter sozinhos.'],
                ['🛡️','Controle humano total','Toda ação sensível — publicação, envio, alteração de campanha — exige aprovação. Você mantém o controle. A IA executa.'],
                ['📈','Crescimento previsível','Sistemas bem construídos crescem de forma previsível. Com dados e automação, você sai do modo reativo e entra no modo estratégico.'],
                ['🔍','Rastreabilidade total','Cada ação tem origem, contexto e log. Você sabe o que foi feito, quando, por quem (ou qual IA) e com que resultado.'],
                ['🚀','Evolução contínua','A plataforma e os agentes evoluem. O que começa como automação simples se torna inteligência operacional ao longo do tempo.'],
            ] as [$icon,$title,$desc])
            <div class="pub-card">
                <div style="font-size:1.8rem;margin-bottom:14px;">{{ $icon }}</div>
                <div style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:8px;">{{ $title }}</div>
                <div style="font-size:.85rem;color:#64748b;line-height:1.7;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Funcionários IA --}}
<section class="section section-dark">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px;">
            <div class="section-label">Funcionários IA</div>
            <h2 class="section-title" style="color:#fff;">Funcionários IA especializados operando<br>com estratégia, aprovação e rastreabilidade.</h2>
            <p class="section-subtitle" style="margin:0 auto;">Cada funcionário IA tem função específica, habilidades mapeadas e rotinas definidas — com supervisão humana nos momentos críticos.</p>
        </div>
        @if($aiEmployees->isNotEmpty())
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-bottom:36px;">
            @foreach($aiEmployees as $employee)
            <div class="pub-card-dark">
                <div style="font-size:2rem;margin-bottom:12px;">{{ $employee->avatar_emoji }}</div>
                <div style="font-size:.95rem;font-weight:600;color:#f1f5f9;margin-bottom:6px;">{{ $employee->name }}</div>
                <div style="font-size:.8rem;color:#94a3b8;line-height:1.6;">{{ mb_substr($employee->description, 0, 100) }}...</div>
            </div>
            @endforeach
        </div>
        @endif
        <div style="text-align:center;">
            <a href="{{ route('funcionarios-ia') }}" class="pub-btn-outline-light">Ver todos os funcionários IA →</a>
        </div>
    </div>
</section>

{{-- Sistema operacional --}}
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;">
            <div>
                <div class="section-label">Sistema de crescimento</div>
                <h2 class="section-title">Do tráfego ao CRM, tudo conectado numa operação inteligente.</h2>
                <p style="font-size:1rem;color:#64748b;line-height:1.75;margin-bottom:28px;">Sua empresa não precisa de mais ferramentas desconectadas. Precisa de um sistema onde tráfego, conteúdo, SEO e dados trabalham juntos — alimentados por IA e supervisionados por pessoas.</p>
                <div style="display:flex;flex-direction:column;gap:14px;">
                    @foreach([
                        ['Aquisição inteligente','Tráfego pago e orgânico com IA otimizando continuamente'],
                        ['Conversão automatizada','Landing pages, copy e nurturing funcionando 24h'],
                        ['Análise em tempo real','Dados que geram diagnósticos e ações concretas'],
                        ['Execução com supervisão','IA executa, humano aprova, plataforma registra'],
                    ] as [$title,$desc])
                    <div style="display:flex;gap:14px;align-items:flex-start;">
                        <div style="width:20px;height:20px;border-radius:50%;background:#eff6ff;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                            <div style="width:6px;height:6px;border-radius:50%;background:#4a6cf7;"></div>
                        </div>
                        <div>
                            <div style="font-size:.9rem;font-weight:600;color:#0f172a;margin-bottom:3px;">{{ $title }}</div>
                            <div style="font-size:.82rem;color:#64748b;">{{ $desc }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div style="background:linear-gradient(135deg,#eff6ff,#f0fdf4);border-radius:20px;padding:40px;border:1px solid #e2e8f0;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    @foreach([
                        ['Tráfego Pago','#4a6cf7','#eff6ff'],
                        ['SEO','#22c55e','#f0fdf4'],
                        ['Automação','#f97316','#fff7ed'],
                        ['Conteúdo','#8b5cf6','#faf5ff'],
                        ['Dados','#06b6d4','#f0fdfa'],
                        ['CRM','#f43f5e','#fff1f2'],
                    ] as [$label,$color,$bg])
                    <div style="background:#fff;border-radius:10px;padding:16px;text-align:center;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                        <div style="width:36px;height:36px;border-radius:8px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                            <div style="width:12px;height:12px;border-radius:50%;background:{{ $color }};"></div>
                        </div>
                        <div style="font-size:.8rem;font-weight:600;color:#334155;">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Serviços --}}
<section class="section" style="background:#f8fafc;">
    <div class="container">
        <div style="text-align:center;margin-bottom:48px;">
            <div class="section-label">Serviços</div>
            <h2 class="section-title">Uma operação completa.<br>Integrada e escalável.</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
            @foreach([
                ['🎯','Tráfego Pago','Google Ads e Meta Ads gerenciados com inteligência'],
                ['🔍','SEO','Otimização técnica e editorial para crescimento orgânico'],
                ['⚡','Automação','Fluxos inteligentes que operam sem intervenção manual'],
                ['✍️','Conteúdo','Posts, artigos, copies e landing pages de alto impacto'],
                ['💻','Desenvolvimento','Sites, sistemas e integrações digitais'],
                ['📊','Dados & Analytics','Dashboards, relatórios e insights para decisões'],
                ['📱','Social Media','Calendários, posts e estratégia para redes sociais'],
                ['🤝','CRM & Vendas','Qualificação de leads e apoio ao processo comercial'],
            ] as [$icon,$title,$desc])
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:22px;text-align:center;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
                <div style="font-size:1.6rem;margin-bottom:10px;">{{ $icon }}</div>
                <div style="font-size:.875rem;font-weight:700;color:#0f172a;margin-bottom:6px;">{{ $title }}</div>
                <div style="font-size:.78rem;color:#64748b;line-height:1.6;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
        <div style="text-align:center;margin-top:36px;">
            <a href="{{ route('servicos') }}" class="pub-btn-outline">Ver todos os serviços →</a>
        </div>
    </div>
</section>

{{-- CTA Final --}}
<section class="section" style="background:linear-gradient(135deg,#0f1117,#1a2a6c);">
    <div class="container" style="text-align:center;max-width:700px;">
        <div style="font-size:2.4rem;margin-bottom:18px;">🚀</div>
        <h2 style="font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:800;color:#fff;line-height:1.2;margin-bottom:16px;letter-spacing:-0.02em;">
            Pronto para construir sua operação de crescimento?
        </h2>
        <p style="color:#94a3b8;font-size:1rem;line-height:1.7;margin-bottom:36px;">
            Solicite um diagnóstico gratuito e descubra como a Lymity pode transformar sua presença digital em um sistema de crescimento real.
        </p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('contato') }}" class="pub-btn-primary" style="padding:15px 36px;font-size:1rem;">
                Solicitar diagnóstico gratuito →
            </a>
            <a href="{{ route('funcionarios-ia') }}" class="pub-btn-outline-light" style="padding:15px 28px;font-size:1rem;">
                Conhecer os Funcionários IA
            </a>
        </div>
    </div>
</section>

</x-layouts.public>
