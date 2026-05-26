<x-layouts.app title="Novo Case">

<div style="margin-bottom:28px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Novo Case</h1>
    <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.cases.index') }}" style="color:#6b8fff;text-decoration:none;">Cases</a> / Criar</p>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;">
    @foreach($errors->all() as $error)<div style="color:#f87171;font-size:.8rem;padding:2px 0;">• {{ $error }}</div>@endforeach
</div>
@endif

<form action="{{ route('admin.cases.store') }}" method="POST">
    @csrf
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="table-wrapper" style="padding:24px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Título *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                    </div>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Nome do Cliente</label>
                        <input type="text" name="client_name" value="{{ old('client_name') }}"
                            style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Desafio *</label>
                    <textarea name="challenge" rows="3" required
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;resize:vertical;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('challenge') }}</textarea>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Solução</label>
                    <textarea name="solution" rows="3"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;resize:vertical;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('solution') }}</textarea>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Tags</label>
                    <input type="text" name="tags" value="{{ old('tags') }}" placeholder="trafego, seo, automacao"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                </div>
            </div>

            <div class="table-wrapper" style="padding:24px;">
                <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Depoimento</div>
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Depoimento</label>
                    <textarea name="testimonial" rows="3"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;resize:vertical;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('testimonial') }}</textarea>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Autor do depoimento</label>
                    <input type="text" name="testimonial_author" value="{{ old('testimonial_author') }}" placeholder="Nome, Cargo — Empresa"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="table-wrapper" style="padding:24px;">
                <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Detalhes</div>
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Segmento</label>
                    <input type="text" name="industry" value="{{ old('industry') }}" placeholder="E-commerce, SaaS..."
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;">Data de publicação</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                        style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.875rem;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">
                </div>
            </div>

            <div class="table-wrapper" style="padding:24px;">
                <div style="font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Resultados (métricas)</div>
                <div style="font-size:.75rem;color:#475569;margin-bottom:12px;">Formato: "Métrica: Valor" — uma por linha.</div>
                <textarea name="results_raw" rows="5" placeholder="Crescimento de tráfego: +320%&#10;Leads gerados: 1.240&#10;ROI: 4.2x"
                    style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;color:#334155;font-size:.8rem;outline:none;resize:vertical;box-sizing:border-box;font-family:monospace;"
                    onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#1e293b'">{{ old('results_raw') }}</textarea>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                <button type="submit" class="btn-primary" style="width:100%;text-align:center;cursor:pointer;border:none;">Salvar Case</button>
                <a href="{{ route('admin.cases.index') }}" style="text-align:center;font-size:.875rem;color:#64748b;text-decoration:none;padding:10px;">Cancelar</a>
            </div>
        </div>
    </div>
</form>

</x-layouts.app>
