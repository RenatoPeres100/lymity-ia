<x-layouts.public title="Contato" meta-description="Solicite um diagnóstico gratuito e descubra como a Lymity pode transformar sua operação digital com IA e estratégia integrada.">

<section class="section" style="background:linear-gradient(160deg,#0f1117,#0d1c54,#0f1117);min-height:36vh;display:flex;align-items:center;">
    <div class="container" style="padding:80px 32px 64px;">
        <div style="max-width:640px;">
            <div class="section-label" style="background:rgba(74,108,247,.1);border-color:rgba(74,108,247,.25);">Contato</div>
            <h1 style="font-size:clamp(2rem,4vw,3rem);font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.025em;margin-bottom:16px;">
                Solicite um diagnóstico gratuito.
            </h1>
            <p style="color:#94a3b8;font-size:1rem;line-height:1.75;">Preencha o formulário e nossa equipe entrará em contato em até 24 horas para entender o seu contexto e propor a melhor estrutura de operação.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:860px;">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:56px;align-items:start;">

            {{-- Form --}}
            <div>
                @if(session('success'))
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px 24px;margin-bottom:28px;display:flex;gap:12px;align-items:flex-start;">
                    <span style="font-size:1.2rem;">✅</span>
                    <div>
                        <div style="font-weight:700;color:#166534;margin-bottom:4px;">Mensagem enviada!</div>
                        <div style="font-size:.875rem;color:#15803d;">{{ session('success') }}</div>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:20px 24px;margin-bottom:28px;">
                    <div style="font-weight:700;color:#991b1b;margin-bottom:8px;">Por favor, corrija os erros abaixo:</div>
                    <ul style="list-style:none;padding:0;margin:0;">
                        @foreach($errors->all() as $error)
                        <li style="font-size:.875rem;color:#dc2626;padding:2px 0;">• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('contato.store') }}" method="POST" style="display:flex;flex-direction:column;gap:20px;">
                    @csrf

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Nome <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Seu nome completo" required
                                style="width:100%;padding:12px 16px;border:1.5px solid {{ $errors->has('name') ? '#ef4444' : '#d1d5db' }};border-radius:10px;font-size:.875rem;color:#0f172a;background:#fff;outline:none;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='{{ $errors->has('name') ? '#ef4444' : '#d1d5db' }}'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">E-mail <span style="color:#ef4444;">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required
                                style="width:100%;padding:12px 16px;border:1.5px solid {{ $errors->has('email') ? '#ef4444' : '#d1d5db' }};border-radius:10px;font-size:.875rem;color:#0f172a;background:#fff;outline:none;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='{{ $errors->has('email') ? '#ef4444' : '#d1d5db' }}'">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Empresa</label>
                            <input type="text" name="company" value="{{ old('company') }}" placeholder="Nome da empresa"
                                style="width:100%;padding:12px 16px;border:1.5px solid #d1d5db;border-radius:10px;font-size:.875rem;color:#0f172a;background:#fff;outline:none;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Telefone</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="(11) 99999-9999"
                                style="width:100%;padding:12px 16px;border:1.5px solid #d1d5db;border-radius:10px;font-size:.875rem;color:#0f172a;background:#fff;outline:none;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                    </div>

                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Serviços de interesse</label>
                        <select name="service_interest" style="width:100%;padding:12px 16px;border:1.5px solid #d1d5db;border-radius:10px;font-size:.875rem;color:#374151;background:#fff;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='#d1d5db'">
                            <option value="">Selecione...</option>
                            <option value="trafego_pago" {{ old('service_interest') === 'trafego_pago' ? 'selected' : '' }}>Tráfego Pago</option>
                            <option value="seo" {{ old('service_interest') === 'seo' ? 'selected' : '' }}>SEO</option>
                            <option value="automacao" {{ old('service_interest') === 'automacao' ? 'selected' : '' }}>Automação</option>
                            <option value="conteudo" {{ old('service_interest') === 'conteudo' ? 'selected' : '' }}>Conteúdo</option>
                            <option value="desenvolvimento" {{ old('service_interest') === 'desenvolvimento' ? 'selected' : '' }}>Desenvolvimento</option>
                            <option value="plataforma_completa" {{ old('service_interest') === 'plataforma_completa' ? 'selected' : '' }}>Plataforma Completa</option>
                            <option value="outro" {{ old('service_interest') === 'outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Mensagem <span style="color:#ef4444;">*</span></label>
                        <textarea name="message" rows="5" placeholder="Conte um pouco sobre seu negócio e o que você está buscando..." required
                            style="width:100%;padding:12px 16px;border:1.5px solid {{ $errors->has('message') ? '#ef4444' : '#d1d5db' }};border-radius:10px;font-size:.875rem;color:#0f172a;background:#fff;outline:none;resize:vertical;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#4a6cf7'" onblur="this.style.borderColor='{{ $errors->has('message') ? '#ef4444' : '#d1d5db' }}'">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="pub-btn-primary" style="align-self:flex-start;padding:14px 36px;font-size:1rem;cursor:pointer;border:none;">
                        Enviar mensagem →
                    </button>
                </form>
            </div>

            {{-- Info --}}
            <div style="position:sticky;top:100px;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:28px;margin-bottom:20px;">
                    <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:16px;">O que acontece depois?</h3>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        @foreach([
                            ['1','Análise do formulário','Revisamos as informações enviadas em até 24h.'],
                            ['2','Contato da equipe','Um especialista entra em contato para entender melhor o contexto.'],
                            ['3','Diagnóstico gratuito','Apresentamos uma análise da sua operação atual e oportunidades de crescimento.'],
                        ] as [$num,$title,$desc])
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div style="width:24px;height:24px;border-radius:50%;background:#eff6ff;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.72rem;font-weight:800;color:#4a6cf7;">{{ $num }}</div>
                            <div>
                                <div style="font-size:.8rem;font-weight:600;color:#0f172a;margin-bottom:2px;">{{ $title }}</div>
                                <div style="font-size:.75rem;color:#64748b;line-height:1.5;">{{ $desc }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="background:linear-gradient(135deg,#0f1117,#0d1c54);border-radius:16px;padding:24px;text-align:center;">
                    <div style="font-size:1.6rem;margin-bottom:10px;">🚀</div>
                    <div style="font-size:.9rem;font-weight:700;color:#fff;margin-bottom:6px;">Diagnóstico 100% gratuito</div>
                    <div style="font-size:.78rem;color:#94a3b8;line-height:1.6;">Sem compromisso. Sem pressão. Só uma análise honesta do potencial da sua operação.</div>
                </div>
            </div>

        </div>
    </div>
</section>

</x-layouts.public>
