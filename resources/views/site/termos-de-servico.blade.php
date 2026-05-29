<x-layouts.public title="Termos de Serviço" metaDescription="Leia os termos e condições que regem o uso da plataforma e dos serviços da Lymity AI Agency.">

<style>
    .legal-hero {
        background: linear-gradient(135deg, #0f1117 0%, #1e2340 100%);
        padding: 80px 32px 64px;
        text-align: center;
    }
    .legal-hero-label {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em;
        text-transform: uppercase; color: #6b8fff;
        background: rgba(74,108,247,0.12); border: 1px solid rgba(74,108,247,0.25);
        padding: 5px 14px; border-radius: 20px; margin-bottom: 20px;
    }
    .legal-hero h1 {
        font-size: clamp(1.8rem, 3.5vw, 2.8rem);
        font-weight: 800; color: #fff; line-height: 1.2;
        letter-spacing: -0.02em; margin-bottom: 14px;
    }
    .legal-hero p {
        font-size: 1rem; color: #64748b; max-width: 560px; margin: 0 auto;
        line-height: 1.7;
    }
    .legal-updated {
        font-size: 0.78rem; color: #475569; margin-top: 14px;
    }

    .legal-body {
        max-width: 820px; margin: 0 auto;
        padding: 64px 32px 80px;
    }
    .legal-toc {
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 14px; padding: 28px 32px;
        margin-bottom: 48px;
    }
    .legal-toc-title {
        font-size: 0.82rem; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: 0.08em;
        margin-bottom: 14px;
    }
    .legal-toc ol {
        margin: 0; padding-left: 20px;
        display: grid; grid-template-columns: 1fr 1fr; gap: 6px 32px;
    }
    .legal-toc ol li a {
        font-size: 0.875rem; color: #4a6cf7; text-decoration: none;
        font-weight: 500; transition: color 0.15s;
    }
    .legal-toc ol li a:hover { color: #3a58d6; }

    .legal-section {
        margin-bottom: 48px;
        scroll-margin-top: 90px;
    }
    .legal-section h2 {
        font-size: 1.2rem; font-weight: 700; color: #0f172a;
        margin-bottom: 16px; padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
        display: flex; align-items: center; gap: 10px;
    }
    .legal-section h2 span.num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 8px;
        background: #eff6ff; color: #4a6cf7;
        font-size: 0.8rem; font-weight: 800; flex-shrink: 0;
    }
    .legal-section p {
        font-size: 0.925rem; color: #475569; line-height: 1.8;
        margin-bottom: 14px;
    }
    .legal-section ul, .legal-section ol {
        padding-left: 20px; margin-bottom: 14px;
    }
    .legal-section li {
        font-size: 0.925rem; color: #475569; line-height: 1.8;
        margin-bottom: 6px;
    }
    .legal-highlight {
        background: #eff6ff; border-left: 4px solid #4a6cf7;
        border-radius: 0 10px 10px 0; padding: 16px 20px;
        margin: 20px 0;
    }
    .legal-highlight p { margin: 0; color: #334155; }
    .legal-warning {
        background: #fff7ed; border-left: 4px solid #f97316;
        border-radius: 0 10px 10px 0; padding: 16px 20px;
        margin: 20px 0;
    }
    .legal-warning p { margin: 0; color: #9a3412; }
    .legal-contact-box {
        background: #0f1117; border-radius: 14px;
        padding: 32px; margin-top: 48px; text-align: center;
    }
    .legal-contact-box h3 { color: #fff; font-size: 1.1rem; margin-bottom: 8px; }
    .legal-contact-box p { color: #64748b; font-size: 0.875rem; margin-bottom: 16px; }
    .legal-contact-box a {
        color: #6b8fff; font-weight: 600; text-decoration: none;
        font-size: 0.925rem;
    }
    .legal-contact-box a:hover { color: #4a6cf7; }

    @media (max-width: 640px) {
        .legal-body { padding: 40px 20px 60px; }
        .legal-toc ol { grid-template-columns: 1fr; }
        .legal-hero { padding: 60px 20px 48px; }
    }
</style>

{{-- Hero --}}
<section class="legal-hero">
    <div class="legal-hero-label">✦ Documento Legal</div>
    <h1>Termos de Serviço</h1>
    <p>As condições que regem o uso da plataforma Lymity AI Agency e dos serviços prestados.</p>
    <p class="legal-updated">Última atualização: Maio de 2025</p>
</section>

{{-- Body --}}
<div class="legal-body">

    {{-- Table of Contents --}}
    <div class="legal-toc">
        <div class="legal-toc-title">Índice</div>
        <ol>
            <li><a href="#aceitacao">Aceitação dos termos</a></li>
            <li><a href="#servicos">Descrição dos serviços</a></li>
            <li><a href="#conta">Conta e acesso</a></li>
            <li><a href="#uso-aceitavel">Uso aceitável</a></li>
            <li><a href="#ia-aprovacao">IA e aprovações obrigatórias</a></li>
            <li><a href="#propriedade">Propriedade intelectual</a></li>
            <li><a href="#pagamento">Pagamento e planos</a></li>
            <li><a href="#responsabilidade">Limitação de responsabilidade</a></li>
            <li><a href="#rescisao">Rescisão e cancelamento</a></li>
            <li><a href="#lei">Lei aplicável e foro</a></li>
        </ol>
    </div>

    {{-- Intro --}}
    <p style="font-size:0.975rem;color:#475569;line-height:1.8;margin-bottom:48px;">
        Estes Termos de Serviço ("Termos") constituem um contrato legal entre você ("Usuário", "Cliente" ou "você") e a <strong>Lymity AI Agency</strong> ("Lymity", "nós" ou "nosso"), empresa prestadora de serviços de marketing digital automatizado por inteligência artificial. Ao criar uma conta, acessar a plataforma ou contratar qualquer um dos nossos serviços, você concorda integralmente com estes Termos. Leia-os com atenção antes de prosseguir.
    </p>

    {{-- 1. Aceitação --}}
    <div class="legal-section" id="aceitacao">
        <h2><span class="num">1</span> Aceitação dos termos</h2>
        <p>O uso da plataforma Lymity AI Agency está condicionado à aceitação destes Termos. Se você não concorda com qualquer parte deste documento, não deve acessar ou usar nossos serviços.</p>
        <p>Ao utilizar a plataforma, você declara que:</p>
        <ul>
            <li>É maior de 18 anos e tem capacidade legal para celebrar contratos;</li>
            <li>Está agindo em nome de uma empresa ou pessoa jurídica, com autoridade para vincular tal entidade a estes Termos;</li>
            <li>As informações fornecidas durante o cadastro são verdadeiras, completas e atualizadas;</li>
            <li>Concorda com nossa <a href="{{ route('politica-privacidade') }}" style="color:#4a6cf7;font-weight:500;">Política de Privacidade</a>, incorporada a estes Termos por referência.</li>
        </ul>
    </div>

    {{-- 2. Serviços --}}
    <div class="legal-section" id="servicos">
        <h2><span class="num">2</span> Descrição dos serviços</h2>
        <p>A Lymity AI Agency oferece uma plataforma SaaS (Software as a Service) para operação de agência digital com suporte de inteligência artificial, incluindo mas não se limitando a:</p>
        <ul>
            <li><strong>Gestão de tráfego pago:</strong> criação, análise e otimização de campanhas no Google Ads e Meta Ads;</li>
            <li><strong>SEO e conteúdo:</strong> geração automatizada de conteúdo, palavras-chave, auditorias e planos de conteúdo;</li>
            <li><strong>Social Media:</strong> criação, agendamento e publicação de posts em redes sociais;</li>
            <li><strong>Automação com IA:</strong> funcionários IA especializados (Social Media IA, SEO IA, Copywriter IA, entre outros) que executam tarefas dentro de limites pré-definidos;</li>
            <li><strong>Dashboard e relatórios:</strong> painel unificado com métricas, logs e acompanhamento de resultados;</li>
            <li><strong>Área do cliente:</strong> portal dedicado para aprovação de conteúdo, visualização de entregas e comunicação;</li>
            <li><strong>Gestão comercial:</strong> propostas, contratos, orçamentos e fluxo de aprovação.</li>
        </ul>
        <p>Os serviços estão sujeitos a disponibilidade e podem ser modificados, suspensos ou descontinuados mediante aviso prévio adequado.</p>
    </div>

    {{-- 3. Conta e acesso --}}
    <div class="legal-section" id="conta">
        <h2><span class="num">3</span> Conta e acesso</h2>
        <p>Para utilizar a plataforma, você precisará criar uma conta com credenciais de acesso. Você é inteiramente responsável por:</p>
        <ul>
            <li>Manter a confidencialidade de suas credenciais (usuário e senha);</li>
            <li>Todas as atividades que ocorram sob sua conta, sejam feitas por você ou por terceiros com acesso às suas credenciais;</li>
            <li>Notificar a Lymity imediatamente em caso de uso não autorizado ou suspeita de comprometimento da conta;</li>
            <li>Gerenciar adequadamente os acessos concedidos a colaboradores e membros da equipe.</li>
        </ul>
        <p>A Lymity utiliza um sistema de controle de acesso baseado em papéis (Admin Geral, Cliente e Colaborador), cada um com permissões específicas. É responsabilidade do titular da conta garantir que os acessos concedidos sejam adequados e seguros.</p>
        <div class="legal-highlight">
            <p>A Lymity nunca solicitará sua senha por e-mail, telefone ou qualquer canal de comunicação externo. Desconfie de qualquer solicitação desse tipo.</p>
        </div>
    </div>

    {{-- 4. Uso aceitável --}}
    <div class="legal-section" id="uso-aceitavel">
        <h2><span class="num">4</span> Uso aceitável</h2>
        <p>Você concorda em usar a plataforma exclusivamente para fins legítimos e em conformidade com todas as leis aplicáveis. É expressamente proibido:</p>
        <ul>
            <li>Utilizar a plataforma para criar, distribuir ou promover conteúdo ilegal, difamatório, abusivo, enganoso ou que viole direitos de terceiros;</li>
            <li>Fazer engenharia reversa, descompilar ou tentar extrair o código-fonte da plataforma;</li>
            <li>Usar bots, scripts ou automações para acessar a plataforma de forma não autorizada;</li>
            <li>Compartilhar credenciais de acesso com terceiros não autorizados;</li>
            <li>Tentar contornar medidas de segurança, controles de acesso ou sistemas de aprovação da plataforma;</li>
            <li>Utilizar os funcionários IA para executar ações não autorizadas, fraudulentas ou prejudiciais;</li>
            <li>Sobrecarregar intencionalmente a infraestrutura da plataforma;</li>
            <li>Publicar conteúdo que viole políticas de plataformas de terceiros (Meta, Google, etc.) integradas ao sistema.</li>
        </ul>
        <div class="legal-warning">
            <p>Violações das regras de uso aceitável podem resultar na suspensão imediata da conta sem direito a reembolso, além de eventual responsabilização civil e criminal.</p>
        </div>
    </div>

    {{-- 5. IA e aprovações --}}
    <div class="legal-section" id="ia-aprovacao">
        <h2><span class="num">5</span> Inteligência Artificial e aprovações obrigatórias</h2>
        <p>A Lymity utiliza modelos de IA para automatizar tarefas de marketing digital. O uso dessas funcionalidades está sujeito às seguintes condições:</p>
        <ul>
            <li><strong>Aprovação obrigatória:</strong> toda publicação externa, alteração de campanha, envio de proposta ou ação sensível executada por funcionários IA requer aprovação explícita de um administrador ou responsável autorizado. Nenhuma ação irreversível é executada sem consentimento humano;</li>
            <li><strong>Responsabilidade pelo conteúdo:</strong> você é responsável por revisar e aprovar todo conteúdo gerado por IA antes de sua publicação. A Lymity não se responsabiliza por conteúdos aprovados e publicados pelo usuário;</li>
            <li><strong>Limites de execução:</strong> cada funcionário IA possui limites de execução e escopo de atuação pré-definidos, que podem ser configurados pela administração da conta;</li>
            <li><strong>Resultados de IA:</strong> os resultados gerados por IA têm caráter de apoio e sugestão. A qualidade e adequação dos resultados dependem da qualidade das informações fornecidas (briefings, contexto de marca, dados de campanhas);</li>
            <li><strong>Integrações de terceiros:</strong> ao conectar plataformas externas (Instagram, Google Ads, etc.), você autoriza a Lymity a interagir com essas plataformas em seu nome, dentro dos limites configurados e aprovados.</li>
        </ul>
    </div>

    {{-- 6. Propriedade intelectual --}}
    <div class="legal-section" id="propriedade">
        <h2><span class="num">6</span> Propriedade intelectual</h2>
        <p><strong>Propriedade da Lymity:</strong> a plataforma, seu código, design, funcionalidades, marca e tecnologia são de propriedade exclusiva da Lymity AI Agency e protegidos por leis de propriedade intelectual. Nenhum destes Termos concede ao usuário direito de propriedade sobre a plataforma.</p>
        <p><strong>Propriedade do cliente:</strong> todo conteúdo, dados de marca, briefings, materiais criativos e informações que você fornece à plataforma permanecem de sua propriedade. A Lymity tem licença limitada para processar esses dados exclusivamente para prestar os serviços contratados.</p>
        <p><strong>Conteúdo gerado por IA:</strong> conteúdos criados pelos funcionários IA com base nos dados e briefings fornecidos pelo cliente são entregues ao cliente mediante aprovação. A Lymity não reivindica propriedade sobre conteúdos aprovados e publicados pelo cliente.</p>
        <p><strong>Feedbacks e sugestões:</strong> quaisquer sugestões, ideias ou feedbacks enviados à Lymity podem ser utilizados para melhoria da plataforma sem obrigação de compensação.</p>
    </div>

    {{-- 7. Pagamento --}}
    <div class="legal-section" id="pagamento">
        <h2><span class="num">7</span> Pagamento e planos</h2>
        <p>Os serviços da Lymity são prestados mediante contrato e plano de assinatura definidos em proposta comercial individualizada. As condições financeiras específicas são estabelecidas no contrato de prestação de serviços firmado entre as partes.</p>
        <p>De forma geral, aplicam-se as seguintes condições:</p>
        <ul>
            <li>Os valores e condições de pagamento são definidos no contrato individual de cada cliente;</li>
            <li>O não pagamento nas datas acordadas pode resultar na suspensão temporária do acesso à plataforma;</li>
            <li>Cobranças indevidas devem ser comunicadas em até 30 dias após o vencimento para análise e estorno, quando aplicável;</li>
            <li>A Lymity reserva-se o direito de ajustar preços mediante comunicação prévia de 30 dias;</li>
            <li>Custos de APIs de terceiros (Google Ads, Meta Ads, provedores de IA) podem ser repassados conforme acordado em contrato.</li>
        </ul>
    </div>

    {{-- 8. Limitação de responsabilidade --}}
    <div class="legal-section" id="responsabilidade">
        <h2><span class="num">8</span> Limitação de responsabilidade</h2>
        <p>Na extensão máxima permitida por lei, a Lymity não se responsabiliza por:</p>
        <ul>
            <li>Perdas indiretas, incidentais, especiais ou consequentes decorrentes do uso ou impossibilidade de uso da plataforma;</li>
            <li>Resultados de campanhas de marketing digital, que dependem de variáveis externas fora do controle da Lymity;</li>
            <li>Conteúdos gerados por IA que foram aprovados e publicados pelo usuário;</li>
            <li>Interrupções, falhas ou indisponibilidades de plataformas de terceiros integradas (Meta, Google, etc.);</li>
            <li>Danos resultantes de acesso não autorizado causado por falha do usuário em proteger suas credenciais;</li>
            <li>Alterações nas políticas de plataformas de terceiros que afetem funcionalidades integradas.</li>
        </ul>
        <p>A responsabilidade total da Lymity por qualquer reclamação não excederá o valor pago pelo cliente nos 3 meses anteriores ao evento que deu origem à reclamação.</p>
        <div class="legal-highlight">
            <p>A plataforma é fornecida "como está". Empreendemos esforços razoáveis para garantir disponibilidade e qualidade, mas não garantimos que o serviço será ininterrupto, livre de erros ou completamente seguro.</p>
        </div>
    </div>

    {{-- 9. Rescisão --}}
    <div class="legal-section" id="rescisao">
        <h2><span class="num">9</span> Rescisão e cancelamento</h2>
        <p><strong>Cancelamento pelo cliente:</strong> o cliente pode solicitar o cancelamento dos serviços a qualquer momento, respeitando os prazos e condições definidos no contrato individual. O cancelamento deve ser solicitado formalmente por e-mail ou pela plataforma.</p>
        <p><strong>Suspensão pela Lymity:</strong> a Lymity pode suspender ou encerrar o acesso à plataforma nas seguintes circunstâncias:</p>
        <ul>
            <li>Violação destes Termos de Serviço;</li>
            <li>Inadimplência no pagamento dos serviços;</li>
            <li>Uso da plataforma para fins ilegais ou prejudiciais a terceiros;</li>
            <li>Inatividade prolongada da conta (superior a 6 meses).</li>
        </ul>
        <p><strong>Efeitos do encerramento:</strong> após o encerramento, o acesso à plataforma será revogado. Os dados do cliente serão mantidos pelo período definido na Política de Privacidade e poderão ser exportados mediante solicitação antes do encerramento definitivo.</p>
    </div>

    {{-- 10. Lei aplicável --}}
    <div class="legal-section" id="lei">
        <h2><span class="num">10</span> Lei aplicável e foro</h2>
        <p>Estes Termos são regidos pelas leis da República Federativa do Brasil. Qualquer disputa decorrente ou relacionada a estes Termos será submetida à tentativa de resolução amigável. Na impossibilidade de acordo, as partes elegem o foro da Comarca de São Paulo/SP como o competente para dirimir quaisquer litígios, com exclusão de qualquer outro, por mais privilegiado que seja.</p>
        <p>Estes Termos constituem o acordo integral entre as partes em relação ao seu objeto e substituem todos os acordos anteriores, sejam eles escritos ou verbais.</p>
        <p>Se qualquer disposição destes Termos for considerada inválida ou inexequível, as demais disposições permanecerão em pleno vigor e efeito.</p>
    </div>

    {{-- Contact box --}}
    <div class="legal-contact-box">
        <h3>Dúvidas sobre os termos?</h3>
        <p>Estamos disponíveis para esclarecer qualquer ponto sobre nossas condições de uso e prestação de serviços.</p>
        <a href="mailto:legal@lymity.com.br">legal@lymity.com.br</a>
        <span style="color:#334155;margin:0 12px;">·</span>
        <a href="{{ route('contato') }}">Formulário de contato</a>
    </div>

</div>

</x-layouts.public>
