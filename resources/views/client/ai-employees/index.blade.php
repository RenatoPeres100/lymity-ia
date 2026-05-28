<x-layouts.app title="Funcionários IA">
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;">Funcionários IA</h1>
    <p style="color:#64748b;font-size:.875rem;margin-top:4px;">Agentes de IA que trabalham para o seu negócio.</p>
</div>
@if($employees->isEmpty())
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:40px;text-align:center;color:#64748b;">
    <div style="font-size:1rem;font-weight:600;margin-bottom:8px;">Nenhum funcionário IA ativo</div>
    <p style="font-size:.875rem;">A agência configurará os funcionários IA para o seu negócio.</p>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
    @foreach($employees as $emp)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
        <div style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $emp->name }}</div>
        <div style="font-size:.8rem;color:#64748b;margin-bottom:8px;">{{ $emp->role ?? $emp->function ?? '—' }}</div>
        <span style="background:#eff6ff;color:#2563eb;font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:5px;">Ativo</span>
    </div>
    @endforeach
</div>
@endif
</x-layouts.app>
