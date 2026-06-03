<x-layouts.app title="Aprovação #{{ $approvalRequest->id }}">

<div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-start;">
    <div>
        <a href="{{ route('admin.approvals.index') }}" style="color:#4f46e5;font-size:.8rem;text-decoration:none;">← Voltar para Aprovações</a>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-top:8px;">
            {{ $approvalRequest->isCritical() ? '🔴 ' : '' }}{{ $approvalRequest->title }}
        </h1>
        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
            <span style="background:{{ $approvalRequest->status_badge_color }};color:#fff;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approvalRequest->status_label }}</span>
            <span style="background:{{ $approvalRequest->sensitive_badge_color }};color:#fff;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approvalRequest->sensitive_level_label }}</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approvalRequest->approval_type_label }}</span>
        </div>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#b91c1c;font-size:.875rem;">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    {{-- Main --}}
    <div>
        {{-- Details --}}
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:20px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Detalhes</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Cliente</div>
                    <div style="font-size:.875rem;color:#334155;">{{ $approvalRequest->client?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Solicitado por</div>
                    <div style="font-size:.875rem;color:#334155;">{{ $approvalRequest->requester?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Funcionário IA</div>
                    <div style="font-size:.875rem;color:#334155;">{{ $approvalRequest->aiEmployee?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Vencimento</div>
                    <div style="font-size:.875rem;color:{{ $approvalRequest->isOverdue() ? '#dc2626' : '#475569' }};">
                        {{ $approvalRequest->due_at ? $approvalRequest->due_at->format('d/m/Y H:i') : '—' }}
                        @if($approvalRequest->isOverdue()) <span style="color:#dc2626;">(vencida)</span> @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Criado em</div>
                    <div style="font-size:.875rem;color:#334155;">{{ $approvalRequest->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($approvalRequest->isApproved())
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Aprovado por</div>
                    <div style="font-size:.875rem;color:#16a34a;">{{ $approvalRequest->approvedBy?->name ?? '—' }} em {{ $approvalRequest->approved_at?->format('d/m/Y H:i') }}</div>
                </div>
                @endif
                @if($approvalRequest->isRejected())
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Rejeitado por</div>
                    <div style="font-size:.875rem;color:#dc2626;">{{ $approvalRequest->rejectedBy?->name ?? '—' }} em {{ $approvalRequest->rejected_at?->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>

            @if($approvalRequest->description)
            <div style="margin-top:16px;">
                <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Descrição</div>
                <div style="font-size:.875rem;color:#334155;background:#f8fafc;border-radius:8px;padding:12px;line-height:1.6;">{{ $approvalRequest->description }}</div>
            </div>
            @endif

            @if($approvalRequest->payload)
            <div style="margin-top:16px;">
                <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Payload</div>
                <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;color:#475569;font-size:.75rem;overflow-x:auto;white-space:pre-wrap;">{{ json_encode($approvalRequest->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif

            @if($approvalRequest->approvable)
            <div style="margin-top:16px;">
                <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Item Relacionado</div>
                <div style="font-size:.875rem;color:#334155;background:#f8fafc;border-radius:8px;padding:12px;">
                    <span style="color:#64748b;">Tipo:</span> {{ class_basename($approvalRequest->approvable_type) }}
                    <span style="color:#64748b;margin-left:12px;">ID:</span> #{{ $approvalRequest->approvable_id }}
                    @if(isset($approvalRequest->approvable->title))
                    <span style="color:#64748b;margin-left:12px;">Título:</span> {{ $approvalRequest->approvable->title }}
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Actions Form --}}
        @if($approvalRequest->isPending())
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:20px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Tomar Decisão</h2>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:.8rem;font-weight:600;color:#64748b;margin-bottom:6px;">Observações (opcional)</label>
                <textarea id="notesField" rows="2" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;" placeholder="Adicione um comentário sobre sua decisão..."></textarea>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.approvals.approve', $approvalRequest->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="notes" id="notesApprove">
                    <button type="submit" onclick="document.getElementById('notesApprove').value=document.getElementById('notesField').value" style="background:#16a34a;color:#fff;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;border:none;cursor:pointer;">✓ Aprovar</button>
                </form>
                <form method="POST" action="{{ route('admin.approvals.reject', $approvalRequest->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="notes" id="notesReject">
                    <button type="submit" onclick="document.getElementById('notesReject').value=document.getElementById('notesField').value" style="background:#dc2626;color:#fff;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;border:none;cursor:pointer;">✕ Rejeitar</button>
                </form>
                <form method="POST" action="{{ route('admin.approvals.request-changes', $approvalRequest->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="notes" id="notesChanges">
                    <button type="submit" onclick="document.getElementById('notesChanges').value=document.getElementById('notesField').value" style="background:#d97706;color:#fff;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;border:none;cursor:pointer;">↩ Pedir Alteração</button>
                </form>
                <form method="POST" action="{{ route('admin.approvals.cancel', $approvalRequest->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="notes" id="notesCancel">
                    <button type="submit" onclick="document.getElementById('notesCancel').value=document.getElementById('notesField').value" style="background:#f1f5f9;color:#64748b;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;border:1px solid #e2e8f0;cursor:pointer;">Cancelar</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Comment Form --}}
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:20px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Comentários ({{ $approvalRequest->comments->count() }})</h2>

            @forelse($approvalRequest->comments as $comment)
            <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.8rem;font-weight:600;color:#1e293b;">{{ $comment->user?->name ?? 'Sistema' }}</span>
                    <span style="font-size:.72rem;color:#94a3b8;">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p style="font-size:.875rem;color:#475569;margin:0;line-height:1.5;">{{ $comment->comment }}</p>
            </div>
            @empty
            <p style="font-size:.875rem;color:#94a3b8;">Nenhum comentário ainda.</p>
            @endforelse

            <form method="POST" action="{{ route('admin.approvals.comments', $approvalRequest->id) }}" style="margin-top:16px;">
                @csrf
                <textarea name="comment" rows="3" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;margin-bottom:10px;" placeholder="Adicione um comentário..."></textarea>
                <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;border:none;cursor:pointer;">Comentar</button>
            </form>
        </div>
    </div>

    {{-- Sidebar: History --}}
    <div>
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Histórico de Ações</h2>
            @forelse($approvalRequest->actions as $action)
            <div style="display:flex;gap:10px;margin-bottom:14px;">
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $action->action_color }};margin-top:5px;flex-shrink:0;"></div>
                <div>
                    <div style="font-size:.8rem;font-weight:600;color:#334155;">{{ $action->action_label }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $action->user?->name ?? 'Sistema' }}</div>
                    @if($action->notes)
                    <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;font-style:italic;">"{{ Str::limit($action->notes, 80) }}"</div>
                    @endif
                    <div style="font-size:.7rem;color:#94a3b8;margin-top:2px;">{{ $action->created_at?->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            @empty
            <p style="font-size:.8rem;color:#94a3b8;">Sem histórico.</p>
            @endforelse
        </div>

        {{-- Email Notifications Section --}}
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-top:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;">Notificações por E-mail</h2>
                @if($approvalRequest->isPending())
                <form method="POST" action="{{ route('admin.approvals.resend-email', $approvalRequest) }}" style="margin:0;">
                    @csrf
                    <button type="submit" style="background:#4f46e5;color:#fff;border:none;padding:7px 16px;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;">
                        Reenviar e-mail de aprovação
                    </button>
                </form>
                @endif
            </div>

            {{-- Status summary --}}
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
                @php
                    $emailStatus = $approvalRequest->notification_status ?? 'not_sent';
                    $statusColors = [
                        'sent'     => ['bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534', 'label' => 'Enviado'],
                        'failed'   => ['bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#b91c1c', 'label' => 'Falha'],
                        'not_sent' => ['bg' => '#f8fafc', 'border' => '#e2e8f0', 'text' => '#64748b', 'label' => 'Não enviado'],
                        'disabled' => ['bg' => '#f8fafc', 'border' => '#e2e8f0', 'text' => '#94a3b8', 'label' => 'Desabilitado'],
                    ];
                    $sc = $statusColors[$emailStatus] ?? $statusColors['not_sent'];
                @endphp
                <span style="background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};color:{{ $sc['text'] }};font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">
                    Status: {{ $sc['label'] }}
                </span>
                @if($approvalRequest->notified_at)
                <span style="background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;font-size:.75rem;padding:4px 12px;border-radius:12px;">
                    Notificado em: {{ $approvalRequest->notified_at->format('d/m/Y H:i') }}
                </span>
                @endif
                @if($approvalRequest->reminder_count > 0)
                <span style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">
                    Lembretes enviados: {{ $approvalRequest->reminder_count }}
                </span>
                @endif
            </div>

            {{-- Email log entries --}}
            @php $emailLogs = $approvalRequest->emailNotifications()->limit(10)->get(); @endphp
            @if($emailLogs->isNotEmpty())
            <div style="border:1px solid #f1f5f9;border-radius:8px;overflow:hidden;">
                <table style="width:100%;font-size:.75rem;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:8px 12px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Destinatário</th>
                            <th style="padding:8px 12px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Tipo</th>
                            <th style="padding:8px 12px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Status</th>
                            <th style="padding:8px 12px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emailLogs as $log)
                        @php
                            $logColors = [
                                'sent'    => '#166534',
                                'failed'  => '#b91c1c',
                                'pending' => '#92400e',
                                'skipped' => '#64748b',
                            ];
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px 12px;color:#334155;">{{ $log->email }}</td>
                            <td style="padding:8px 12px;color:#64748b;">{{ ucfirst($log->notification_type) }}</td>
                            <td style="padding:8px 12px;">
                                <span style="color:{{ $logColors[$log->status] ?? '#64748b' }};font-weight:600;">{{ ucfirst($log->status) }}</span>
                                @if($log->status === 'failed' && $log->error_message)
                                <div style="color:#94a3b8;font-size:.7rem;margin-top:2px;">{{ Str::limit($log->error_message, 60) }}</div>
                                @endif
                            </td>
                            <td style="padding:8px 12px;color:#94a3b8;">{{ ($log->sent_at ?? $log->failed_at ?? $log->created_at)?->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p style="font-size:.8rem;color:#94a3b8;">Nenhuma tentativa de e-mail registrada.</p>
            @endif
        </div>
    </div>

</div>

</x-layouts.app>
