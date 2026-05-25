<x-layouts.app title="Propostas">

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Propostas Comerciais</h2>
        <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Propostas criadas, aprovadas e enviadas aos clientes</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <a href="{{ route('admin.proposals.create') }}" class="btn btn-primary">+ Nova Proposta</a>
        <button onclick="document.getElementById('aiModal').style.display='flex'" class="btn btn-secondary">✦ Gerar com IA</button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    <select name="client_id" class="form-input" style="width:200px;">
        <option value="">Todos os clientes</option>
        @foreach($clients as $c)
        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
    </select>
    <select name="status" class="form-input" style="width:180px;">
        <option value="">Todos os status</option>
        @foreach(['draft'=>'Rascunho','pending_approval'=>'Aguardando Aprovação','approved'=>'Aprovado','rejected'=>'Rejeitado','sent'=>'Enviado','accepted'=>'Aceito','archived'=>'Arquivado'] as $val=>$label)
        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary">Filtrar</button>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Título</th>
                <th>Status</th>
                <th>Total</th>
                <th>Validade</th>
                <th>Criado por</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proposals as $proposal)
            <tr>
                <td style="color:#94a3b8;font-size:.75rem;">{{ $proposal->id }}</td>
                <td style="font-weight:500;">{{ $proposal->client->name ?? '—' }}</td>
                <td>{{ $proposal->title }}</td>
                <td><span class="badge badge-{{ $proposal->status_color }}">{{ $proposal->status_label }}</span></td>
                <td>{{ $proposal->total_amount ? 'R$ '.number_format($proposal->total_amount,2,',','.') : '—' }}</td>
                <td>{{ $proposal->valid_until?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:.82rem;color:#64748b;">{{ $proposal->creator->name ?? '—' }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <a href="{{ route('admin.proposals.show', $proposal) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.78rem;">Ver</a>
                        <a href="{{ route('admin.proposals.edit', $proposal) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:.78rem;">Editar</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:#94a3b8;">Nenhuma proposta criada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $proposals->links() }}

{{-- AI Modal --}}
<div id="aiModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:480px;margin:16px;">
        <h3 style="font-weight:700;color:#1e293b;margin-bottom:1rem;">✦ Gerar Proposta com IA</h3>
        <form method="POST" action="{{ route('admin.proposals.generate-ai') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Cliente</label>
                <select name="client_id" class="form-input" required>
                    <option value="">Selecione...</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Título da Proposta</label>
                <input type="text" name="title" class="form-input" placeholder="Ex: Proposta de crescimento digital" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contexto / Briefing (opcional)</label>
                <textarea name="description" class="form-input" rows="3" placeholder="Descreva o escopo, objetivos..."></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('aiModal').style.display='none'" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Gerar</button>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
