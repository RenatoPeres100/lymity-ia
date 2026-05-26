<x-layouts.app title="Google Drive">
<div style="max-width:600px;margin:0 auto;padding:60px 16px;text-align:center;">

    <div style="font-size:4rem;margin-bottom:20px;">🗂</div>
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0 0 12px;">Google Drive</h1>

    @if(isset($client))
        <p style="font-size:.875rem;color:#64748b;margin:0 0 24px;">Cliente: <strong style="color:#334155;">{{ $client->name }}</strong></p>
    @endif

    <div style="background:{{ ($result['configured'] ?? false) ? '#052e16' : '#1e293b' }};border:1px solid {{ ($result['configured'] ?? false) ? '#166534' : '#334155' }};border-radius:12px;padding:28px;text-align:left;">
        @if($result['configured'] ?? false)
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <span style="color:#4ade80;font-size:1.2rem;">✓</span>
                <span style="font-size:.875rem;font-weight:700;color:#4ade80;">Credenciais detectadas</span>
            </div>
            <p style="font-size:.875rem;color:#94a3b8;margin:0;">{{ $result['message'] }}</p>
        @else
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <span style="color:#fb923c;font-size:1.2rem;">⚠</span>
                <span style="font-size:.875rem;font-weight:700;color:#fb923c;">Google Drive não configurado</span>
            </div>
            <p style="font-size:.875rem;color:#94a3b8;margin:0 0 16px;">{{ $result['message'] }}</p>
            <div style="background:#020617;border-radius:8px;padding:14px;">
                <p style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;margin:0 0 8px;">Adicionar ao .env:</p>
                @foreach(['GOOGLE_DRIVE_ENABLED=true','GOOGLE_DRIVE_CLIENT_ID=seu-client-id','GOOGLE_DRIVE_CLIENT_SECRET=seu-client-secret','GOOGLE_DRIVE_REDIRECT_URI=https://seudominio.com.br/oauth/google/callback'] as $line)
                    <code style="display:block;font-family:monospace;font-size:.72rem;color:#a5f3fc;margin-bottom:3px;">{{ $line }}</code>
                @endforeach
            </div>
        @endif
    </div>

    <div style="margin-top:24px;">
        <a href="{{ url()->previous() }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Voltar</a>
    </div>
</div>
</x-layouts.app>
