<x-layouts.app title="{{ $file->name }}">
<div style="max-width:800px;margin:0 auto;padding:32px 16px;">

    <div style="margin-bottom:24px;">
        <a href="{{ route('admin.files.index') }}" style="color:#64748b;font-size:.8rem;text-decoration:none;">← Arquivos</a>
        <h1 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin:8px 0 4px;">{{ $file->name }}</h1>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:8px;padding:10px 16px;margin-bottom:20px;font-size:.875rem;">{{ session('success') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">

        {{-- Preview --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;grid-column:1/-1;">
            @if($url)
                @if($file->isImage())
                    <img src="{{ $url }}" alt="{{ $file->name }}" style="max-width:100%;max-height:400px;border-radius:8px;display:block;margin:0 auto;">
                @elseif($file->isPdf())
                    <div style="text-align:center;padding:20px;">
                        <div style="font-size:3rem;margin-bottom:12px;">📄</div>
                        <a href="{{ $url }}" target="_blank" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Abrir PDF</a>
                    </div>
                @else
                    <div style="text-align:center;padding:20px;">
                        <div style="font-size:3rem;margin-bottom:12px;">📁</div>
                        <a href="{{ $url }}" target="_blank" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Baixar / Abrir</a>
                    </div>
                @endif
            @else
                <div style="text-align:center;padding:20px;color:#94a3b8;">Arquivo sem URL disponível.</div>
            @endif
        </div>

        {{-- Details --}}
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
            <h3 style="font-size:.875rem;font-weight:700;color:#1e293b;margin:0 0 16px;">Dados Técnicos</h3>
            @foreach([
                ['Nome', $file->name],
                ['Tipo', $file->file_type_label],
                ['MIME', $file->mime_type ?? '—'],
                ['Tamanho', $file->formatted_size],
                ['Origem', $file->source_label],
                ['Caminho local', $file->local_path ?? '—'],
            ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:.8rem;">
                <span style="color:#64748b;">{{ $label }}</span>
                <span style="color:#334155;text-align:right;max-width:60%;word-break:break-all;">{{ $value }}</span>
            </div>
            @endforeach
        </div>

        {{-- Meta --}}
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
            <h3 style="font-size:.875rem;font-weight:700;color:#1e293b;margin:0 0 16px;">Metadados</h3>
            @foreach([
                ['Cliente', $file->client?->name ?? '—'],
                ['Enviado por', $file->uploader?->name ?? '—'],
                ['Data de envio', $file->created_at->format('d/m/Y H:i')],
                ['Relacionado a', $file->related_type ? "{$file->related_type}#{$file->related_id}" : '—'],
                ['Notas', $file->notes ?? '—'],
            ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:.8rem;">
                <span style="color:#64748b;">{{ $label }}</span>
                <span style="color:#334155;text-align:right;max-width:60%;word-break:break-all;">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Relate form --}}
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <h3 style="font-size:.875rem;font-weight:700;color:#1e293b;margin:0 0 14px;">Vincular a módulo</h3>
        <form method="POST" action="{{ route('admin.files.relate', $file) }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            @csrf
            <div>
                <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;">Tipo</label>
                <input type="text" name="related_type" value="{{ $file->related_type }}" placeholder="App\Models\SocialPost" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;color:#334155;font-size:.8rem;width:240px;">
            </div>
            <div>
                <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;">ID</label>
                <input type="number" name="related_id" value="{{ $file->related_id }}" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;color:#334155;font-size:.8rem;width:100px;">
            </div>
            <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:6px;border:none;cursor:pointer;">Vincular</button>
        </form>
        <p style="font-size:.72rem;color:#94a3b8;margin:8px 0 0;">Módulos suportados: SocialPost, AdCampaign, BlogPost, Proposal, Budget, ClientWebsitePage</p>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:10px;">
        @if($url)
        <a href="{{ $url }}" target="_blank" style="background:#eff6ff;color:#4f46e5;border:1px solid #e0e7ff;font-size:.875rem;font-weight:600;padding:10px 20px;border-radius:8px;text-decoration:none;">Abrir arquivo</a>
        @endif
        <form method="POST" action="{{ route('admin.files.destroy', $file) }}" onsubmit="return confirm('Remover arquivo permanentemente?')">
            @csrf @method('DELETE')
            <button style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;font-size:.875rem;font-weight:600;padding:10px 20px;border-radius:8px;cursor:pointer;">Remover</button>
        </form>
    </div>

</div>
</x-layouts.app>
