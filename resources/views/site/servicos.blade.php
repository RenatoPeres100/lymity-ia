<x-layouts.public title="Serviços" meta-description="Tráfego pago, SEO, automação, conteúdo, desenvolvimento e muito mais — integrados em uma operação de crescimento digital com IA.">

<section class="section" style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);min-height:44vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px 64px;">
        <div style="max-width:660px;">
            <div class="section-label" style="background:rgba(74,108,247,.1);border-color:rgba(74,108,247,.25);">Serviços</div>
            <h1 style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.025em;margin-bottom:16px;">
                Uma operação completa.<br>Integrada. Escalável.
            </h1>
            <p style="color:#94a3b8;font-size:1rem;line-height:1.75;">Não oferecemos serviços avulsos. Construímos sistemas onde cada disciplina potencializa as demais.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;gap:24px;">
            @foreach([
                ['🎯','Tráfego Pago','google-ads,meta-ads','Gestão estratégica de campanhas no Google Ads e Meta Ads. Com IA analisando performance continuamente, sua verba rende mais e os resultados melhoram progressivamente.',['Gestão Google Ads','Gestão Meta Ads','Criação de criativos','Otimização contínua','Relatórios semanais','Landing pages']],
                ['🔍','SEO','seo-organico','Otimização para mecanismos de busca que gera tráfego qualificado de forma sustentável. Do técnico ao editorial, cobrimos tudo.',['Auditoria técnica','Pesquisa de palavras-chave','Otimização on-page','Link building','Blog otimizado','Relatórios mensais']],
                ['⚡','Automação','automacao-marketing','Fluxos automáticos que nutrem leads, convertem clientes e retêm quem já comprou — operando 24h sem intervenção manual.',['E-mail marketing','Fluxos de nutrição','CRM integrado','WhatsApp automation','Remarketing automático','Integrações via API']],
                ['✍️','Conteúdo','conteudo-digital','Conteúdo estratégico que atrai, educa e converte — com copy persuasivo, artigos de autoridade e materiais para todas as etapas do funil.',['Posts para redes sociais','Artigos de blog','Landing pages','E-mails e newsletters','Scripts de vídeo','Anúncios e copies']],
                ['💻','Desenvolvimento','desenvolvimento-web','Sites, landing pages, sistemas e integrações que entregam performance, segurança e experiência de alto nível.',['Sites institucionais','Landing pages','E-commerce','Sistemas sob medida','APIs e integrações','Manutenção e evolução']],
                ['📊','Dados & Analytics','dados-analytics','Transformamos dados em diagnósticos e decisões. Dashboards claros, relatórios acionáveis e insights que guiam estratégia.',['Setup de Analytics','Dashboards personalizados','Relatórios de performance','Diagnósticos mensais','Mapeamento de funil','Insights com IA']],
            ] as [$icon,$title,$slug,$desc,$items])
            <div class="pub-card" style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;padding:32px;">
                <div>
                    <div style="font-size:2.4rem;margin-bottom:14px;">{{ $icon }}</div>
                    <h2 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:10px;">{{ $title }}</h2>
                    <p style="color:#64748b;font-size:.9rem;line-height:1.75;">{{ $desc }}</p>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:12px;">Inclui</div>
                    <ul style="list-style:none;padding:0;margin:0;">
                        @foreach($items as $item)
                        <li style="display:flex;align-items:center;gap:8px;font-size:.85rem;color:#475569;padding:5px 0;border-bottom:1px solid #f1f5f9;">
                            <span style="color:#4a6cf7;font-size:.7rem;">✓</span>{{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section" style="background:#f8fafc;">
    <div class="container" style="text-align:center;max-width:600px;">
        <h2 class="section-title">Quer saber qual combinação faz sentido para o seu negócio?</h2>
        <p class="section-subtitle" style="margin:0 auto 32px;">Cada empresa tem um contexto diferente. Solicite um diagnóstico e veja como estruturar sua operação.</p>
        <a href="{{ route('contato') }}" class="pub-btn-primary">Solicitar diagnóstico gratuito →</a>
    </div>
</section>

</x-layouts.public>
