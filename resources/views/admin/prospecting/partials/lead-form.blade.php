@php
$s = 'border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;width:100%;box-sizing:border-box;';
$lbl = 'display:block;font-size:.8rem;font-weight:500;color:#374151;margin-bottom:.3rem;';
$err = 'font-size:.75rem;color:#dc2626;margin-top:.25rem;';
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

    {{-- Nome --}}
    <div style="grid-column:1/-1;">
        <label style="{{ $lbl }}">Nome do contato <span style="color:#ef4444;">*</span></label>
        <input type="text" name="name" value="{{ old('name', $lead->name ?? '') }}" required
            style="{{ $s }}{{ $errors->has('name') ? 'border-color:#dc2626;' : '' }}">
        @error('name') <p style="{{ $err }}">{{ $message }}</p> @enderror
    </div>

    {{-- Empresa --}}
    <div>
        <label style="{{ $lbl }}">Empresa / Negócio</label>
        <input type="text" name="company_name" value="{{ old('company_name', $lead->company_name ?? '') }}" style="{{ $s }}">
    </div>

    {{-- Segmento --}}
    <div>
        <label style="{{ $lbl }}">Segmento</label>
        <input type="text" name="segment" value="{{ old('segment', $lead->segment ?? '') }}"
            placeholder="Ex: Clínica de estética, E-commerce..." style="{{ $s }}">
    </div>

    {{-- Website --}}
    <div>
        <label style="{{ $lbl }}">Website</label>
        <input type="url" name="website" value="{{ old('website', $lead->website ?? '') }}"
            placeholder="https://..." style="{{ $s }}">
    </div>

    {{-- Instagram --}}
    <div>
        <label style="{{ $lbl }}">Instagram</label>
        <input type="text" name="instagram" value="{{ old('instagram', $lead->instagram ?? '') }}"
            placeholder="@usuario" style="{{ $s }}">
    </div>

    {{-- LinkedIn --}}
    <div>
        <label style="{{ $lbl }}">LinkedIn</label>
        <input type="text" name="linkedin" value="{{ old('linkedin', $lead->linkedin ?? '') }}" style="{{ $s }}">
    </div>

    {{-- WhatsApp --}}
    <div>
        <label style="{{ $lbl }}">WhatsApp</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp', $lead->whatsapp ?? '') }}"
            placeholder="11999999999" style="{{ $s }}">
    </div>

    {{-- E-mail --}}
    <div>
        <label style="{{ $lbl }}">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $lead->email ?? '') }}" style="{{ $s }}">
    </div>

    {{-- Telefone --}}
    <div>
        <label style="{{ $lbl }}">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $lead->phone ?? '') }}" style="{{ $s }}">
    </div>

    {{-- Cidade --}}
    <div>
        <label style="{{ $lbl }}">Cidade</label>
        <input type="text" name="city" value="{{ old('city', $lead->city ?? '') }}" style="{{ $s }}">
    </div>

    {{-- Estado --}}
    <div>
        <label style="{{ $lbl }}">Estado</label>
        <input type="text" name="state" value="{{ old('state', $lead->state ?? '') }}"
            placeholder="SP, RJ, MG..." maxlength="2" style="{{ $s }}">
    </div>

    {{-- Origem --}}
    <div>
        <label style="{{ $lbl }}">Origem do lead</label>
        <input type="text" name="source" value="{{ old('source', $lead->source ?? '') }}"
            placeholder="Ex: prospecção manual, indicação, evento..." style="{{ $s }}">
    </div>

    {{-- Interesse --}}
    <div>
        <label style="{{ $lbl }}">Nível de interesse</label>
        <select name="interest_level" style="{{ $s }}">
            <option value="">Selecionar...</option>
            <option value="cold" {{ old('interest_level', $lead->interest_level ?? '') === 'cold' ? 'selected' : '' }}>Frio</option>
            <option value="warm" {{ old('interest_level', $lead->interest_level ?? '') === 'warm' ? 'selected' : '' }}>Morno</option>
            <option value="hot"  {{ old('interest_level', $lead->interest_level ?? '') === 'hot'  ? 'selected' : '' }}>Quente</option>
        </select>
    </div>

    {{-- Fit Score --}}
    <div>
        <label style="{{ $lbl }}">Fit Score (0–100)</label>
        <input type="number" name="fit_score" value="{{ old('fit_score', $lead->fit_score ?? '') }}"
            min="0" max="100" style="{{ $s }}">
    </div>

    {{-- Orçamento estimado --}}
    <div>
        <label style="{{ $lbl }}">Orçamento estimado</label>
        <input type="text" name="estimated_budget" value="{{ old('estimated_budget', $lead->estimated_budget ?? '') }}"
            placeholder="Ex: R$ 3.000–5.000/mês" style="{{ $s }}">
    </div>

    {{-- Valor potencial --}}
    <div>
        <label style="{{ $lbl }}">Valor potencial (R$)</label>
        <input type="number" name="potential_value" value="{{ old('potential_value', $lead->potential_value ?? '') }}"
            step="0.01" min="0" style="{{ $s }}">
    </div>

    {{-- Próxima ação --}}
    <div>
        <label style="{{ $lbl }}">Próxima ação</label>
        <input type="text" name="next_action" value="{{ old('next_action', $lead->next_action ?? '') }}"
            placeholder="Ex: enviar primeira abordagem" style="{{ $s }}">
    </div>

    {{-- Próximo follow-up --}}
    <div>
        <label style="{{ $lbl }}">Próximo follow-up</label>
        <input type="date" name="next_follow_up_at"
            value="{{ old('next_follow_up_at', isset($lead->next_follow_up_at) && $lead->next_follow_up_at ? $lead->next_follow_up_at->format('Y-m-d') : '') }}"
            style="{{ $s }}">
    </div>

    {{-- Responsável --}}
    @if(isset($owners) && count($owners))
    <div>
        <label style="{{ $lbl }}">Responsável</label>
        <select name="owner_id" style="{{ $s }}">
            <option value="">Selecionar...</option>
            @foreach($owners as $owner)
            <option value="{{ $owner->id }}" {{ old('owner_id', $lead->owner_id ?? auth()->id()) == $owner->id ? 'selected' : '' }}>
                {{ $owner->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div></div>
    @endif

    {{-- Dores --}}
    <div style="grid-column:1/-1;">
        <label style="{{ $lbl }}">Dores / Problemas identificados</label>
        <textarea name="pain_points" rows="3" placeholder="Descreva os problemas ou desafios que este lead enfrenta..."
            style="{{ $s }}">{{ old('pain_points', $lead->pain_points ?? '') }}</textarea>
    </div>

    {{-- Resumo da oportunidade --}}
    <div style="grid-column:1/-1;">
        <label style="{{ $lbl }}">Resumo da oportunidade</label>
        <textarea name="opportunity_summary" rows="3" placeholder="Como a Lymity IA pode ajudar este lead?"
            style="{{ $s }}">{{ old('opportunity_summary', $lead->opportunity_summary ?? '') }}</textarea>
    </div>

    {{-- Observações --}}
    <div style="grid-column:1/-1;">
        <label style="{{ $lbl }}">Observações internas</label>
        <textarea name="notes" rows="3" placeholder="Notas internas sobre este lead..."
            style="{{ $s }}">{{ old('notes', $lead->notes ?? '') }}</textarea>
    </div>
</div>
