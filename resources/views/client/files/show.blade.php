<x-layouts.app title="{{ $file->name }}">
<div style="max-width:700px;margin:0 auto;padding:32px 16px;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('client.files.index') }}" style="color:#64748b;font-size:.8rem;text-decoration:none;">← Meus Arquivos</a>
        <h1 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin:8px 0 4px;">{{ $file->name }}</h1>
    </div>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:20px;">
        @if($url)
            @if($file->isImage())
                <img src="{{ $url }}" alt="{{ $file->name }}" style="max-width:100%;max-height:400px;border-radius:8px;display:block;margin:0 auto 16px;">
            @else
                <div style="text-align:center;padding:20px;font-size:3.5rem;margin-bottom:8px;">{{ $file->isPdf() ? '📄' : '📁' }}</div>
            @endif
            <div style="text-align:center;">
                <a href="{{ $url }}" target="_blank" style="background:#4a6cf7;color:#fff;font-size:.875rem;font-weight:600;padding:10px 24px;border-radius:8px;text-decoration:none;">Abrir / Baixar</a>
            </div>
        @else
            <p style="text-align:center;color:#94a3b8;font-size:.875rem;">Arquivo sem URL disponível.</p>
        @endif
    </div>

    @foreach([['Tipo',$file->file_type_label],['Tamanho',$file->formatted_size],['Data de envio',$file->created_at->format('d/m/Y H:i')],['Observações',$file->notes??'—']] as [$label,$value])
    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:.8rem;">
        <span style="color:#64748b;">{{ $label }}</span>
        <span style="color:#334155;">{{ $value }}</span>
    </div>
    @endforeach

    <div style="margin-top:20px;">
        <form method="POST" action="{{ route('client.files.destroy', $file) }}" onsubmit="return confirm('Remover arquivo?')">
            @csrf @method('DELETE')
            <button style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;font-size:.875rem;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">Remover</button>
        </form>
    </div>
</div>
</x-layouts.app>
