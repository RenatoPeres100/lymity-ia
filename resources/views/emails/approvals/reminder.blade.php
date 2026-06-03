<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Lembrete de aprovação pendente</title>
<style>
  body{margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;}
  .wrapper{max-width:600px;margin:32px auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);}
  .header{background:linear-gradient(135deg,#78350f 0%,#b45309 50%,#d97706 100%);padding:36px 40px 28px;text-align:center;}
  .logo{font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-.02em;}
  .logo span{color:#fde68a;}
  .header-subtitle{font-size:13px;color:#fef3c7;margin-top:6px;letter-spacing:.04em;text-transform:uppercase;}
  .badge{display:inline-block;background:rgba(255,255,255,.2);color:#fffbeb;font-size:11px;font-weight:600;padding:4px 12px;border-radius:20px;margin-top:12px;letter-spacing:.06em;text-transform:uppercase;}
  .body{padding:36px 40px;}
  .alert-bar{background:#fef3c7;border-left:4px solid #f59e0b;border-radius:6px;padding:14px 16px;margin-bottom:28px;}
  .alert-bar p{margin:0;font-size:14px;color:#92400e;line-height:1.5;}
  .section-title{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;}
  .card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px 24px;margin-bottom:20px;}
  .approval-title{font-size:18px;font-weight:700;color:#0f172a;margin:0 0 8px;line-height:1.3;}
  .meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;}
  .meta-item label{display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;}
  .meta-item span{font-size:13px;color:#334155;font-weight:500;}
  .reminder-count{font-size:12px;color:#64748b;margin-top:8px;}
  .actions{margin:28px 0;}
  .btn{display:block;text-align:center;text-decoration:none;font-size:14px;font-weight:600;padding:13px 24px;border-radius:8px;margin-bottom:10px;}
  .btn-primary{background:#d97706;color:#ffffff;}
  .btn-success{background:#059669;color:#ffffff;}
  .btn-warning{background:#7c3aed;color:#ffffff;}
  .btn-danger{background:#dc2626;color:#ffffff;}
  .btn-outline{background:#ffffff;color:#334155;border:1px solid #e2e8f0;}
  .security-note{background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:14px 16px;margin-top:20px;}
  .security-note p{margin:0;font-size:12px;color:#0c4a6e;line-height:1.6;}
  .security-note strong{color:#075985;}
  .footer{background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;}
  .footer p{font-size:12px;color:#94a3b8;margin:0;line-height:1.6;}
  @media(max-width:600px){
    .body,.header,.footer{padding:24px 20px;}
    .meta-grid{grid-template-columns:1fr;}
  }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <div class="logo">Lymity <span>IA</span></div>
    <div class="header-subtitle">Plataforma de Agência Digital</div>
    <div class="badge">Lembrete — Aprovação Pendente</div>
  </div>

  <div class="body">

    <div class="alert-bar">
      <p>
        <strong>Ação necessária:</strong> existe uma solicitação aguardando sua aprovação.
        @if($approval->due_at)
        O prazo é <strong>{{ $approval->due_at->format('d/m/Y \à\s H:i') }}</strong>.
        @endif
        Revise agora para garantir que a publicação ocorra no prazo.
      </p>
    </div>

    <div class="section-title">Detalhes da Solicitação</div>
    <div class="card">
      <div class="approval-title">{{ $approval->title }}</div>
      @if($approval->description)
      <p style="font-size:14px;color:#475569;line-height:1.65;margin:8px 0 0;">{{ $approval->description }}</p>
      @endif

      <div class="meta-grid">
        <div class="meta-item">
          <label>Tipo</label>
          <span>{{ $approval->approval_type_label }}</span>
        </div>
        @if($approval->client)
        <div class="meta-item">
          <label>Cliente</label>
          <span>{{ $approval->client->name }}</span>
        </div>
        @endif
        <div class="meta-item">
          <label>Aguardando desde</label>
          <span>{{ $approval->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($approval->due_at)
        <div class="meta-item">
          <label>Prazo</label>
          <span style="color:{{ $approval->due_at->isPast() ? '#dc2626' : '#d97706' }};font-weight:700;">
            {{ $approval->due_at->format('d/m/Y H:i') }}
            @if($approval->due_at->isPast())
            (vencido)
            @else
            (em {{ $approval->due_at->diffForHumans() }})
            @endif
          </span>
        </div>
        @endif
        @if($approval->approvable && isset($approval->approvable->scheduled_at) && $approval->approvable->scheduled_at)
        <div class="meta-item">
          <label>Publicação agendada</label>
          <span>{{ $approval->approvable->scheduled_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
      </div>

      @if(($approval->reminder_count ?? 0) > 0)
      <div class="reminder-count">Este é o lembrete nº {{ ($approval->reminder_count ?? 0) + 1 }} enviado.</div>
      @endif
    </div>

    <div class="section-title">Ações</div>
    <div class="actions">
      <a href="{{ $panelUrl }}" class="btn btn-outline">
        Abrir aprovação no painel →
      </a>
      <a href="{{ $actionUrls['approve'] }}" class="btn btn-success">
        Aprovar agora
      </a>
      <a href="{{ $actionUrls['request_changes'] }}" class="btn btn-warning">
        Solicitar ajuste
      </a>
      <a href="{{ $actionUrls['reject'] }}" class="btn btn-danger">
        Reprovar
      </a>
    </div>

    <div class="security-note">
      <p>
        <strong>Por segurança:</strong> os botões de ação acima abrem uma tela de confirmação antes de executar qualquer ação. Nenhuma alteração é feita apenas ao clicar no link.
      </p>
    </div>

  </div>

  <div class="footer">
    <p>
      Olá, <strong>{{ $recipient->name }}</strong>. Você recebe este lembrete porque há um conteúdo pendente de aprovação na Lymity IA.<br>
      Se você não reconhece esta solicitação, ignore este e-mail ou acesse o painel da Lymity IA.
    </p>
    <p style="margin-top:10px;">
      <a href="{{ config('app.url') }}" style="color:#4f46e5;text-decoration:none;">{{ config('app.url') }}</a>
    </p>
  </div>

</div>
</body>
</html>
