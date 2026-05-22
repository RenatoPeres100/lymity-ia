<x-layouts.app title="Minha Marca">

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Perfil de Marca</h2>
    <p class="text-sm text-slate-500 mt-1">Informações de branding definidas pela sua agência.</p>
</div>

@if(!$brand)
<div class="card p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.477-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
    <p class="text-slate-500 text-sm">Perfil de marca ainda não foi configurado. Aguarde sua agência.</p>
</div>
@else

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    @foreach([
        ['Tom de Voz', $brand->tone_of_voice, 'text-violet-600', 'bg-violet-100', '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
        ['Público-alvo', $brand->target_audience, 'text-blue-600', 'bg-blue-100', '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>'],
        ['Oferta Principal', $brand->main_offer, 'text-emerald-600', 'bg-emerald-100', '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/>'],
        ['Principais Objeções', $brand->objections, 'text-amber-600', 'bg-amber-100', '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>'],
        ['CTAs Recomendados', $brand->cta_examples, 'text-indigo-600', 'bg-indigo-100', '<path d="M5 12h14M12 5l7 7-7 7"/>'],
        ['Estilo Visual', $brand->visual_style, 'text-pink-600', 'bg-pink-100', '<circle cx="13.5" cy="6.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688z"/>'],
        ['Termos Proibidos', $brand->forbidden_terms, 'text-red-600', 'bg-red-100', '<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>'],
        ['Termos Preferidos', $brand->preferred_terms, 'text-green-600', 'bg-green-100', '<polyline points="20 6 9 17 4 12"/>'],
    ] as [$label, $value, $iconColor, $iconBg, $iconPath])
    @if($value)
    <div class="card p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 rounded-lg {{ $iconBg }} flex items-center justify-center">
                <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $iconPath !!}</svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-700">{{ $label }}</h3>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $value }}</p>
    </div>
    @endif
    @endforeach

    @if($brand->notes)
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-700">Notas</h3>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $brand->notes }}</p>
    </div>
    @endif

</div>

@endif

</x-layouts.app>
