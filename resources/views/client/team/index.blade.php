<x-layouts.app title="Minha Equipe">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Minha Equipe</h1>
        <p style="font-size:.85rem;color:#64748b;">Gerencie os colaboradores do seu ambiente.</p>
    </div>
    @if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('client.team.create') }}" style="background:#6366f1;color:#fff;padding:10px 20px;border-radius:8px;font-size:.875rem;font-weight:600;text-decoration:none;">+ Novo Colaborador</a>
    @endif
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

@if($team->isEmpty())
<div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px;padding:40px;text-align:center;color:#64748b;">
    <div style="font-size:2rem;margin-bottom:12px;">👥</div>
    <p style="font-weight:600;margin-bottom:4px;">Nenhum colaborador ainda</p>
    <p style="font-size:.85rem;">Adicione membros da equipe para colaborar no seu ambiente.</p>
    @if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('client.team.create') }}" style="display:inline-block;margin-top:16px;background:#6366f1;color:#fff;padding:10px 20px;border-radius:8px;font-size:.875rem;font-weight:600;text-decoration:none;">+ Criar primeiro colaborador</a>
    @endif
</div>
@else
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 20px;font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Nome</th>
                <th style="text-align:left;padding:12px 20px;font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Cargo</th>
                <th style="text-align:left;padding:12px 20px;font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Permissões</th>
                <th style="text-align:left;padding:12px 20px;font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Status</th>
                <th style="text-align:right;padding:12px 20px;font-size:.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($team as $member)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:14px 20px;">
                    <div style="font-weight:600;color:#0f172a;font-size:.9rem;">{{ $member->name }}</div>
                    <div style="font-size:.78rem;color:#94a3b8;">{{ $member->email }}</div>
                </td>
                <td style="padding:14px 20px;font-size:.875rem;color:#334155;">{{ $member->job_title ?: '—' }}</td>
                <td style="padding:14px 20px;">
                    <span style="font-size:.78rem;color:#64748b;">{{ $member->permissions->count() }} permissões</span>
                </td>
                <td style="padding:14px 20px;">
                    @if($member->status === 'active')
                        <span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;">Ativo</span>
                    @else
                        <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;">Inativo</span>
                    @endif
                </td>
                <td style="padding:14px 20px;text-align:right;">
                    <a href="{{ route('client.team.show', $member) }}" style="color:#6366f1;font-size:.825rem;font-weight:500;text-decoration:none;">Ver</a>
                    <a href="{{ route('client.team.edit', $member) }}" style="color:#64748b;font-size:.825rem;font-weight:500;text-decoration:none;margin-left:12px;">Editar</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</x-layouts.app>
