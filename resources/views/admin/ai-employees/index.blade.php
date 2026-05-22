<x-layouts.app title="Funcionários IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:4px;">Funcionários IA</h1>
        <p style="font-size:.85rem;color:#64748b;">Agentes de inteligência artificial da plataforma.</p>
    </div>
    <div style="font-size:.8rem;color:#64748b;background:#0f172a;border:1px solid #1e293b;padding:8px 16px;border-radius:8px;">
        {{ $employees->where('status','active')->count() }} ativos de {{ $employees->count() }}
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
    @forelse($employees as $employee)
    <a href="{{ route('admin.ai-employees.show', $employee) }}" style="text-decoration:none;">
        <div style="background:#111827;border:1px solid #1e293b;border-radius:14px;padding:24px;cursor:pointer;transition:border-color .2s;" onmouseover="this.style.borderColor='#4a6cf7'" onmouseout="this.style.borderColor='#1e293b'">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#1e3a5f,#1a1a40);display:flex;align-items:center;justify-content:center;font-size:1.3rem;">{{ $employee->avatar_emoji }}</div>
                    <div>
                        <div style="font-size:.9rem;font-weight:700;color:#e2e8f0;">{{ $employee->name }}</div>
                        <div style="font-size:.72rem;color:#6b8fff;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">{{ $employee->role }}</div>
                    </div>
                </div>
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $employee->status === 'active' ? '#22c55e' : '#475569' }};margin-top:4px;flex-shrink:0;"></div>
            </div>
            <p style="font-size:.8rem;color:#94a3b8;line-height:1.6;margin-bottom:14px;">{{ mb_substr($employee->description, 0, 100) }}...</p>
            <div style="display:flex;flex-wrap:wrap;gap:5px;">
                @foreach($employee->skills->take(3) as $skill)
                <span style="background:#0f172a;color:#64748b;font-size:.68rem;padding:2px 8px;border-radius:10px;border:1px solid #1e293b;">{{ $skill->name }}</span>
                @endforeach
                @if($employee->skills->count() > 3)
                <span style="background:#0f172a;color:#475569;font-size:.68rem;padding:2px 8px;border-radius:10px;border:1px solid #1e293b;">+{{ $employee->skills->count() - 3 }}</span>
                @endif
            </div>
        </div>
    </a>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:48px;color:#475569;font-size:.875rem;">Nenhum funcionário IA cadastrado.</div>
    @endforelse
</div>

</x-layouts.app>
