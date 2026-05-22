<x-layouts.app :title="'Marca — ' . $client->name">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <span class="text-slate-700 font-medium">{{ $client->name }}</span>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Perfil de Marca</span>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ $brand ? route('admin.clients.brand.update', $client) : route('admin.clients.brand.store', $client) }}">
    @csrf
    @if($brand) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Tom de Voz --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Tom de Voz</h3>
            </div>
            <textarea name="tone_of_voice" rows="4"
                class="input w-full resize-none"
                placeholder="Ex.: Profissional, empático, direto ao ponto. Evita jargões técnicos...">{{ old('tone_of_voice', $brand?->tone_of_voice) }}</textarea>
        </div>

        {{-- Público-alvo --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Público-alvo</h3>
            </div>
            <textarea name="target_audience" rows="4"
                class="input w-full resize-none"
                placeholder="Ex.: Empreendedores B2B entre 30-50 anos, dono de pequenas empresas de serviços...">{{ old('target_audience', $brand?->target_audience) }}</textarea>
        </div>

        {{-- Oferta Principal --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><path d="M12 22V7m0 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0-4a2 2 0 1 0 0 4"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Oferta Principal</h3>
            </div>
            <textarea name="main_offer" rows="4"
                class="input w-full resize-none"
                placeholder="Ex.: Consultoria de marketing digital com foco em geração de leads qualificados...">{{ old('main_offer', $brand?->main_offer) }}</textarea>
        </div>

        {{-- Objeções --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Principais Objeções</h3>
            </div>
            <textarea name="objections" rows="4"
                class="input w-full resize-none"
                placeholder="Ex.: Preço alto, falta de tempo para acompanhar, dificuldade em medir resultados...">{{ old('objections', $brand?->objections) }}</textarea>
        </div>

        {{-- Concorrentes --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Concorrentes</h3>
            </div>
            <textarea name="competitors" rows="4"
                class="input w-full resize-none"
                placeholder="Ex.: Empresa X (forte em redes sociais), Empresa Y (foco em SEO)...">{{ old('competitors', $brand?->competitors) }}</textarea>
        </div>

        {{-- Estilo Visual --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.477-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Estilo Visual</h3>
            </div>
            <textarea name="visual_style" rows="4"
                class="input w-full resize-none"
                placeholder="Ex.: Cores sóbrias (azul marinho e branco), fontes sans-serif, imagens profissionais...">{{ old('visual_style', $brand?->visual_style) }}</textarea>
        </div>

        {{-- Termos Proibidos --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Termos Proibidos</h3>
            </div>
            <textarea name="forbidden_terms" rows="3"
                class="input w-full resize-none"
                placeholder="Ex.: barato, improviso, amador...">{{ old('forbidden_terms', $brand?->forbidden_terms) }}</textarea>
        </div>

        {{-- Termos Preferidos --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Termos Preferidos</h3>
            </div>
            <textarea name="preferred_terms" rows="3"
                class="input w-full resize-none"
                placeholder="Ex.: solução, resultado, transformação, crescimento...">{{ old('preferred_terms', $brand?->preferred_terms) }}</textarea>
        </div>

        {{-- CTAs --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">CTAs (Chamadas para Ação)</h3>
            </div>
            <textarea name="cta_examples" rows="3"
                class="input w-full resize-none"
                placeholder="Ex.: Solicite uma proposta, Fale com um especialista, Agende sua consultoria...">{{ old('cta_examples', $brand?->cta_examples) }}</textarea>
        </div>

        {{-- Notas --}}
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800">Notas Adicionais</h3>
            </div>
            <textarea name="notes" rows="3"
                class="input w-full resize-none"
                placeholder="Outras informações relevantes sobre a marca...">{{ old('notes', $brand?->notes) }}</textarea>
        </div>

    </div>

    <div class="flex items-center justify-end gap-3 mt-6">
        <a href="{{ route('admin.clients') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ $brand ? 'Salvar alterações' : 'Criar perfil de marca' }}
        </button>
    </div>

</form>

</x-layouts.app>
