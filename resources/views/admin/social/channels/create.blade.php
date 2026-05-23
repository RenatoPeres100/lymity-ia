<x-layouts.app>
    <div style="padding:2rem;max-width:600px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.channels.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Voltar</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#f1f5f9;margin-top:.5rem;">Novo Canal</h1>
        </div>

        <div style="background:#f59e0b20;border:1px solid #f59e0b;color:#f59e0b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1.5rem;font-size:.85rem;">
            ⚠️ Tokens de acesso são gerenciados de forma segura e nunca exibidos neste painel.
        </div>

        @if($errors->any())
        <div style="background:#ef444420;border:1px solid #ef4444;color:#ef4444;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.channels.store') }}" style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:2rem;">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Plataforma *</label>
                    <select name="platform" required style="width:100%;background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        @foreach(['instagram'=>'Instagram','facebook'=>'Facebook','linkedin'=>'LinkedIn','tiktok'=>'TikTok','threads'=>'Threads','youtube'=>'YouTube','pinterest'=>'Pinterest'] as $v=>$l)
                        <option value="{{ $v }}" @selected(old('platform')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Nome *</label>
                    <input type="text" name="account_name" value="{{ old('account_name') }}" required placeholder="Ex: Instagram Lymity" style="width:100%;background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Handle / @</label>
                    <input type="text" name="handle" value="{{ old('handle') }}" placeholder="@lymity_ia" style="width:100%;background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Cliente</label>
                    <select name="client_id" style="width:100%;background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="">— Agência —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="color:#94a3b8;font-size:.85rem;display:block;margin-bottom:.5rem;">Status</label>
                    <select name="status" style="width:100%;background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="active">Ativo</option>
                        <option value="paused">Pausado</option>
                        <option value="disconnected">Desconectado</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">Criar Canal</button>
                <a href="{{ route('admin.social.channels.index') }}" style="background:#334155;color:#94a3b8;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
