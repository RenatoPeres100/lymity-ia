<x-layouts.public title="Plataforma" meta-description="Conheça a plataforma Lymity: dashboard, funcionários IA, aprovações, tarefas, logs e operação completa de crescimento digital integrada.">

<section class="section" style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);min-height:44vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px 64px;">
        <div style="max-width:680px;">
            <div class="section-label" style="background:rgba(74,108,247,.1);border-color:rgba(74,108,247,.25);">Plataforma</div>
            <h1 style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.025em;margin-bottom:16px;">
                Uma plataforma operacional.<br>Não só um painel.
            </h1>
            <p style="color:#94a3b8;font-size:1.05rem;line-height:1.75;">Cada módulo da Lymity foi projetado para conectar execução, aprovação, rastreabilidade e inteligência em um único sistema coerente.</p>
        </div>
    </div>
</section>

{{-- Visão geral --}}
<section class="section">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px;">
            <div class="section-label">Módulos da plataforma</div>
            <h2 class="section-title">Tudo que sua operação precisa,<br>em um lugar só.</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;">
            @foreach([
                ['📊','Dashboard Central','Visão completa da operação em tempo real: campanhas ativas, tarefas pendentes, aprovações em aberto, tráfego, leads e performance consolidada de todos os canais.'],
                ['🤖','Funcionários IA','Oito agentes especializados operando com rotinas definidas, habilidades mapeadas e aprovação humana nos momentos críticos. Cada um com log completo de ações.'],
                ['✅','Sistema de Aprovações','Nenhuma ação sensível passa sem aprovação. Publicações, envio de e-mails, alteração de orçamento — tudo exige revisão humana antes de executar.'],
                ['📋','Gestão de Tarefas','Tarefas criadas por IA ou por humanos, com contexto, prioridade, prazo e rastreabilidade. Você sabe o que está sendo feito e por quem.'],
                ['📝','Blog & Conteúdo','Crie, revise e publique conteúdo diretamente pela plataforma. Controle de categorias, status de aprovação, SEO e histórico de versões.'],
                ['🏆','Cases & Resultados','Documente e publique resultados reais dos clientes. Cases com métricas, depoimentos e prova social gerenciados diretamente no painel.'],
                ['🎯','Campanhas & Tráfego','Integração com Google Ads e Meta Ads. Visualize performance, CPC, conversões e ROI consolidados com dados em tempo real.'],
                ['🔗','CRM & Leads','Capture, qualifique e acompanhe leads desde o primeiro contato até o fechamento. Pipeline integrado com automação de nurturing.'],
                ['📈','Analytics & Dados','Dashboards personalizados com dados de todas as fontes. Relatórios automáticos, diagnósticos mensais e insights gerados por IA.'],
                ['👥','Gestão de Clientes','Multi-empresa com acesso segmentado. Cada cliente vê apenas o que lhe pertence. Agência mantém visão global e controle total.'],
                ['⚙️','Automações','Fluxos de e-mail, WhatsApp e remarketing rodando 24h. Triggers por comportamento, segmentação dinâmica e personalização em escala.'],
                ['🔐','Logs & Auditoria','Cada ação registrada com timestamp, usuário (ou IA), contexto e resultado. Rastreabilidade total para compliance e aprendizado.'],
            ] as [$icon,$title,$desc])
            <div class="pub-card" style="padding:28px;">
                <div style="font-size:1.8rem;margin-bottom:12px;">{{ $icon }}</div>
                <div style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:8px;">{{ $title }}</div>
                <div style="font-size:.84rem;color:#64748b;line-height:1.7;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Como funciona --}}
<section class="section" style="background:#f8fafc;">
    <div class="container" style="max-width:900px;">
        <div style="text-align:center;margin-bottom:56px;">
            <div class="section-label">Como funciona</div>
            <h2 class="section-title">Do onboarding à operação em 4 etapas.</h2>
        </div>
        <div style="display:flex;flex-direction:column;gap:0;">
            @foreach([
                ['01','Diagnóstico e onboarding','Mapeamos sua operação atual, canais ativos, objetivos e contexto competitivo. Configuramos a plataforma com os dados do seu negócio.','#4a6cf7'],
                ['02','Configuração dos agentes IA','Ativamos os Funcionários IA relevantes para o seu plano, definimos rotinas, horários e parâmetros de atuação com base no diagnóstico.','#8b5cf6'],
                ['03','Aprovação e supervisão','Toda ação sensível entra na fila de aprovação. Você (ou seu gestor) revisa, aprova ou rejeita com um clique — pelo painel ou por notificação.','#06b6d4'],
                ['04','Escala e melhoria contínua','Com dados acumulados, os agentes melhoram. Relatórios mensais identificam oportunidades. A operação cresce de forma previsível.','#22c55e'],
            ] as [$num,$title,$desc,$color])
            <div style="display:flex;gap:24px;padding:32px 0;border-bottom:1px solid #e2e8f0;align-items:flex-start;">
                <div style="width:52px;height:52px;border-radius:12px;background:{{ $color }}15;border:1px solid {{ $color }}30;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-size:1rem;font-weight:800;color:{{ $color }};">{{ $num }}</span>
                </div>
                <div>
                    <div style="font-size:1.05rem;font-weight:700;color:#0f172a;margin-bottom:6px;">{{ $title }}</div>
                    <div style="font-size:.875rem;color:#64748b;line-height:1.7;">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Segurança --}}
<section class="section section-dark">
    <div class="container" style="max-width:860px;">
        <div style="text-align:center;margin-bottom:48px;">
            <div class="section-label">Segurança e controle</div>
            <h2 class="section-title" style="color:#fff;">IA executa. Humano decide.<br>Plataforma registra tudo.</h2>
            <p class="section-subtitle" style="margin:0 auto;">A arquitetura da Lymity foi projetada com segurança por padrão. Nenhuma IA tem autonomia irrestrita.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
            @foreach([
                ['🔐','Aprovação obrigatória','Publicações, envios e alterações orçamentárias sempre exigem revisão humana.'],
                ['📋','Logs imutáveis','Cada ação gera um registro permanente com contexto completo.'],
                ['👁️','Visibilidade total','Você vê o que cada agente fez, quando fez e com qual resultado.'],
                ['🚫','Sem autonomia irrestrita','Agentes não podem publicar, enviar ou alterar sem aprovação do gestor.'],
            ] as [$icon,$title,$desc])
            <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:24px;">
                <div style="font-size:1.5rem;margin-bottom:10px;">{{ $icon }}</div>
                <div style="font-size:.875rem;font-weight:600;color:#f1f5f9;margin-bottom:6px;">{{ $title }}</div>
                <div style="font-size:.8rem;color:#94a3b8;line-height:1.6;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="section">
    <div class="container" style="text-align:center;max-width:600px;">
        <h2 class="section-title">Pronto para ver a plataforma em ação?</h2>
        <p class="section-subtitle" style="margin:0 auto 32px;">Solicite um diagnóstico e descubra como configurar sua operação de crescimento.</p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('contato') }}" class="pub-btn-primary">Solicitar diagnóstico gratuito →</a>
            <a href="{{ route('funcionarios-ia') }}" class="pub-btn-outline">Conhecer os Funcionários IA</a>
        </div>
    </div>
</section>

</x-layouts.public>
