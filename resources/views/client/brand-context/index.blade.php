<x-layouts.app title="Brand Context">
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;">Brand Context</h1>
    <p style="color:#64748b;font-size:.875rem;margin-top:4px;">Informações de marca do seu negócio utilizadas pelos funcionários IA.</p>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

@if($brand)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:28px;">
    <form method="POST" action="{{ route('client.brand-context.update') }}">
        @csrf @method('PUT')
        @foreach(['brand_voice'=>'Voz da Marca','target_audience'=>'Público-Alvo','tone'=>'Tom de Comunicação','differentials'=>'Diferenciais'] as $field=>$label)
        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">{{ $label }}</label>
            <textarea name="{{ $field }}" rows="3" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:.875rem;box-sizing:border-box;resize:vertical;">{{ old($field, $brand->$field ?? '') }}</textarea>
        </div>
        @endforeach
        @if(auth()->user()->hasPermission('client.brand_context.update'))
        <button type="submit" style="background:#4a6cf7;color:#fff;padding:10px 24px;border-radius:8px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;">Salvar</button>
        @endif
    </form>
</div>
@else
<div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:12px;padding:32px;text-align:center;color:#1e40af;">
    <div style="font-size:1rem;font-weight:600;margin-bottom:8px;">Brand Context não configurado</div>
    <p style="font-size:.875rem;color:#3b82f6;">Entre em contato com a agência para configurar o Brand Context do seu negócio.</p>
</div>
@endif
</x-layouts.app>
