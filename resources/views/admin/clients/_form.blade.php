@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.8rem;color:#dc2626;">
    <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

    {{-- Left column --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Bloco 1: Dados principais --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Dados principais</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Nome do cliente *</label>
                    <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" required style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Razão social</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name', $client->legal_name ?? '') }}" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">CNPJ / CPF</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id', $client->tax_id ?? '') }}" placeholder="00.000.000/0000-00" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Segmento</label>
                    <input type="text" name="segment" value="{{ old('segment', $client->segment ?? '') }}" placeholder="Ex: Clínica de estética" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Status *</label>
                    <select name="status" required style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                        <option value="active"   {{ old('status', $client->status ?? 'active') === 'active'   ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ old('status', $client->status ?? '') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                        <option value="archived" {{ old('status', $client->status ?? '') === 'archived' ? 'selected' : '' }}>Arquivado</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Bloco 2: Contato --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Contato</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $client->email ?? '') }}" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone ?? '') }}" placeholder="(11) 9999-9999" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">WhatsApp</label>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp', $client->whatsapp ?? '') }}" placeholder="5511999999999" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Site</label>
                    <input type="url" name="website" value="{{ old('website', $client->website ?? '') }}" placeholder="https://site.com.br" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
            </div>
        </div>

        {{-- Bloco 3: Redes Sociais --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Redes Sociais</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Instagram</label>
                    <input type="text" name="instagram" value="{{ old('instagram', $client->instagram ?? '') }}" placeholder="@perfil" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Facebook</label>
                    <input type="text" name="facebook" value="{{ old('facebook', $client->facebook ?? '') }}" placeholder="URL ou @perfil" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">LinkedIn</label>
                    <input type="text" name="linkedin" value="{{ old('linkedin', $client->linkedin ?? '') }}" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">TikTok</label>
                    <input type="text" name="tiktok" value="{{ old('tiktok', $client->tiktok ?? '') }}" placeholder="@perfil" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">YouTube</label>
                    <input type="text" name="youtube" value="{{ old('youtube', $client->youtube ?? '') }}" placeholder="URL do canal" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.85rem;color:#0f172a;box-sizing:border-box;">
                </div>
            </div>
        </div>

    </div>

    {{-- Right column: Estratégia --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Estratégia da marca</h3>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Tom de voz</label>
                <textarea name="brand_voice" rows="3" placeholder="Ex: profissional, acolhedor, sofisticado..." style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.82rem;color:#0f172a;box-sizing:border-box;resize:vertical;">{{ old('brand_voice', $client->brand_voice ?? '') }}</textarea>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Público-alvo</label>
                <textarea name="target_audience" rows="3" placeholder="Ex: mulheres de 25-50 anos interessadas em..." style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.82rem;color:#0f172a;box-sizing:border-box;resize:vertical;">{{ old('target_audience', $client->target_audience ?? '') }}</textarea>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Oferta principal</label>
                <textarea name="offer_description" rows="3" placeholder="Ex: tratamentos estéticos personalizados..." style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.82rem;color:#0f172a;box-sizing:border-box;resize:vertical;">{{ old('offer_description', $client->offer_description ?? '') }}</textarea>
            </div>
            <div>
                <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;">Observações internas</label>
                <textarea name="notes" rows="3" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:.82rem;color:#0f172a;box-sizing:border-box;resize:vertical;">{{ old('notes', $client->notes ?? '') }}</textarea>
            </div>
        </div>

        {{-- Submit --}}
        <div style="display:flex;gap:10px;">
            <button type="submit" style="flex:1;background:#4a6cf7;color:#fff;font-size:.85rem;font-weight:600;padding:11px;border-radius:8px;border:none;cursor:pointer;">
                {{ isset($client) && $client->exists ? 'Salvar alterações' : 'Criar cliente' }}
            </button>
            <a href="{{ route('admin.clients.index') }}" style="flex:1;background:#f1f5f9;color:#475569;font-size:.85rem;font-weight:600;padding:11px;border-radius:8px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </div>
</div>
