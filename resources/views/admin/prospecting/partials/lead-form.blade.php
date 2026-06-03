<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Nome --}}
    <div class="md:col-span-2">
        <label class="form-label">Nome do contato <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $lead->name ?? '') }}"
            class="input-field w-full @error('name') border-red-400 @enderror" required>
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Empresa --}}
    <div>
        <label class="form-label">Empresa / Negócio</label>
        <input type="text" name="company_name" value="{{ old('company_name', $lead->company_name ?? '') }}"
            class="input-field w-full">
    </div>

    {{-- Segmento --}}
    <div>
        <label class="form-label">Segmento</label>
        <input type="text" name="segment" value="{{ old('segment', $lead->segment ?? '') }}"
            placeholder="Ex: Clínica de estética, E-commerce, Restaurante..."
            class="input-field w-full">
    </div>

    {{-- Website --}}
    <div>
        <label class="form-label">Website</label>
        <input type="url" name="website" value="{{ old('website', $lead->website ?? '') }}"
            placeholder="https://..."
            class="input-field w-full">
    </div>

    {{-- Instagram --}}
    <div>
        <label class="form-label">Instagram</label>
        <input type="text" name="instagram" value="{{ old('instagram', $lead->instagram ?? '') }}"
            placeholder="@usuario"
            class="input-field w-full">
    </div>

    {{-- LinkedIn --}}
    <div>
        <label class="form-label">LinkedIn</label>
        <input type="text" name="linkedin" value="{{ old('linkedin', $lead->linkedin ?? '') }}"
            class="input-field w-full">
    </div>

    {{-- WhatsApp --}}
    <div>
        <label class="form-label">WhatsApp</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp', $lead->whatsapp ?? '') }}"
            placeholder="11999999999"
            class="input-field w-full">
    </div>

    {{-- E-mail --}}
    <div>
        <label class="form-label">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $lead->email ?? '') }}"
            class="input-field w-full">
    </div>

    {{-- Telefone --}}
    <div>
        <label class="form-label">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $lead->phone ?? '') }}"
            class="input-field w-full">
    </div>

    {{-- Cidade --}}
    <div>
        <label class="form-label">Cidade</label>
        <input type="text" name="city" value="{{ old('city', $lead->city ?? '') }}"
            class="input-field w-full">
    </div>

    {{-- Estado --}}
    <div>
        <label class="form-label">Estado</label>
        <input type="text" name="state" value="{{ old('state', $lead->state ?? '') }}"
            placeholder="SP, RJ, MG..."
            class="input-field w-full" maxlength="2">
    </div>

    {{-- Origem --}}
    <div>
        <label class="form-label">Origem do lead</label>
        <input type="text" name="source" value="{{ old('source', $lead->source ?? '') }}"
            placeholder="Ex: prospecção manual, indicação, evento..."
            class="input-field w-full">
    </div>

    {{-- Interesse --}}
    <div>
        <label class="form-label">Nível de interesse</label>
        <select name="interest_level" class="input-field w-full">
            <option value="">Selecionar...</option>
            <option value="cold" {{ old('interest_level', $lead->interest_level ?? '') === 'cold' ? 'selected' : '' }}>Frio</option>
            <option value="warm" {{ old('interest_level', $lead->interest_level ?? '') === 'warm' ? 'selected' : '' }}>Morno</option>
            <option value="hot"  {{ old('interest_level', $lead->interest_level ?? '') === 'hot'  ? 'selected' : '' }}>Quente</option>
        </select>
    </div>

    {{-- Fit Score --}}
    <div>
        <label class="form-label">Fit Score (0–100)</label>
        <input type="number" name="fit_score" value="{{ old('fit_score', $lead->fit_score ?? '') }}"
            min="0" max="100"
            class="input-field w-full">
    </div>

    {{-- Orçamento estimado --}}
    <div>
        <label class="form-label">Orçamento estimado</label>
        <input type="text" name="estimated_budget" value="{{ old('estimated_budget', $lead->estimated_budget ?? '') }}"
            placeholder="Ex: R$ 3.000–5.000/mês"
            class="input-field w-full">
    </div>

    {{-- Valor potencial --}}
    <div>
        <label class="form-label">Valor potencial (R$)</label>
        <input type="number" name="potential_value" value="{{ old('potential_value', $lead->potential_value ?? '') }}"
            step="0.01" min="0"
            class="input-field w-full">
    </div>

    {{-- Próxima ação --}}
    <div>
        <label class="form-label">Próxima ação</label>
        <input type="text" name="next_action" value="{{ old('next_action', $lead->next_action ?? '') }}"
            placeholder="Ex: enviar primeira abordagem"
            class="input-field w-full">
    </div>

    {{-- Próximo follow-up --}}
    <div>
        <label class="form-label">Próximo follow-up</label>
        <input type="date" name="next_follow_up_at"
            value="{{ old('next_follow_up_at', isset($lead->next_follow_up_at) ? $lead->next_follow_up_at->format('Y-m-d') : '') }}"
            class="input-field w-full">
    </div>

    {{-- Responsável --}}
    @if(isset($owners))
    <div>
        <label class="form-label">Responsável</label>
        <select name="owner_id" class="input-field w-full">
            <option value="">Selecionar...</option>
            @foreach($owners as $owner)
            <option value="{{ $owner->id }}" {{ old('owner_id', $lead->owner_id ?? auth()->id()) == $owner->id ? 'selected' : '' }}>
                {{ $owner->name }}
            </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Dores --}}
    <div class="md:col-span-2">
        <label class="form-label">Dores / Problemas identificados</label>
        <textarea name="pain_points" rows="3" class="input-field w-full"
            placeholder="Descreva os problemas ou desafios que este lead enfrenta...">{{ old('pain_points', $lead->pain_points ?? '') }}</textarea>
    </div>

    {{-- Resumo da oportunidade --}}
    <div class="md:col-span-2">
        <label class="form-label">Resumo da oportunidade</label>
        <textarea name="opportunity_summary" rows="3" class="input-field w-full"
            placeholder="Como a Lymity IA pode ajudar este lead?">{{ old('opportunity_summary', $lead->opportunity_summary ?? '') }}</textarea>
    </div>

    {{-- Observações --}}
    <div class="md:col-span-2">
        <label class="form-label">Observações internas</label>
        <textarea name="notes" rows="3" class="input-field w-full"
            placeholder="Notas internas sobre este lead...">{{ old('notes', $lead->notes ?? '') }}</textarea>
    </div>
</div>
