<x-layouts.app title="Funcionários IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Funcionários IA</h1>
        <p style="font-size:.85rem;color:#64748b;">Agentes de inteligência artificial da plataforma.</p>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
        <div style="font-size:.8rem;color:#64748b;background:#f1f5f9;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;">
            {{ $employees->where('status','active')->count() }} ativos de {{ $employees->count() }}
        </div>
        <a href="{{ route('admin.ai-employees.create') }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;text-decoration:none;">+ Novo Funcionário</a>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#991b1b;font-size:.875rem;">✗ {{ session('error') }}</div>
@endif

<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
    <label style="display:flex;align-items:center;gap:6px;font-size:.82rem;color:#64748b;cursor:pointer;">
        <input type="checkbox" id="bulk-select-all" style="accent-color:#6366f1;width:15px;height:15px;">
        Selecionar todos
    </label>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
    @forelse($employees as $employee)
    <div style="position:relative;">
        <label style="position:absolute;top:12px;left:12px;z-index:10;cursor:pointer;" onclick="event.stopPropagation()">
            <input type="checkbox" class="bulk-cb" value="{{ $employee->id }}" style="accent-color:#6366f1;width:16px;height:16px;">
        </label>
        <a href="{{ route('admin.ai-employees.show', $employee) }}" style="text-decoration:none;">
        <div style="background:#111827;border:1px solid #1e293b;border-radius:14px;padding:24px;padding-left:36px;cursor:pointer;transition:border-color .2s;" onmouseover="this.style.borderColor='#4a6cf7'" onmouseout="this.style.borderColor='#1e293b'">
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
                <span style="background:#1e293b;color:#94a3b8;font-size:.68rem;padding:2px 8px;border-radius:10px;border:1px solid #334155;">{{ $skill->name }}</span>
                @endforeach
                @if($employee->skills->count() > 3)
                <span style="background:#1e293b;color:#64748b;font-size:.68rem;padding:2px 8px;border-radius:10px;border:1px solid #334155;">+{{ $employee->skills->count() - 3 }}</span>
                @endif
            </div>
        </div>
        </a>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:48px;color:#475569;font-size:.875rem;">Nenhum funcionário IA cadastrado.</div>
    @endforelse
</div>

<x-bulk-actions :action="route('admin.ai-employees.bulk-delete')" :confirmMsg="'Excluir os funcionários IA selecionados?'" />

</x-layouts.app>
