<x-layouts.public title="Sobre" meta-description="A Lymity não é uma agência comum. Somos uma operação de crescimento digital com inteligência artificial, dados e execução integrada.">

<section class="section" style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);min-height:50vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px 64px;">
        <div style="max-width:680px;">
            <div class="section-label" style="background:rgba(74,108,247,.1);border-color:rgba(74,108,247,.25);">Sobre a Lymity</div>
            <h1 style="font-size:clamp(2rem,4vw,3.4rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.025em;margin-bottom:18px;">
                Não somos uma agência.<br>Somos uma operação de crescimento.
            </h1>
            <p style="color:#94a3b8;font-size:1.1rem;line-height:1.75;">A Lymity nasceu para resolver um problema que toda empresa enfrenta: como crescer de forma consistente, previsível e escalável no digital — sem depender de sorte, de campanhas isoladas ou de equipes sobrecarregadas.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:900px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start;">
            <div>
                <h2 class="section-title" style="font-size:1.8rem;">A visão que nos move</h2>
                <p style="color:#64748b;line-height:1.8;margin-bottom:20px;">Acreditamos que crescimento digital não é resultado de uma boa campanha ou de um bom post. É resultado de um sistema. Um sistema onde tráfego, conteúdo, SEO, automação e dados trabalham juntos, de forma integrada, com rastreabilidade e melhoria contínua.</p>
                <p style="color:#64748b;line-height:1.8;">Por isso, construímos a Lymity como uma plataforma — não apenas como um conjunto de serviços. Uma plataforma onde funcionários IA especializados executam tarefas operacionais com consistência, enquanto profissionais humanos mantêm o controle estratégico e aprovam todas as ações sensíveis.</p>
            </div>
            <div>
                <h2 class="section-title" style="font-size:1.8rem;">Por que tecnologia e dados?</h2>
                <p style="color:#64748b;line-height:1.8;margin-bottom:20px;">Empresas que crescem de forma consistente tomam decisões baseadas em dados. Não em feeling. Não em intuição. Em dados reais, analisados com inteligência.</p>
                <p style="color:#64748b;line-height:1.8;">A tecnologia permite que isso aconteça em escala. Que padrões sejam identificados automaticamente. Que oportunidades sejam capturadas rapidamente. E que o time humano foque no que realmente importa: estratégia, relacionamento e crescimento.</p>
            </div>
        </div>

        <div style="margin-top:56px;padding:40px;background:#f8fafc;border-radius:16px;border:1px solid #e2e8f0;">
            <h3 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:24px;">Nossos princípios</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
                @foreach([
                    ['🎯','Resultados sobre aparência','Preferimos entregar crescimento real a parecer ocupados.'],
                    ['🔍','Transparência total','Cada ação é registrada. Cada decisão tem justificativa.'],
                    ['🤝','Supervisão humana sempre','IA executa. Humano decide. Sempre.'],
                    ['📈','Melhoria contínua','Sistemas que evoluem com os dados que geram.'],
                ] as [$icon,$title,$desc])
                <div style="padding:20px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
                    <div style="font-size:1.5rem;margin-bottom:10px;">{{ $icon }}</div>
                    <div style="font-size:.875rem;font-weight:600;color:#0f172a;margin-bottom:6px;">{{ $title }}</div>
                    <div style="font-size:.8rem;color:#64748b;line-height:1.6;">{{ $desc }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="section section-dark">
    <div class="container" style="text-align:center;max-width:660px;">
        <h2 class="section-title">Pronto para conhecer a plataforma?</h2>
        <p class="section-subtitle" style="margin:0 auto 32px;">Veja como funciona nossa operação por dentro e descubra o que os Funcionários IA podem fazer pelo seu crescimento.</p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('plataforma') }}" class="pub-btn-primary">Ver a plataforma →</a>
            <a href="{{ route('contato') }}" class="pub-btn-outline-light">Falar com a equipe</a>
        </div>
    </div>
</section>

</x-layouts.public>
