<x-layouts.app title="Novo Upload">
<div style="max-width:700px;margin:0 auto;padding:32px 16px;">

    <div style="margin-bottom:28px;">
        <a href="{{ route('admin.files.index') }}" style="color:#64748b;font-size:.8rem;text-decoration:none;">← Arquivos</a>
        <h1 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin:8px 0 4px;">Novo Upload</h1>
    </div>

    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.875rem;">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="font-size:.8rem;font-weight:600;color:#475569;display:block;margin-bottom:6px;">Arquivo <span style="color:#dc2626;">*</span></label>
            <input type="file" name="file" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
            <p style="font-size:.72rem;color:#94a3b8;margin:4px 0 0;">Máximo 10 MB. Imagens, PDFs, documentos, vídeos leves.</p>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-size:.8rem;font-weight:600;color:#475569;display:block;margin-bottom:6px;">Cliente (opcional)</label>
            <select name="client_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
                <option value="">— Arquivo da agência —</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-size:.8rem;font-weight:600;color:#475569;display:block;margin-bottom:6px;">Notas</label>
            <textarea name="notes" rows="3" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex;gap:10px;margin-bottom:20px;">
            <div style="flex:1;">
                <label style="font-size:.8rem;font-weight:600;color:#475569;display:block;margin-bottom:6px;">Tipo relacionado (opcional)</label>
                <input type="text" name="related_type" value="{{ old('related_type') }}" placeholder="Ex: App\Models\SocialPost" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
            </div>
            <div style="flex:1;">
                <label style="font-size:.8rem;font-weight:600;color:#475569;display:block;margin-bottom:6px;">ID relacionado</label>
                <input type="number" name="related_id" value="{{ old('related_id') }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;">
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;">Enviar Arquivo</button>
            <a href="{{ route('admin.files.index') }}" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</div>
</x-layouts.app>
