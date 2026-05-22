<x-layouts.app title="Configurações">

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Configurações da Agência</h2>
    <p class="text-sm text-slate-500 mt-1">Parâmetros globais da plataforma. Edição avançada nas próximas fases.</p>
</div>

<div class="alert alert-info mb-6 text-sm">
    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
    <span>
        <strong>Fase 1:</strong> As configurações estão sendo visualizadas em modo somente leitura. A edição completa será liberada na Fase 2.
    </span>
</div>

@if($settings->isEmpty())
<div class="card">
    <div class="py-14 text-center text-slate-400">
        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.07 4.93A10 10 0 0 1 21.5 12a10 10 0 0 1-2.43 7.07M4.93 4.93A10 10 0 0 0 2.5 12a10 10 0 0 0 2.43 7.07"/>
        </svg>
        <div class="text-sm font-medium text-slate-500 mb-1">Nenhuma configuração registrada</div>
        <div class="text-xs">As configurações aparecerão aqui após a configuração inicial.</div>
    </div>
</div>
@else
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($settings as $setting)
    <div class="card p-5">
        <div class="flex items-start justify-between mb-3">
            <code class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $setting->key }}</code>
            <span class="badge badge-gray text-xs">{{ $setting->type }}</span>
        </div>
        <div class="text-sm text-slate-700 break-all">
            {{ $setting->value ?? '(vazio)' }}
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Agency info --}}
<div class="mt-8 card">
    <div class="card-header">
        <span class="card-title">Informações da Agência</span>
    </div>
    <div class="card-body grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @php $company = \App\Models\Company::first(); @endphp
        @if($company)
        @foreach([
            ['Nome', $company->name],
            ['Razão Social', $company->legal_name ?? '—'],
            ['E-mail', $company->email ?? '—'],
            ['Website', $company->website ?? '—'],
            ['Status', $company->status],
        ] as [$label, $value])
        <div>
            <div class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-1">{{ $label }}</div>
            <div class="text-sm font-medium text-slate-700">{{ $value }}</div>
        </div>
        @endforeach
        @endif
    </div>
</div>

</x-layouts.app>
