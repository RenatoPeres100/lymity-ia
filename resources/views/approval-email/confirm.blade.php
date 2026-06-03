<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Confirmar ação — Lymity IA</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
  .card{background:#fff;border-radius:16px;max-width:560px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.1);overflow:hidden;}
  .top{padding:28px 32px;border-bottom:1px solid #f1f5f9;}
  .logo{font-size:20px;font-weight:800;color:#1e1b4b;letter-spacing:-.02em;}
  .logo span{color:#6366f1;}
  .body{padding:32px;}
  .action-badge{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;padding:5px 14px;border-radius:20px;margin-bottom:20px;letter-spacing:.04em;text-transform:uppercase;}
  .badge-approve{background:#dcfce7;color:#166534;}
  .badge-reject{background:#fee2e2;color:#991b1b;}
  .badge-request_changes{background:#fef3c7;color:#92400e;}
  h1{font-size:1.25rem;font-weight:700;color:#0f172a;margin-bottom:8px;}
  .sub{font-size:.875rem;color:#475569;line-height:1.6;margin-bottom:24px;}
  .approval-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:24px;}
  .approval-card .title{font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:8px;}
  .meta{display:flex;flex-wrap:wrap;gap:10px;font-size:.78rem;color:#64748b;}
  .meta span{background:#fff;border:1px solid #e2e8f0;padding:2px 10px;border-radius:12px;}
  .form-group{margin-bottom:16px;}
  .form-group label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em;}
  textarea{width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.875rem;color:#111827;resize:vertical;min-height:90px;font-family:inherit;}
  textarea:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);}
  .actions{display:flex;flex-direction:column;gap:10px;}
  .btn{display:block;text-align:center;padding:13px 20px;border-radius:8px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;}
  .btn-approve{background:#16a34a;color:#fff;}
  .btn-reject{background:#dc2626;color:#fff;}
  .btn-changes{background:#d97706;color:#fff;}
  .btn-cancel{background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;}
  .notice{font-size:.75rem;color:#94a3b8;margin-top:20px;line-height:1.6;text-align:center;}
</style>
</head>
<body>
<div class="card">
  <div class="top">
    <div class="logo">Lymity <span>IA</span></div>
  </div>
  <div class="body">

    @php
      $actionLabels = ['approve' => 'Aprovar', 'reject' => 'Reprovar', 'request_changes' => 'Solicitar Ajuste'];
      $label = $actionLabels[$action] ?? $action;
    @endphp

    <div class="action-badge badge-{{ $action }}">
      {{ $label }}
    </div>

    <h1>Confirmar: {{ $label }}</h1>
    <p class="sub">
      Você está prestes a executar esta ação na solicitação abaixo. Revise antes de confirmar.
    </p>

    <div class="approval-card">
      <div class="title">{{ $approval->title }}</div>
      <div class="meta">
        <span>{{ $approval->approval_type_label }}</span>
        <span>{{ $approval->sensitive_level_label }}</span>
        @if($approval->client)<span>{{ $approval->client->name }}</span>@endif
        <span>Criado {{ $approval->created_at->diffForHumans() }}</span>
      </div>
      @if($approval->description)
      <p style="font-size:.825rem;color:#64748b;margin-top:10px;line-height:1.55;">{{ Str::limit($approval->description, 200) }}</p>
      @endif
    </div>

    <form method="POST" action="{{ request()->url() }}">
      @csrf

      {{-- Pass signed URL params --}}
      @foreach(request()->query() as $k => $v)
        @if($k !== '_token')
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endif
      @endforeach

      @if($action === 'request_changes')
      <div class="form-group">
        <label>Descreva as alterações necessárias <span style="color:#dc2626;">*</span></label>
        <textarea name="notes" required minlength="3" placeholder="Explique o que precisa ser ajustado...">{{ old('notes') }}</textarea>
        @error('notes')<p style="color:#dc2626;font-size:.78rem;margin-top:4px;">{{ $message }}</p>@enderror
      </div>
      @elseif($action === 'reject')
      <div class="form-group">
        <label>Motivo da reprovação (recomendado)</label>
        <textarea name="notes" placeholder="Informe o motivo da reprovação..." maxlength="1000">{{ old('notes') }}</textarea>
      </div>
      @endif

      @if($errors->any())
      <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.825rem;color:#b91c1c;">
        @foreach($errors->all() as $e)• {{ $e }}<br>@endforeach
      </div>
      @endif

      <div class="actions">
        <button type="submit" class="btn btn-{{ $action === 'request_changes' ? 'changes' : $action }}">
          Confirmar: {{ $label }}
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-cancel">Cancelar</a>
      </div>
    </form>

    <p class="notice">
      @if($user)
      Ação será registrada como <strong>{{ $user->name }}</strong>.
      @else
      Identificação: via link assinado de e-mail.
      @endif
    </p>

  </div>
</div>
</body>
</html>
