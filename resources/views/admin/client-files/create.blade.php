<x-layouts.app title="Upload — {{ $client->name }}">
<div style="max-width:600px;margin:0 auto;padding:32px 16px;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('admin.clients.files.index', $client) }}" style="color:#64748b;font-size:.8rem;text-decoration:none;">← Arquivos de {{ $client->name }}</a>
        <h1 style="font-size:1.3rem;font-weight:700;color:#f1f5f9;margin:8px 0 4px;">Upload para {{ $client->name }}</h1>
    </div>

    @if($errors->any())
        <div style="background:#450a0a;color:#f87171;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.875rem;">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.clients.files.store', $client) }}" enctype="multipart/form-data" style="background:#0f172a;border:1px solid #1e293b;border-radius:12px;padding:28px;">
        @csrf
        <div style="margin-bottom:20px;">
            <label style="font-size:.8rem;font-weight:600;color:#94a3b8;display:block;margin-bottom:6px;">Arquivo <span style="color:#f87171;">*</span></label>
            <input type="file" name="file" required style="width:100%;background:#020617;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;">
        </div>
        <div style="margin-bottom:20px;">
            <label style="font-size:.8rem;font-weight:600;color:#94a3b8;display:block;margin-bottom:6px;">Notas</label>
            <textarea name="notes" rows="3" style="width:100%;background:#020617;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;resize:vertical;">{{ old('notes') }}</textarea>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;">Enviar</button>
            <a href="{{ route('admin.clients.files.index', $client) }}" style="background:#1e293b;color:#94a3b8;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>
</x-layouts.app>
