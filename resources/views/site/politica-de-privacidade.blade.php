<x-layouts.public title="Política de Privacidade" metaDescription="Saiba como a Lymity AI Agency coleta, usa e protege seus dados pessoais.">

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
    <h1>Política de Privacidade</h1>
    <p>Transparência total sobre como tratamos suas informações. Sua privacidade é uma prioridade para nós.</p>
    <p class="legal-updated">Última atualização: Maio de 2025</p>
</section>

{{-- Body --}}
<div class="legal-body">

    {{-- Table of Contents --}}
    <div class="legal-toc">
        <div class="legal-toc-title">Índice</div>
        <ol>
            <li><a href="#coleta">Dados que coletamos</a></li>
            <li><a href="#uso">Como usamos seus dados</a></li>
            <li><a href="#ia">Inteligência Artificial e automação</a></li>
            <li><a href="#compartilhamento">Compartilhamento de dados</a></li>
            <li><a href="#armazenamento">Armazenamento e segurança</a></li>
            <li><a href="#cookies">Cookies e rastreamento</a></li>
            <li><a href="#direitos">Seus direitos (LGPD)</a></li>
            <li><a href="#retencao">Retenção de dados</a></li>
            <li><a href="#menores">Menores de idade</a></li>
            <li><a href="#alteracoes">Alterações desta política</a></li>
        </ol>
    </div>

    {{-- Intro --}}
    <p style="font-size:0.975rem;color:#475569;line-height:1.8;margin-bottom:48px;">
        A <strong>Lymity AI Agency</strong> ("Lymity", "nós", "nosso") é uma plataforma de agência digital automatizada por inteligência artificial. Esta Política de Privacidade descreve como coletamos, usamos, armazenamos e protegemos suas informações pessoais ao utilizar nossa plataforma, site institucional e serviços relacionados. Ao acessar ou usar nossos serviços, você concorda com as práticas descritas neste documento, em conformidade com a <strong>Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018)</strong>.
    </p>

    {{-- 1. Dados que coletamos --}}
    <div class="legal-section" id="coleta">
        <h2><span class="num">1</span> Dados que coletamos</h2>
        <p>Coletamos diferentes categorias de dados dependendo de como você interage com nossa plataforma:</p>
        <p><strong>Dados fornecidos diretamente por você:</strong></p>
        <ul>
            <li>Nome completo, e-mail, telefone e dados de contato ao preencher formulários ou criar conta;</li>
            <li>Informações da empresa ou marca (nome, segmento, site, redes sociais) para personalização dos serviços de IA;</li>
            <li>Conteúdo enviado para aprovação, publicação ou análise (textos, imagens, briefings, campanhas);</li>
            <li>Credenciais de acesso (protegidas por hash — nunca armazenamos senhas em texto claro);</li>
            <li>Mensagens e comunicações enviadas pelo formulário de contato ou pela plataforma.</li>
        </ul>
        <p><strong>Dados coletados automaticamente:</strong></p>
        <ul>
            <li>Endereço IP, tipo de dispositivo, sistema operacional e navegador;</li>
            <li>Páginas visitadas, tempo de sessão e ações realizadas na plataforma (logs de uso);</li>
            <li>Dados de desempenho de campanhas integradas (Google Ads, Meta Ads) quando autorizados;</li>
            <li>Logs gerados por funcionários IA durante execução de tarefas automatizadas.</li>
        </ul>
        <p><strong>Dados de terceiros:</strong></p>
        <ul>
            <li>Informações de redes sociais conectadas (Instagram, Facebook) mediante autorização OAuth explícita;</li>
            <li>Dados de Google Drive ou outros serviços de armazenamento quando integrados voluntariamente.</li>
        </ul>
    </div>

    {{-- 2. Como usamos seus dados --}}
    <div class="legal-section" id="uso">
        <h2><span class="num">2</span> Como usamos seus dados</h2>
        <p>Utilizamos seus dados exclusivamente para as finalidades a seguir, com base legal adequada conforme a LGPD:</p>
        <ul>
            <li><strong>Prestação dos serviços:</strong> operar a plataforma, executar tarefas de IA, gerar conteúdo, gerenciar aprovações e automatizar fluxos de trabalho;</li>
            <li><strong>Personalização:</strong> adaptar os funcionários IA, rotinas e resultados ao perfil, tom de voz e objetivos da sua marca;</li>
            <li><strong>Comunicação:</strong> enviar notificações sobre aprovações, entregas, alertas do sistema e atualizações relevantes;</li>
            <li><strong>Melhoria contínua:</strong> analisar padrões de uso para aprimorar a plataforma e os algoritmos de IA;</li>
            <li><strong>Conformidade legal:</strong> cumprir obrigações legais, fiscais e regulatórias aplicáveis;</li>
            <li><strong>Segurança:</strong> detectar, investigar e prevenir acessos não autorizados, fraudes e abusos.</li>
        </ul>
        <div class="legal-highlight">
            <p>Nunca vendemos, alugamos ou comercializamos seus dados pessoais para terceiros. Seus dados não são usados para fins publicitários fora do contexto dos serviços contratados.</p>
        </div>
    </div>

    {{-- 3. IA e automação --}}
    <div class="legal-section" id="ia">
        <h2><span class="num">3</span> Inteligência Artificial e automação</h2>
        <p>Nossa plataforma utiliza modelos de IA (incluindo Claude da Anthropic, Gemini do Google e outros provedores) para gerar conteúdo, analisar dados e executar tarefas automatizadas. Ao usar nossos serviços, você entende que:</p>
        <ul>
            <li>Conteúdos e briefings que você fornece podem ser processados por modelos de IA para gerar resultados;</li>
            <li>Toda ação sensível (publicações externas, alterações em campanhas, envio de propostas) requer aprovação explícita de um administrador ou responsável autorizado antes de ser executada;</li>
            <li>Logs detalhados de todas as ações dos funcionários IA são mantidos e acessíveis aos administradores da conta;</li>
            <li>Resultados gerados por IA são sempre apresentados para revisão humana antes de qualquer publicação ou envio;</li>
            <li>Os provedores de IA utilizados possuem suas próprias políticas de privacidade e podem processar dados conforme seus termos — utilizamos apenas provedores com padrões adequados de proteção de dados.</li>
        </ul>
    </div>

    {{-- 4. Compartilhamento --}}
    <div class="legal-section" id="compartilhamento">
        <h2><span class="num">4</span> Compartilhamento de dados</h2>
        <p>Seus dados podem ser compartilhados com terceiros apenas nas seguintes circunstâncias:</p>
        <ul>
            <li><strong>Provedores de infraestrutura:</strong> servidores VPS, serviços de hospedagem e bancos de dados necessários para operar a plataforma;</li>
            <li><strong>APIs e integrações autorizadas:</strong> Google Ads, Meta Ads, Instagram, Google Drive e outras integrações ativadas por você;</li>
            <li><strong>Provedores de IA:</strong> modelos de linguagem utilizados para geração de conteúdo e automação (Anthropic, Google, etc.);</li>
            <li><strong>Obrigações legais:</strong> quando exigido por lei, decisão judicial ou autoridade competente;</li>
            <li><strong>Proteção de direitos:</strong> para investigar ou prevenir fraudes, violações de segurança ou danos a terceiros.</li>
        </ul>
        <p>Todos os fornecedores terceirizados são contratualmente obrigados a proteger seus dados e usá-los apenas para as finalidades especificadas.</p>
    </div>

    {{-- 5. Armazenamento e segurança --}}
    <div class="legal-section" id="armazenamento">
        <h2><span class="num">5</span> Armazenamento e segurança</h2>
        <p>Adotamos medidas técnicas e organizacionais para proteger seus dados:</p>
        <ul>
            <li>Senhas armazenadas com hashing seguro (bcrypt);</li>
            <li>Comunicações protegidas por HTTPS/TLS;</li>
            <li>Acesso à plataforma restrito por autenticação e controle de permissões por papel (admin, cliente, colaborador);</li>
            <li>Logs de segurança e auditoria para rastreamento de acessos;</li>
            <li>Infraestrutura isolada em VPS dedicada com firewall e monitoramento contínuo;</li>
            <li>Backups regulares dos dados armazenados.</li>
        </ul>
        <p>Nenhum sistema é 100% seguro. Em caso de incidente de segurança que afete seus dados, notificaremos os titulares e a Autoridade Nacional de Proteção de Dados (ANPD) conforme exigido pela LGPD.</p>
    </div>

    {{-- 6. Cookies --}}
    <div class="legal-section" id="cookies">
        <h2><span class="num">6</span> Cookies e rastreamento</h2>
        <p>Utilizamos cookies e tecnologias similares para:</p>
        <ul>
            <li><strong>Cookies essenciais:</strong> manter sua sessão autenticada e garantir o funcionamento básico da plataforma;</li>
            <li><strong>Cookies de preferência:</strong> lembrar configurações e preferências de uso;</li>
            <li><strong>Cookies analíticos:</strong> entender como os usuários interagem com o site e a plataforma para melhorias.</li>
        </ul>
        <p>Você pode configurar seu navegador para bloquear ou excluir cookies. A desativação de cookies essenciais pode impedir o funcionamento correto da plataforma autenticada.</p>
    </div>

    {{-- 7. Seus direitos (LGPD) --}}
    <div class="legal-section" id="direitos">
        <h2><span class="num">7</span> Seus direitos (LGPD)</h2>
        <p>Conforme a Lei Geral de Proteção de Dados, você tem os seguintes direitos em relação aos seus dados pessoais:</p>
        <ul>
            <li><strong>Confirmação e acesso:</strong> saber se tratamos seus dados e obter uma cópia deles;</li>
            <li><strong>Correção:</strong> solicitar a atualização de dados incompletos, inexatos ou desatualizados;</li>
            <li><strong>Anonimização, bloqueio ou eliminação:</strong> de dados desnecessários ou tratados em desconformidade;</li>
            <li><strong>Portabilidade:</strong> receber seus dados em formato estruturado para transferência a outro fornecedor;</li>
            <li><strong>Revogação de consentimento:</strong> retirar o consentimento para tratamento de dados a qualquer momento;</li>
            <li><strong>Oposição:</strong> opor-se a tratamento realizado sem consentimento que viole a LGPD;</li>
            <li><strong>Eliminação:</strong> solicitar a exclusão dos seus dados pessoais, salvo quando necessário para obrigações legais.</li>
        </ul>
        <p>Para exercer qualquer um desses direitos, entre em contato pelo e-mail: <a href="mailto:privacidade@lymity.com.br" style="color:#4a6cf7;font-weight:600;">privacidade@lymity.com.br</a></p>
    </div>

    {{-- 8. Retenção --}}
    <div class="legal-section" id="retencao">
        <h2><span class="num">8</span> Retenção de dados</h2>
        <p>Mantemos seus dados pelo tempo necessário para cumprir as finalidades descritas nesta política e as obrigações legais aplicáveis:</p>
        <ul>
            <li><strong>Dados de conta ativa:</strong> durante toda a vigência do contrato de serviço;</li>
            <li><strong>Logs de auditoria e segurança:</strong> por até 2 anos após o encerramento da conta;</li>
            <li><strong>Dados fiscais e contratuais:</strong> pelo período exigido pela legislação fiscal brasileira (mínimo 5 anos);</li>
            <li><strong>Dados de marketing e leads:</strong> até 2 anos após o último contato ou mediante solicitação de exclusão.</li>
        </ul>
        <p>Após os prazos de retenção, os dados são excluídos com segurança ou anonimizados de forma irreversível.</p>
    </div>

    {{-- 9. Menores --}}
    <div class="legal-section" id="menores">
        <h2><span class="num">9</span> Menores de idade</h2>
        <p>Nossos serviços são destinados exclusivamente a empresas e profissionais maiores de 18 anos. Não coletamos intencionalmente dados de menores de idade. Se tomarmos conhecimento de que coletamos dados de um menor sem consentimento adequado, excluiremos essas informações imediatamente.</p>
    </div>

    {{-- 10. Alterações --}}
    <div class="legal-section" id="alteracoes">
        <h2><span class="num">10</span> Alterações desta política</h2>
        <p>Podemos atualizar esta Política de Privacidade periodicamente para refletir mudanças em nossas práticas, tecnologias utilizadas ou requisitos legais. Em caso de alterações relevantes, notificaremos você por e-mail ou por aviso destacado na plataforma com pelo menos 15 dias de antecedência.</p>
        <p>A versão mais recente desta política estará sempre disponível em <strong>/politica-de-privacidade</strong> com a data da última atualização indicada.</p>
    </div>

    {{-- Contact box --}}
    <div class="legal-contact-box">
        <h3>Dúvidas sobre privacidade?</h3>
        <p>Nossa equipe está pronta para responder qualquer questão relacionada ao tratamento dos seus dados pessoais.</p>
        <a href="mailto:privacidade@lymity.com.br">privacidade@lymity.com.br</a>
        <span style="color:#334155;margin:0 12px;">·</span>
        <a href="{{ route('contato') }}">Formulário de contato</a>
    </div>

</div>

</x-layouts.public>
