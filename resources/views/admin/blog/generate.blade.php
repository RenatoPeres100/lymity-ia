<x-layouts.app title="Gerar Post com IA">

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('admin.seo.blog.index') }}" style="font-size:.875rem;color:#64748b;text-decoration:none;">← Blog SEO</a>
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-top:.5rem;">Gerar Post de Blog com IA</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">O SEO IA irá criar um post completo com conteúdo, SEO e meta dados otimizados</p>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <form method="POST" action="{{ route('admin.seo.blog.generate.store') }}">
            @csrf

            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Tipo de Post *</label>
                <select name="type" id="typeSelect" onchange="toggleClientField()" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;">
                    <option value="agency" {{ old('type', 'agency') == 'agency' ? 'selected' : '' }}>Agência (blog próprio da Lymity)</option>
                    <option value="client" {{ old('type') == 'client' ? 'selected' : '' }}>Cliente (blog do cliente)</option>
                </select>
            </div>

            <div id="clientField" style="margin-bottom:1rem;{{ old('type') == 'client' ? '' : 'display:none;' }}">
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Cliente *</label>
                <select name="client_id" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;">
                    <option value="">Selecione um cliente...</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Palavra-chave foco *</label>
                <input type="text" name="keyword" value="{{ old('keyword') }}" required placeholder="Ex: marketing digital com inteligência artificial" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">
                <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">A IA usará essa palavra-chave como tema central e para otimizar o SEO do post.</div>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.375rem;">Título personalizado (opcional)</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Se vazio, a IA gera o título automaticamente" style="width:100%;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;box-sizing:border-box;">
            </div>

            <button type="submit" style="background:#6366f1;color:#fff;padding:.625rem 1.5rem;border-radius:.5rem;font-size:.875rem;font-weight:500;border:none;cursor:pointer;width:100%;">
                Gerar Post com IA
            </button>
        </form>
    </div>

    <div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
            <h3 style="font-size:.875rem;font-weight:600;color:#1e293b;margin-bottom:.75rem;">O que a IA vai gerar</h3>
            <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.5rem;">
                <li style="font-size:.8125rem;color:#64748b;display:flex;align-items:flex-start;gap:.5rem;">
                    <span style="color:#6366f1;font-weight:700;flex-shrink:0;">✓</span>
                    Título SEO otimizado
                </li>
                <li style="font-size:.8125rem;color:#64748b;display:flex;align-items:flex-start;gap:.5rem;">
                    <span style="color:#6366f1;font-weight:700;flex-shrink:0;">✓</span>
                    Conteúdo com 8+ parágrafos estruturados
                </li>
                <li style="font-size:.8125rem;color:#64748b;display:flex;align-items:flex-start;gap:.5rem;">
                    <span style="color:#6366f1;font-weight:700;flex-shrink:0;">✓</span>
                    Meta title e meta description
                </li>
                <li style="font-size:.8125rem;color:#64748b;display:flex;align-items:flex-start;gap:.5rem;">
                    <span style="color:#6366f1;font-weight:700;flex-shrink:0;">✓</span>
                    Palavra-chave foco e secundárias
                </li>
                <li style="font-size:.8125rem;color:#64748b;display:flex;align-items:flex-start;gap:.5rem;">
                    <span style="color:#6366f1;font-weight:700;flex-shrink:0;">✓</span>
                    Slug amigável para URL
                </li>
                <li style="font-size:.8125rem;color:#64748b;display:flex;align-items:flex-start;gap:.5rem;">
                    <span style="color:#6366f1;font-weight:700;flex-shrink:0;">✓</span>
                    Enviado para aprovação automaticamente
                </li>
            </ul>
        </div>

        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.75rem;padding:1.25rem;margin-top:1rem;">
            <div style="font-size:.8125rem;color:#92400e;font-weight:600;margin-bottom:.5rem;">Aviso</div>
            <div style="font-size:.8125rem;color:#78350f;line-height:1.5;">O post será criado como rascunho em aguardando aprovação. Nenhum conteúdo é publicado automaticamente sem aprovação.</div>
        </div>
    </div>
</div>

<script>
function toggleClientField() {
    const type = document.getElementById('typeSelect').value;
    const field = document.getElementById('clientField');
    field.style.display = type === 'client' ? 'block' : 'none';
}
</script>

</x-layouts.app>
