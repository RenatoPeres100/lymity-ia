<x-layouts.app title="Agendamentos IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:4px;">Agendamentos IA</h1>
        <p style="font-size:.85rem;color:#64748b;">Rotinas automáticas dos funcionários IA.</p>
    </div>
    <a href="{{ route('admin.ai-schedules.create') }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;text-decoration:none;">+ Novo Agendamento</a>
</div>

@if(session('success'))
<div style="background:#0f2a1a;border:1px solid #166534;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#4ade80;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #1e293b;">
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Funcionário</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Frequência</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Próxima Execução</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Última Execução</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
            <tr style="border-bottom:1px solid #0f172a;transition:background .15s;" onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#e2e8f0;">{{ $schedule->employee?->name ?? '—' }}</div>
                    @if($schedule->client)<div style="font-size:.72rem;color:#475569;">{{ $schedule->client->name }}</div>@endif
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:#94a3b8;">{{ $schedule->frequency_label }}</td>
                <td style="padding:12px 16px;font-size:.78rem;color:{{ $schedule->isDue() ? '#fb923c' : '#94a3b8' }};">
                    {{ $schedule->next_run_at?->format('d/m/Y H:i') ?? '—' }}
                    @if($schedule->isDue())<span style="font-size:.68rem;"> ⚡ pendente</span>@endif
                </td>
                <td style="padding:12px 16px;font-size:.78rem;color:#475569;">{{ $schedule->last_run_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td style="padding:12px 16px;">
                    <span style="background:{{ $schedule->active ? '#052e16' : '#1e293b' }};color:{{ $schedule->active ? '#4ade80' : '#94a3b8' }};font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:10px;">
                        {{ $schedule->active ? 'Ativo' : 'Pausado' }}
                    </span>
                </td>
                <td style="padding:12px 16px;text-align:right;">
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <form method="POST" action="{{ route('admin.ai-schedules.toggle', $schedule) }}">@csrf
                            <button type="submit" style="background:#1e293b;color:#94a3b8;font-size:.75rem;padding:5px 12px;border-radius:6px;border:none;cursor:pointer;">
                                {{ $schedule->active ? 'Pausar' : 'Ativar' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.ai-schedules.edit', $schedule) }}" style="background:#1e293b;color:#6b8fff;font-size:.75rem;padding:5px 12px;border-radius:6px;text-decoration:none;">Editar</a>
                        <form method="POST" action="{{ route('admin.ai-schedules.destroy', $schedule) }}" onsubmit="return confirm('Remover agendamento?')">@csrf @method('DELETE')
                            <button type="submit" style="background:#2d0a0a;color:#f87171;font-size:.75rem;padding:5px 12px;border-radius:6px;border:none;cursor:pointer;">Remover</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:48px;text-align:center;color:#475569;font-size:.875rem;">Nenhum agendamento cadastrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($schedules->hasPages())
<div style="margin-top:20px;">{{ $schedules->links() }}</div>
@endif

</x-layouts.app>
