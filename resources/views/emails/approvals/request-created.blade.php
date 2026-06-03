<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nova aprovação pendente</title>
<style>
  body{margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;}
  .wrapper{max-width:600px;margin:32px auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);}
  .header{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#1e40af 100%);padding:32px 40px 24px;text-align:center;}
  .logo{font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-.02em;}
  .logo span{color:#818cf8;}
  .header-subtitle{font-size:12px;color:#c7d2fe;margin-top:5px;letter-spacing:.04em;text-transform:uppercase;}
  .badge{display:inline-block;background:rgba(255,255,255,.15);color:#e0e7ff;font-size:11px;font-weight:600;padding:4px 12px;border-radius:20px;margin-top:10px;letter-spacing:.06em;text-transform:uppercase;}
  .body{padding:32px 40px;}
  .alert-bar{background:#fef3c7;border-left:4px solid #f59e0b;border-radius:6px;padding:12px 16px;margin-bottom:24px;}
  .alert-bar p{margin:0;font-size:13px;color:#92400e;line-height:1.5;}
  .section-label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;}

  /* Content preview card */
  .content-card{border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:24px;}
  .content-image{width:100%;max-height:280px;object-fit:cover;display:block;}
  .content-image-placeholder{background:linear-gradient(135deg,#1e1b4b,#3730a3);height:120px;display:flex;align-items:center;justify-content:center;}
  .content-image-placeholder span{color:#a5b4fc;font-size:13px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;}
  .content-body{padding:20px 24px;}
  .content-type-tag{display:inline-block;background:#ede9fe;color:#4f46e5;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:.06em;text-transform:uppercase;margin-bottom:10px;}
  .content-title{font-size:17px;font-weight:700;color:#0f172a;margin:0 0 10px;line-height:1.35;}
  .content-excerpt{font-size:13px;color:#475569;line-height:1.65;margin:0;}
  .content-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;}
  .meta-tag{font-size:11px;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;padding:2px 10px;border-radius:12px;}

  /* View content button */
  .btn-view{display:block;text-align:center;text-decoration:none;font-size:13px;font-weight:600;padding:11px 20px;border-radius:8px;margin-bottom:8px;background:#f8fafc;color:#334155;border:1px solid #e2e8f0;}

  /* Action buttons */
  .actions{margin:20px 0;}
  .btn{display:block;text-align:center;text-decoration:none;font-size:14px;font-weight:600;padding:13px 24px;border-radius:8px;margin-bottom:10px;}
  .btn-success{background:#059669;color:#ffffff;}
  .btn-warning{background:#d97706;color:#ffffff;}
  .btn-danger{background:#dc2626;color:#ffffff;}

  .security-note{background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px 16px;margin-top:16px;}
  .security-note p{margin:0;font-size:12px;color:#0c4a6e;line-height:1.6;}
  .footer{background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;}
  .footer p{font-size:12px;color:#94a3b8;margin:0;line-height:1.6;}
  @media(max-width:600px){
    .body,.header,.footer{padding:24px 20px;}
    .content-body{padding:16px;}
  }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <div class="logo">Lymity <span>IA</span></div>
    <div class="header-subtitle">Plataforma de Agência Digital</div>
    <div class="badge">Nova Aprovação Pendente</div>
  </div>

  <div class="body">

    <div class="alert-bar">
      <p>Olá, <strong>{{ $recipient->name }}</strong>. Há um conteúdo aguardando sua aprovação. Revise e tome uma ação para que a publicação siga no prazo.</p>
    </div>

    {{-- ── Content Preview ── --}}
    <div class="section-label">Conteúdo para aprovação</div>
    <div class="content-card">

      @php
        $approvable  = $approval->approvable ?? null;
        $isBlog      = $approvable && str_contains(get_class($approvable), 'BlogPost');
        $isSocial    = $approvable && str_contains(get_class($approvable), 'SocialPost');
        $previewImg  = null;
        $previewText = null;
        $contentUrl  = $panelUrl;

        if ($isBlog) {
            $previewImg  = $approvable->featured_image ?? null;
            $previewText = $approvable->excerpt ?? $approvable->meta_description ?? null;
            try { $contentUrl = route('admin.blog.posts.show', $approvable->id); } catch (\Throwable) {}
        } elseif ($isSocial) {
            $previewImg  = $approvable->public_image_url ?? $approvable->image_url ?? null;
            $previewText = $approvable->main_caption ?? null;
            try { $contentUrl = route('admin.social.posts.show', $approvable->id); } catch (\Throwable) {}
        } else {
            $previewText = $approval->description;
        }
      @endphp

      @if($previewImg)
        <img src="{{ $previewImg }}" alt="Preview do conteúdo" class="content-image">
      @else
        <div class="content-image-placeholder">
          <span>{{ $isSocial ? 'Post Social' : ($isBlog ? 'Post Blog' : $approval->approval_type_label) }}</span>
        </div>
      @endif

      <div class="content-body">
        <div class="content-type-tag">{{ $approval->approval_type_label }}</div>
        <div class="content-title">{{ $approval->title }}</div>

        @if($previewText)
        <p class="content-excerpt">{{ \Illuminate\Support\Str::limit($previewText, 220) }}</p>
        @endif

        <div class="content-meta">
          <span class="meta-tag">{{ $approval->sensitive_level_label }}</span>
          @if($approval->client)
          <span class="meta-tag">{{ $approval->client->name }}</span>
          @endif
          @if($approval->aiEmployee)
          <span class="meta-tag">por {{ $approval->aiEmployee->name }}</span>
          @endif
          <span class="meta-tag">Criado {{ $approval->created_at->format('d/m/Y \à\s H:i') }}</span>
          @if($approval->due_at)
          <span class="meta-tag" style="{{ $approval->due_at->isPast() ? 'color:#dc2626;' : '' }}">
            Prazo: {{ $approval->due_at->format('d/m/Y H:i') }}
          </span>
          @endif
          @if($approvable && isset($approvable->scheduled_at) && $approvable->scheduled_at)
          <span class="meta-tag">Publicar: {{ $approvable->scheduled_at->format('d/m/Y H:i') }}</span>
          @endif
        </div>
      </div>
    </div>

    {{-- Ver conteúdo completo --}}
    <a href="{{ $contentUrl }}" class="btn-view">
      Ver conteúdo completo no painel →
    </a>

    {{-- ── Ações ── --}}
    <div class="section-label" style="margin-top:24px;">Tomar uma ação</div>
    <div class="actions">
      <a href="{{ $actionUrls['approve'] }}" class="btn btn-success">
        ✓ &nbsp; Aprovar este conteúdo
      </a>
      <a href="{{ $actionUrls['request_changes'] }}" class="btn btn-warning">
        ✎ &nbsp; Solicitar ajuste
      </a>
      <a href="{{ $actionUrls['reject'] }}" class="btn btn-danger">
        ✕ &nbsp; Reprovar
      </a>
    </div>

    <div class="security-note">
      <p>
        <strong>Por segurança:</strong> ao clicar em qualquer botão acima você verá uma tela de confirmação antes de qualquer ação ser executada. Os links expiram em 72 horas.
      </p>
    </div>

  </div>

  <div class="footer">
    <p>
      Você recebe este e-mail porque tem permissão de aprovação na Lymity IA.<br>
      Se não reconhece esta solicitação, ignore este e-mail.
    </p>
    <p style="margin-top:8px;">
      <a href="{{ config('app.url') }}" style="color:#4f46e5;text-decoration:none;">{{ config('app.url') }}</a>
    </p>
  </div>

</div>
</body>
</html>
