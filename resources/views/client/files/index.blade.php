<x-layouts.app title="Meus Arquivos">
<div style="max-width:1000px;margin:0 auto;padding:32px 16px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0 0 4px;">Meus Arquivos</h1>
            <p style="font-size:.875rem;color:#64748b;margin:0;">{{ $files->total() }} arquivo(s)</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('client.files.google-drive') }}" style="background:#f1f5f9;color:#475569;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none;border:1px solid #e2e8f0;">Google Drive</a>
            <a href="{{ route('client.files.create') }}" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none;">+ Upload</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:8px;padding:10px 16px;margin-bottom:20px;font-size:.875rem;">{{ session('success') }}</div>
    @endif

    {{-- Folders --}}
    @if($folders->count())
    <div style="margin-bottom:28px;">
        <h2 style="font-size:.875rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin:0 0 12px;">Pastas</h2>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            @foreach($folders as $folder)
            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;min-width:150px;">
                <div style="font-size:1.2rem;margin-bottom:4px;">📁</div>
                <div style="font-size:.8rem;font-weight:600;color:#334155;">{{ $folder->name }}</div>
            </div>
            @endforeach

            <details>
                <summary style="cursor:pointer;background:#f8fafc;border:1px dashed #e2e8f0;border-radius:8px;padding:12px 16px;min-width:150px;color:#64748b;font-size:.8rem;list-style:none;">+ Nova Pasta</summary>
                <form method="POST" action="{{ route('client.folders.store') }}" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                    @csrf
                    <input type="text" name="name" placeholder="Nome da pasta" required style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;color:#334155;font-size:.8rem;flex:1;">
                    <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:7px 14px;border-radius:6px;border:none;cursor:pointer;">Criar</button>
                </form>
            </details>
        </div>
    </div>
    @endif

    {{-- Files grid --}}
    @if($files->count())
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
        @foreach($files as $file)
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="height:100px;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-size:2.5rem;">
                @if($file->isImage() && $file->local_path)
                    <img src="{{ Storage::disk('public')->url($file->local_path) }}" alt="{{ $file->name }}" style="width:100%;height:100%;object-fit:cover;">
                @elseif($file->isPdf()) 📄
                @else 📁
                @endif
            </div>
            <div style="padding:10px 12px;">
                <div style="font-size:.78rem;font-weight:600;color:#334155;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $file->name }}</div>
                <div style="font-size:.7rem;color:#94a3b8;margin-bottom:8px;">{{ $file->formatted_size }}</div>
                <a href="{{ route('client.files.show', $file) }}" style="font-size:.72rem;font-weight:600;color:#4f46e5;text-decoration:none;">Ver detalhes</a>
            </div>
        </div>
        @endforeach
    </div>
    <div>{{ $files->links() }}</div>
    @else
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:48px 24px;text-align:center;">
        <div style="font-size:3rem;margin-bottom:12px;">📂</div>
        <p style="font-size:.875rem;color:#64748b;margin:0 0 16px;">Nenhum arquivo ainda.</p>
        <a href="{{ route('client.files.create') }}" style="background:#4f46e5;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Fazer primeiro upload</a>
    </div>
    @endif

</div>
</x-layouts.app>
