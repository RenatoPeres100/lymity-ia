<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Link de teste válido — Lymity IA</title>
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
  .badge{display:inline-block;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:.8rem;font-weight:600;padding:6px 16px;border-radius:20px;margin-bottom:24px;}
  .note{font-size:.8rem;color:#94a3b8;line-height:1.5;}
  .btn{display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:11px 28px;border-radius:8px;font-size:.875rem;font-weight:600;}
</style>
</head>
<body>
<div class="card">
  <div class="top"><div class="logo">Lymity <span>IA</span></div></div>
  <div class="body">
    <div class="icon">✅</div>
    <h1>Link de aprovação funcionando!</h1>
    <div class="badge">Assinatura válida · Link de teste</div>
    <p class="sub">Este era um e-mail de teste. A assinatura foi verificada com sucesso — os links de aprovação reais funcionarão da mesma forma.</p>
    <p class="note">Ação clicada: <strong>{{ $action }}</strong></p>
    <br>
    <a href="{{ config('app.url') }}/admin/approvals" class="btn">Ir para Aprovações →</a>
  </div>
</div>
</body>
</html>
