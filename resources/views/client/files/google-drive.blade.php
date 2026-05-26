<x-layouts.app title="Google Drive">
<div style="max-width:560px;margin:0 auto;padding:60px 16px;text-align:center;">
    <div style="font-size:4rem;margin-bottom:20px;">🗂</div>
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0 0 12px;">Google Drive</h1>
    <p style="font-size:.875rem;color:#64748b;margin:0 0 28px;">Integração com Google Drive para sincronizar seus arquivos.</p>

    <div style="background:{{ ($result['configured'] ?? false) ? '#052e16' : '#1e293b' }};border:1px solid {{ ($result['configured'] ?? false) ? '#166534' : '#334155' }};border-radius:12px;padding:24px;text-align:left;margin-bottom:24px;">
        @if($result['configured'] ?? false)
            <p style="font-size:.875rem;color:#4ade80;font-weight:700;margin:0 0 8px;">✓ Google Drive disponível</p>
            <p style="font-size:.875rem;color:#94a3b8;margin:0;">{{ $result['message'] }}</p>
        @else
            <p style="font-size:.875rem;color:#fb923c;font-weight:700;margin:0 0 8px;">⚠ Google Drive não configurado</p>
            <p style="font-size:.875rem;color:#94a3b8;margin:0;">{{ $result['message'] }}</p>
            <p style="font-size:.78rem;color:#64748b;margin:12px 0 0;">Entre em contato com o administrador para configurar a integração.</p>
        @endif
    </div>

    <a href="{{ route('client.files.index') }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">← Voltar aos arquivos</a>
</div>
</x-layouts.app>
