<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Erro — Lymity IA</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
  .card{background:#fff;border-radius:16px;max-width:460px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.1);padding:40px 36px;text-align:center;}
  .logo{font-size:20px;font-weight:800;color:#1e1b4b;margin-bottom:28px;}
  .logo span{color:#6366f1;}
  .icon{font-size:52px;margin-bottom:16px;}
  h1{font-size:1.2rem;font-weight:700;color:#0f172a;margin-bottom:10px;}
  .sub{font-size:.875rem;color:#64748b;line-height:1.6;margin-bottom:28px;}
  .btn{display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:11px 28px;border-radius:8px;font-size:.875rem;font-weight:600;}
</style>
</head>
<body>
<div class="card">
  <div class="logo">Lymity <span>IA</span></div>
  <div class="icon">⚠️</div>
  <h1>Não foi possível executar a ação</h1>
  <p class="sub">{{ $message ?? 'Ocorreu um erro inesperado. Tente novamente pelo painel.' }}</p>
  <a href="{{ $panelUrl ?? config('app.url') }}" class="btn">Abrir no painel</a>
</div>
</body>
</html>
