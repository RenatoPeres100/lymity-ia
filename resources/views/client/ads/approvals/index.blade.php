<x-layouts.app title="Aprovações de Ads">

<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:#1e293b;">Aprovações de Mídia Paga</h2>
    <p style="font-size:.875rem;color:#64748b;margin-top:.25rem;">Aprovações de campanhas e alterações de orçamento</p>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    @forelse($approvals as $ap)
    <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $ap->title }}</div>
                @if($ap->description)<div style="font-size:.8125rem;color:#64748b;margin-top:.25rem;">{{ $ap->description }}</div>@endif
                <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">{{ $ap->created_at->format('d/m/Y H:i') }} · {{ ucfirst($ap->approval_type) }}</div>
            </div>
            <span style="background:{{ $ap->status === 'approved' ? '#dcfce7' : ($ap->status === 'pending' ? '#fef9c3' : '#fee2e2') }};color:{{ $ap->status === 'approved' ? '#166534' : ($ap->status === 'pending' ? '#92400e' : '#991b1b') }};padding:.25rem .625rem;border-radius:.25rem;font-size:.75rem;font-weight:500;white-space:nowrap;">
                {{ match($ap->status) { 'pending'=>'Pendente', 'approved'=>'Aprovado', 'rejected'=>'Rejeitado', 'changes_requested'=>'Revisão', default=>$ap->status } }}
            </span>
        </div>
    </div>
    @empty
    <div style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">Nenhuma aprovação de mídia paga encontrada.</div>
    @endforelse
    @if($approvals->hasPages())
    <div style="padding:1rem;border-top:1px solid #f1f5f9;">{{ $approvals->links() }}</div>
    @endif
</div>

</x-layouts.app>
