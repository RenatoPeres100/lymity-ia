<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ação confirmada — Lymity IA</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
  .card{background:#fff;border-radius:16px;max-width:480px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.1);overflow:hidden;text-align:center;}
  .top{padding:28px 32px;border-bottom:1px solid #f1f5f9;}
  .logo{font-size:20px;font-weight:800;color:#1e1b4b;}
  .logo span{color:#6366f1;}
  .body{padding:40px 32px;}
  .icon{font-size:52px;margin-bottom:16px;}
  h1{font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:8px;}
  .sub{font-size:.875rem;color:#64748b;line-height:1.6;margin-bottom:28px;}
  .approval-title{font-size:.9rem;font-weight:600;color:#334155;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 16px;margin-bottom:28px;}
  .btn{display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:11px 28px;border-radius:8px;font-size:.875rem;font-weight:600;}
</style>
</head>
<body>
<div class="card">
  <div class="top"><div class="logo">Lymity <span>IA</span></div></div>
  <div class="body">
    @php
      $icons = ['approve' => '✅', 'reject' => '❌', 'request_changes' => '✏️'];
      $msgs  = ['approve' => 'Conteúdo aprovado com sucesso!', 'reject' => 'Conteúdo reprovado.', 'request_changes' => 'Ajustes solicitados com sucesso.'];
    @endphp
    <div class="icon">{{ $icons[$action] ?? '✓' }}</div>
    <h1>{{ $msgs[$action] ?? 'Ação executada.' }}</h1>
    <p class="sub">A ação foi registrada no sistema. O conteúdo foi atualizado de acordo.</p>
    <div class="approval-title">{{ $approval->title }}</div>
    <a href="{{ $panelUrl }}" class="btn">Ver no painel →</a>
  </div>
</div>
</body>
</html>
