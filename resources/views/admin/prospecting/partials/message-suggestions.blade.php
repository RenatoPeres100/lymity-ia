<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Mensagens IA</h3>
        <span style="font-size:.75rem;color:#94a3b8;">{{ $lead->messageSuggestions->count() }} gerada(s)</span>
    </div>
    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;">
        @forelse($lead->messageSuggestions as $msg)
        <div style="border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;
            {{ $msg->status === 'approved' ? 'border-color:#bbf7d0;background:#f0fdf4;' : ($msg->status === 'rejected' ? 'background:#fef2f2;border-color:#fecaca;opacity:.8;' : 'background:#fff;') }}">

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
                <div style="display:flex;align-items:center;gap:.4rem;">
                    <span style="font-size:.75rem;font-weight:600;color:#374151;">{{ $msg->channel_label }}</span>
                    <span style="font-size:.7rem;color:#94a3b8;">·</span>
                    <span style="font-size:.75rem;color:#64748b;">{{ $msg->objective_label }}</span>
                </div>
                <span style="font-size:.7rem;padding:.15rem .4rem;border-radius:999px;font-weight:600;
                    {{ $msg->status === 'approved' ? 'background:#dcfce7;color:#16a34a;' : ($msg->status === 'rejected' ? 'background:#fef2f2;color:#dc2626;' : ($msg->status === 'used' ? 'background:#f1f5f9;color:#64748b;' : 'background:#fefce8;color:#a16207;')) }}">
                    {{ $msg->status_label }}
                </span>
            </div>

            <div id="msg-{{ $msg->id }}"
                 style="font-size:.875rem;color:#1e293b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.375rem;padding:.75rem;font-family:monospace;line-height:1.6;white-space:pre-wrap;word-break:break-word;">{{ $msg->message }}</div>

            @if($msg->metadata && !empty($msg->metadata['tone']))
            <div style="font-size:.7rem;color:#94a3b8;margin-top:.3rem;">Tom: {{ $msg->metadata['tone'] }}</div>
            @endif

            <div style="display:flex;align-items:center;gap:.4rem;margin-top:.75rem;flex-wrap:wrap;">

                {{-- Copiar --}}
                <button onclick="copyMsg({{ $msg->id }})"
                        style="background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;padding:.3rem .6rem;border-radius:.375rem;cursor:pointer;font-size:.75rem;display:flex;align-items:center;gap:.3rem;">
                    📋 Copiar
                </button>

                @if(in_array($msg->status, ['draft', 'rejected']))
                @can('approveMessage', $msg)
                <form action="{{ route('admin.prospecting.messages.approve', $msg) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit"
                            style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:.3rem .6rem;border-radius:.375rem;cursor:pointer;font-size:.75rem;">✓ Aprovar</button>
                </form>
                @endcan
                @endif

                @if(in_array($msg->status, ['draft', 'approved']))
                @can('approveMessage', $msg)
                <form action="{{ route('admin.prospecting.messages.reject', $msg) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit"
                            style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:.3rem .6rem;border-radius:.375rem;cursor:pointer;font-size:.75rem;">✕ Rejeitar</button>
                </form>
                @endcan
                @endif

                @if($msg->status === 'approved')
                <form action="{{ route('admin.prospecting.messages.mark-used', $msg) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit"
                            style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;padding:.3rem .6rem;border-radius:.375rem;cursor:pointer;font-size:.75rem;">Marcar como usada</button>
                </form>
                @endif

                <span style="font-size:.7rem;color:#94a3b8;margin-left:auto;">{{ $msg->created_at->format('d/m H:i') }}</span>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:2rem 0;color:#94a3b8;font-size:.875rem;">
            Nenhuma mensagem gerada ainda.<br>
            <span style="font-size:.8rem;">Use o painel SDR IA para gerar mensagens personalizadas.</span>
        </div>
        @endforelse
    </div>
</div>

<script>
function copyMsg(id) {
    var el = document.getElementById('msg-' + id);
    if (!el) return;
    navigator.clipboard.writeText(el.innerText).then(function() {
        var btn = event.target.closest('button');
        var orig = btn.innerHTML;
        btn.innerHTML = '✓ Copiado!';
        btn.style.color = '#16a34a';
        setTimeout(function() { btn.innerHTML = orig; btn.style.color = ''; }, 2000);
    });
}
</script>
