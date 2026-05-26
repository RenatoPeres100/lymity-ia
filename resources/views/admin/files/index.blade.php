<x-layouts.app title="Arquivos">
<div style="max-width:1200px;margin:0 auto;padding:32px 16px;">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0 0 4px;">Arquivos</h1>
            <p style="font-size:.875rem;color:#64748b;margin:0;">Todos os arquivos da agência e clientes</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('admin.files.google-drive') }}" style="background:#f1f5f9;color:#475569;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none;display:flex;align-items:center;gap:6px;border:1px solid #e2e8f0;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                Google Drive
            </a>
            <a href="{{ route('admin.files.create') }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none;">+ Novo Upload</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:8px;padding:10px 16px;margin-bottom:20px;font-size:.875rem;">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:24px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;">Cliente</label>
            <select name="client_id" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;color:#334155;font-size:.8rem;min-width:150px;">
                <option value="">Todos</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;">Origem</label>
            <select name="source" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;color:#334155;font-size:.8rem;min-width:130px;">
                <option value="">Todas</option>
                @foreach(['upload'=>'Upload','google_drive'=>'Google Drive','generated'=>'Gerado por IA','imported'=>'Importado'] as $v=>$l)
                    <option value="{{ $v }}" {{ request('source') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;">Tipo</label>
            <select name="file_type" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;color:#334155;font-size:.8rem;min-width:120px;">
                <option value="">Todos</option>
                @foreach(['image'=>'Imagem','pdf'=>'PDF','document'=>'Documento','video'=>'Vídeo','audio'=>'Áudio','other'=>'Outro'] as $v=>$l)
                    <option value="{{ $v }}" {{ request('file_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;">Busca</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome do arquivo..." style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;color:#334155;font-size:.8rem;min-width:180px;">
        </div>
        <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:6px;border:none;cursor:pointer;">Filtrar</button>
        <a href="{{ route('admin.files.index') }}" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:6px;text-decoration:none;">Limpar</a>
    </form>

    {{-- Table --}}
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    @foreach(['Nome','Tipo','Tamanho','Cliente','Origem','Enviado por','Data',''] as $h)
                        <th style="padding:10px 16px;font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;text-align:left;">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;font-size:.8rem;color:#334155;font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <a href="{{ route('admin.files.show', $file) }}" style="color:#4f46e5;text-decoration:none;">{{ $file->name }}</a>
                    </td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#64748b;">{{ $file->file_type_label }}</td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#64748b;">{{ $file->formatted_size }}</td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#64748b;">{{ $file->client?->name ?? '—' }}</td>
                    <td style="padding:12px 16px;">
                        <span style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:.68rem;font-weight:600;padding:3px 8px;border-radius:6px;">{{ $file->source_label }}</span>
                    </td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#64748b;">{{ $file->uploader?->name ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#94a3b8;">{{ $file->created_at->format('d/m/y') }}</td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex;gap:8px;">
                            <a href="{{ route('admin.files.show', $file) }}" style="color:#4a6cf7;font-size:.72rem;font-weight:600;text-decoration:none;">Ver</a>
                            <form method="POST" action="{{ route('admin.files.destroy', $file) }}" onsubmit="return confirm('Remover arquivo?')">
                                @csrf @method('DELETE')
                                <button style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:.72rem;font-weight:600;padding:0;">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="padding:32px 16px;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhum arquivo encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $files->links() }}</div>
</div>
</x-layouts.app>
