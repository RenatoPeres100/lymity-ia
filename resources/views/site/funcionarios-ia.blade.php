<x-layouts.public title="Funcionários IA" meta-description="Conheça os 8 Funcionários IA da Lymity: Social Media, Copywriter, SEO, Tráfego, Analytics, Automação, CRM e Desenvolvimento — operando com aprovação humana.">

<section class="section" style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);min-height:44vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px 64px;">
        <div style="max-width:680px;">
            <div class="section-label" style="background:rgba(74,108,247,.1);border-color:rgba(74,108,247,.25);">Funcionários IA</div>
            <h1 style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.025em;margin-bottom:16px;">
                Agentes especializados.<br>Operando todos os dias.
            </h1>
            <p style="color:#94a3b8;font-size:1.05rem;line-height:1.75;">Cada Funcionário IA tem função específica, habilidades mapeadas e rotinas definidas — com supervisão humana obrigatória nos momentos críticos.</p>
        </div>
    </div>
</section>

@if($aiEmployees->isNotEmpty())
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;">
            @foreach($aiEmployees as $employee)
            <div class="pub-card" style="padding:32px;">
                <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:20px;">
                    <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#eff6ff,#f0fdf4);border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;">
                        {{ $employee->avatar_emoji }}
                    </div>
                    <div>
                        <div style="font-size:1.05rem;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $employee->name }}</div>
                        <div style="font-size:.78rem;font-weight:600;color:#4a6cf7;text-transform:uppercase;letter-spacing:.06em;">{{ $employee->role }}</div>
                    </div>
                </div>
                <p style="font-size:.875rem;color:#64748b;line-height:1.75;margin-bottom:20px;">{{ $employee->description }}</p>
                @if($employee->skills->isNotEmpty())
                <div>
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:10px;">Habilidades</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach($employee->skills->take(6) as $skill)
                        <span style="background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;border:1px solid #e2e8f0;">{{ $skill->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;display:flex;align-items:center;gap:8px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $employee->status === 'active' ? '#22c55e' : '#94a3b8' }};"></div>
                    <span style="font-size:.75rem;color:#94a3b8;font-weight:500;">{{ $employee->getStatusLabelAttribute() }}</span>
                    @if($employee->approval_required)
                    <span style="margin-left:auto;font-size:.7rem;background:#fff7ed;color:#f97316;border:1px solid #fed7aa;padding:2px 8px;border-radius:10px;font-weight:600;">Aprovação obrigatória</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Como funcionam --}}
<section class="section" style="background:#f8fafc;">
    <div class="container" style="max-width:860px;">
        <div style="text-align:center;margin-bottom:48px;">
            <div class="section-label">Como funcionam</div>
            <h2 class="section-title">IA com responsabilidade.<br>Execução com rastreabilidade.</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;">
            @foreach([
                ['⚙️','Rotinas definidas','Cada agente tem tarefas diárias, semanais e mensais configuradas com base no seu plano e contexto.'],
                ['✅','Aprovação humana','Toda ação que afeta o mundo externo — publicação, envio, alteração — exige aprovação antes de executar.'],
                ['📋','Log completo','Cada ação gera um registro: o que foi feito, quando, com qual input e qual output.'],
                ['📈','Melhoria contínua','Com dados acumulados, os agentes refinam abordagens e melhoram resultados ao longo do tempo.'],
            ] as [$icon,$title,$desc])
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
                <div style="font-size:1.5rem;margin-bottom:10px;">{{ $icon }}</div>
                <div style="font-size:.875rem;font-weight:600;color:#0f172a;margin-bottom:6px;">{{ $title }}</div>
                <div style="font-size:.8rem;color:#64748b;line-height:1.6;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section-dark">
    <div class="container" style="text-align:center;max-width:600px;">
        <h2 class="section-title">Quer os Funcionários IA trabalhando pela sua empresa?</h2>
        <p class="section-subtitle" style="margin:0 auto 32px;">Solicite um diagnóstico gratuito e veja quais agentes fazem sentido para o seu negócio.</p>
        <a href="{{ route('contato') }}" class="pub-btn-primary">Solicitar diagnóstico gratuito →</a>
    </div>
</section>

</x-layouts.public>
