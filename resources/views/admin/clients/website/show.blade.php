<x-layouts.app :title="'Website — ' . $client->name">

<div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
    <a href="{{ route('admin.clients') }}" class="hover:text-slate-700">Clientes</a>
    <span>/</span>
    <span class="text-slate-700 font-medium">{{ $client->name }}</span>
    <span>/</span>
    <span class="text-slate-900 font-semibold">Website</span>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

@if($website)
{{-- Website exists — show info + edit --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="card p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-400">Domínio</div>
            <div class="font-semibold text-slate-800">{{ $website->domain ?: '—' }}</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-400">Plataforma</div>
            <div class="font-semibold text-slate-800">{{ $website->platform_label }}</div>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl {{ $website->status === 'active' ? 'bg-emerald-100' : 'bg-slate-100' }} flex items-center justify-center">
            <svg class="w-5 h-5 {{ $website->status === 'active' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-400">Status</div>
            <div class="font-semibold text-slate-800">{{ $website->status_label }}</div>
        </div>
    </div>
</div>

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('admin.clients.pages.index', $client) }}" class="btn btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
        Gerenciar Páginas
    </a>
</div>

<form method="POST" action="{{ route('admin.clients.website.update', [$client, $website]) }}">
    @csrf @method('PUT')

@else
<form method="POST" action="{{ route('admin.clients.website.store', $client) }}">
    @csrf
@endif

    <div class="card p-6">
        <h3 class="font-semibold text-slate-800 mb-5">{{ $website ? 'Editar Website' : 'Criar Website' }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Domínio</label>
                <input type="text" name="domain" value="{{ old('domain', $website?->domain) }}"
                    class="input" placeholder="ex: meusite.com.br">
            </div>
            <div>
                <label class="label">Plataforma</label>
                <select name="platform" class="input">
                    <option value="internal" @selected(old('platform', $website?->platform) === 'internal')>Interna (Lymity)</option>
                    <option value="wordpress" @selected(old('platform', $website?->platform) === 'wordpress')>WordPress</option>
                    <option value="external" @selected(old('platform', $website?->platform) === 'external')>Externa</option>
                </select>
            </div>
            <div>
                <label class="label">Status</label>
                <select name="status" class="input">
                    <option value="planning" @selected(old('status', $website?->status) === 'planning')>Planejamento</option>
                    <option value="active" @selected(old('status', $website?->status) === 'active')>Ativo</option>
                    <option value="paused" @selected(old('status', $website?->status) === 'paused')>Pausado</option>
                    <option value="archived" @selected(old('status', $website?->status) === 'archived')>Arquivado</option>
                </select>
            </div>
            <div>
                <label class="label">Cor Primária</label>
                <input type="text" name="primary_color" value="{{ old('primary_color', $website?->primary_color) }}"
                    class="input" placeholder="#2563eb">
            </div>
            <div>
                <label class="label">Cor Secundária</label>
                <input type="text" name="secondary_color" value="{{ old('secondary_color', $website?->secondary_color) }}"
                    class="input" placeholder="#64748b">
            </div>
            <div>
                <label class="label">URL do Logo</label>
                <input type="url" name="logo_url" value="{{ old('logo_url', $website?->logo_url) }}"
                    class="input" placeholder="https://...">
            </div>
            <div class="md:col-span-2">
                <label class="label">Notas</label>
                <textarea name="notes" rows="3" class="input resize-none"
                    placeholder="Observações sobre o website...">{{ old('notes', $website?->notes) }}</textarea>
            </div>
        </div>
        <div class="flex justify-end mt-5">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $website ? 'Salvar' : 'Criar Website' }}
            </button>
        </div>
    </div>

</form>

</x-layouts.app>
