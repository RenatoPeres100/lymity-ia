<x-layouts.app>
    <div style="padding:2rem;max-width:600px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.calendar.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Voltar</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">Novo Calendário</h1>
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.calendar.store') }}" style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:2rem;">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Mês *</label>
                    <select name="month" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        @for($m=1;$m<=12;$m++)
                        <option value="{{ $m }}" @selected(old('month',now()->month)==$m)>{{ \Carbon\Carbon::createFromDate(now()->year,$m,1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Ano *</label>
                    <input type="number" name="year" value="{{ old('year', now()->year) }}" min="2024" max="2030" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Cliente</label>
                    <select name="client_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="">— Agência —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Status</label>
                    <select name="status" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="draft">Rascunho</option>
                        <option value="active">Ativo</option>
                        <option value="archived">Arquivado</option>
                    </select>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Resumo estratégico</label>
                    <textarea name="strategy_summary" rows="4" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('strategy_summary') }}</textarea>
                </div>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">Criar Calendário</button>
                <a href="{{ route('admin.social.calendar.index') }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
