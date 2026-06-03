<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mensagens IA</h3>
        <span class="text-xs text-slate-400">{{ $lead->messageSuggestions->count() }} gerada(s)</span>
    </div>
    <div class="card-body">
        @forelse($lead->messageSuggestions as $msg)
        <div class="p-4 border border-slate-200 rounded-xl mb-3 {{ $msg->status === 'approved' ? 'border-green-200 bg-green-50' : ($msg->status === 'rejected' ? 'border-red-100 bg-red-50 opacity-70' : 'bg-white') }}">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600">{{ $msg->channel_label }}</span>
                    <span class="text-xs text-slate-400">·</span>
                    <span class="text-xs text-slate-500">{{ $msg->objective_label }}</span>
                </div>
                <span class="badge text-xs {{ $msg->status === 'approved' ? 'badge-green' : ($msg->status === 'rejected' ? 'badge-red' : ($msg->status === 'used' ? 'badge-slate' : 'badge-yellow')) }}">
                    {{ $msg->status_label }}
                </span>
            </div>

            <div class="text-sm text-slate-800 bg-white rounded-lg p-3 border border-slate-100 font-mono leading-relaxed" id="msg-{{ $msg->id }}">{{ $msg->message }}</div>

            @if($msg->metadata && ($msg->metadata['tone'] || $msg->metadata['notes']))
            <div class="text-xs text-slate-400 mt-1">
                @if($msg->metadata['tone']) Tom: {{ $msg->metadata['tone'] }} @endif
                @if($msg->metadata['notes']) · {{ $msg->metadata['notes'] }} @endif
            </div>
            @endif

            <div class="flex items-center gap-2 mt-3 flex-wrap">
                {{-- Copiar --}}
                <button onclick="copyMessage({{ $msg->id }})" class="btn btn-ghost btn-sm text-slate-600">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    Copiar
                </button>

                @if(in_array($msg->status, ['draft', 'rejected']))
                @can('approveMessage', $msg)
                <form action="{{ route('admin.prospecting.messages.approve', $msg) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-ghost text-green-600">✓ Aprovar</button>
                </form>
                @endcan
                @endif

                @if(in_array($msg->status, ['draft', 'approved']))
                @can('approveMessage', $msg)
                <form action="{{ route('admin.prospecting.messages.reject', $msg) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-ghost text-red-500">✕ Rejeitar</button>
                </form>
                @endcan
                @endif

                @if($msg->status === 'approved')
                <form action="{{ route('admin.prospecting.messages.mark-used', $msg) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-ghost text-blue-600">Marcar como usada</button>
                </form>
                @endif

                <span class="text-xs text-slate-400 ml-auto">{{ $msg->created_at->format('d/m H:i') }}</span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-6">Nenhuma mensagem IA gerada ainda.<br>
            <span class="text-xs">Use o painel SDR IA para gerar mensagens personalizadas.</span>
        </p>
        @endforelse
    </div>
</div>

<script>
function copyMessage(id) {
    const el = document.getElementById('msg-' + id);
    if (!el) return;
    navigator.clipboard.writeText(el.innerText).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '✓ Copiado!';
        btn.classList.add('text-green-600');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('text-green-600'); }, 2000);
    });
}
</script>
