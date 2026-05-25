<x-layouts.app title="Arquivos — {{ $client->name }}">
<div style="max-width:1100px;margin:0 auto;padding:32px 16px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <a href="{{ route('admin.clients') }}" style="color:#64748b;font-size:.8rem;text-decoration:none;">← Clientes</a>
            <h1 style="font-size:1.3rem;font-weight:700;color:#f1f5f9;margin:8px 0 4px;">{{ $client->name }} — Arquivos</h1>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('admin.clients.files.google-drive', $client) }}" style="background:#1e293b;color:#94a3b8;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none;">Google Drive</a>
            <a href="{{ route('admin.clients.files.create', $client) }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none;">+ Upload</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#052e16;color:#4ade80;border-radius:8px;padding:10px 16px;margin-bottom:20px;font-size:.875rem;">{{ session('success') }}</div>
    @endif

    {{-- New Folder --}}
    <details style="margin-bottom:20px;">
        <summary style="cursor:pointer;font-size:.875rem;color:#94a3b8;font-weight:600;padding:10px 16px;background:#0f172a;border:1px solid #1e293b;border-radius:8px;">+ Nova Pasta</summary>
        <form method="POST" action="{{ route('admin.clients.folders.store', $client) }}" style="background:#0f172a;border:1px solid #1e293b;border-top:none;border-radius:0 0 8px 8px;padding:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            @csrf
            <input type="text" name="name" placeholder="Nome da pasta" required style="background:#020617;border:1px solid #1e293b;border-radius:6px;padding:8px 12px;color:#e2e8f0;font-size:.8rem;flex:1;min-width:200px;">
            <input type="text" name="description" placeholder="Descrição (opcional)" style="background:#020617;border:1px solid #1e293b;border-radius:6px;padding:8px 12px;color:#e2e8f0;font-size:.8rem;flex:1;min-width:200px;">
            <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:6px;border:none;cursor:pointer;">Criar</button>
        </form>
    </details>

    {{-- Folders --}}
    @if($folders->count())
    <div style="margin-bottom:24px;">
        <h2 style="font-size:.875rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin:0 0 12px;">Pastas</h2>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            @foreach($folders as $folder)
            <div style="background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px 16px;min-width:160px;">
                <div style="font-size:1.2rem;margin-bottom:4px;">📁</div>
                <div style="font-size:.8rem;font-weight:600;color:#e2e8f0;">{{ $folder->name }}</div>
                @if($folder->description)<div style="font-size:.72rem;color:#64748b;">{{ $folder->description }}</div>@endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Files --}}
    <h2 style="font-size:.875rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin:0 0 12px;">Arquivos ({{ $files->total() }})</h2>

    <div style="background:#0f172a;border:1px solid #1e293b;border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#020617;border-bottom:1px solid #1e293b;">
                    @foreach(['Nome','Tipo','Tamanho','Data',''] as $h)
                        <th style="padding:10px 16px;font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;text-align:left;">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                <tr style="border-bottom:1px solid #0f172a;">
                    <td style="padding:12px 16px;font-size:.8rem;color:#e2e8f0;">
                        <a href="{{ route('admin.clients.files.show', [$client, $file]) }}" style="color:#e2e8f0;text-decoration:none;">{{ $file->name }}</a>
                    </td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#94a3b8;">{{ $file->file_type_label }}</td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#94a3b8;">{{ $file->formatted_size }}</td>
                    <td style="padding:12px 16px;font-size:.78rem;color:#64748b;">{{ $file->created_at->format('d/m/y') }}</td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex;gap:8px;">
                            <a href="{{ route('admin.clients.files.show', [$client, $file]) }}" style="color:#4a6cf7;font-size:.72rem;font-weight:600;text-decoration:none;">Ver</a>
                            <form method="POST" action="{{ route('admin.clients.files.destroy', [$client, $file]) }}" onsubmit="return confirm('Remover?')">
                                @csrf @method('DELETE')
                                <button style="background:none;border:none;cursor:pointer;color:#f87171;font-size:.72rem;font-weight:600;padding:0;">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:32px 16px;text-align:center;font-size:.875rem;color:#475569;">Nenhum arquivo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $files->links() }}</div>
</div>
</x-layouts.app>
