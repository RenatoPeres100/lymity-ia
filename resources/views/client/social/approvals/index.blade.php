<x-layouts.app>
    <div style="padding:2rem;">
        <div style="margin-bottom:2rem;">
            <h1 style="font-size:1.8rem;font-weight:700;color:#f1f5f9;">Aprovações de Posts</h1>
            <p style="color:#94a3b8;">Posts aguardando sua aprovação</p>
        </div>

        @if(session('success'))
        <div style="background:#10b98120;border:1px solid #10b981;color:#10b981;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        <div style="display:grid;gap:1rem;">
            @forelse($approvals as $approval)
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                    <div>
                        <h3 style="color:#f1f5f9;font-size:1rem;font-weight:600;">{{ $approval->title }}</h3>
                        <p style="color:#64748b;font-size:.8rem;margin-top:.25rem;">{{ $approval->created_at->diffForHumans() }}</p>
                    </div>
                    <span style="font-size:.75rem;padding:.2rem .6rem;border-radius:9999px;background:{{ $approval->status_badge_color }}20;color:{{ $approval->status_badge_color }};">{{ ucfirst($approval->status) }}</span>
                </div>

                @if($approval->description)
                <p style="color:#94a3b8;font-size:.85rem;line-height:1.6;margin-bottom:1rem;white-space:pre-wrap;">{{ Str::limit($approval->description, 300) }}</p>
                @endif

                @if($approval->isPending())
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('client.social.approvals.approve', $approval) }}">
                        @csrf
                        <input type="hidden" name="notes" value="Aprovado pelo cliente.">
                        <button style="background:#10b981;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">✓ Aprovar</button>
                    </form>
                    <form method="POST" action="{{ route('client.social.approvals.reject', $approval) }}">
                        @csrf
                        <input type="hidden" name="notes" value="Rejeitado pelo cliente.">
                        <button style="background:#ef4444;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">✗ Rejeitar</button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:3rem;text-align:center;">
                <p style="color:#64748b;">Nenhuma aprovação pendente.</p>
            </div>
            @endforelse
        </div>
        <div style="margin-top:1rem;">{{ $approvals->links() }}</div>
    </div>
</x-layouts.app>
