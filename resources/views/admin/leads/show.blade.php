<x-layouts.app title="Lead">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:4px;">Lead: {{ $lead->name }}</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.leads.index') }}" style="color:#6b8fff;text-decoration:none;">Leads</a> / Detalhes</p>
    </div>
    <div style="font-size:.75rem;color:#94a3b8;">Recebido {{ $lead->created_at->diffForHumans() }}</div>
</div>

@if(session('success'))
<div style="background:#0f2a1a;border:1px solid #166534;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#4ade80;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:28px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:18px;">Informações do Contato</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                @foreach([['Nome',$lead->name],['Email',$lead->email],['Telefone',$lead->phone ?? '—'],['Empresa',$lead->company ?? '—']] as [$label,$value])
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">{{ $label }}</div>
                    <div style="font-size:.9rem;color:#e2e8f0;">{{ $value }}</div>
                </div>
                @endforeach
            </div>
            @if($lead->service_interest)
            <div>
                <div style="font-size:.72rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Interesse</div>
                <div style="font-size:.9rem;color:#e2e8f0;">{{ str_replace('_',' ',ucfirst($lead->service_interest)) }}</div>
            </div>
            @endif
        </div>

        <div class="table-wrapper" style="padding:28px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Mensagem</div>
            <p style="font-size:.9rem;color:#cbd5e1;line-height:1.8;white-space:pre-wrap;">{{ $lead->message }}</p>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">Atualizar Status</div>
            <form action="{{ route('admin.leads.update', $lead) }}" method="POST">
                @csrf @method('PUT')
                <select name="status" style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 14px;color:#e2e8f0;font-size:.875rem;outline:none;box-sizing:border-box;margin-bottom:12px;">
                    @foreach(['new'=>'Novo','contacted'=>'Contatado','qualified'=>'Qualificado','converted'=>'Convertido','lost'=>'Perdido'] as $val => $label)
                    <option value="{{ $val }}" {{ $lead->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Notas internas</label>
                    <textarea name="notes" rows="4"
                        style="width:100%;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:10px 14px;color:#e2e8f0;font-size:.8rem;outline:none;resize:vertical;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ $lead->notes }}</textarea>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;cursor:pointer;border:none;text-align:center;">Salvar</button>
            </form>
        </div>

        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Metadados</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach([['ID',$lead->id],['UTM Source',$lead->utm_source ?? '—'],['UTM Medium',$lead->utm_medium ?? '—'],['IP',$lead->ip_address ?? '—'],['Criado',$lead->created_at->format('d/m/Y H:i')]] as [$label,$value])
                <div style="display:flex;justify-content:space-between;">
                    <span style="font-size:.75rem;color:#475569;">{{ $label }}</span>
                    <span style="font-size:.75rem;color:#94a3b8;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

</x-layouts.app>
