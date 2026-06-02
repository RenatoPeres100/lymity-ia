<x-layouts.app>
    <div style="padding:2rem;">
        <div style="margin-bottom:2rem;">
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;">Configuração Gemini — Imagens IA</h1>
            <p style="color:#64748b;">Status da configuração para geração de imagens com Gemini AI</p>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

            {{-- Status card --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1.25rem;">Status da Configuração</h3>

                @php
                    $checks = [
                        ['GEMINI_API_KEY configurada',   $geminiConfigured],
                        ['AI_PROVIDER = google',          config('ai.provider') === 'google'],
                        ['GEMINI_IMAGE_ENABLED = true',   $imageEnabled],
                        ['GEMINI_IMAGE_MODEL configurado',$imageModel !== null],
                        ['Storage link ativo',            file_exists(public_path('storage'))],
                    ];
                @endphp

                @foreach($checks as [$label, $ok])
                <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid #f1f5f9;">
                    @if($ok)
                    <svg width="18" height="18" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    <span style="font-size:.9rem;color:#374151;">{{ $label }}</span>
                    @else
                    <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    <span style="font-size:.9rem;color:#374151;">{{ $label }}</span>
                    @endif
                </div>
                @endforeach

                <form action="{{ route('admin.ai-images.settings.test') }}" method="POST" style="margin-top:1.25rem;">
                    @csrf
                    <button type="submit" style="background:#4f46e5;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;width:100%;">
                        Testar conexão Gemini
                    </button>
                </form>
            </div>

            {{-- Configuration values --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1.25rem;">Configurações Ativas</h3>

                @php
                    $configItems = [
                        'GEMINI_API_KEY'        => $geminiConfigured ? '******* (configurada)' : 'Não configurada',
                        'Modelo de texto'        => $textModel ?? 'Não configurado',
                        'Modelo de imagem'       => $imageModel ?? 'Não configurado',
                        'Geração de imagem'      => $imageEnabled ? 'Habilitada' : 'Desabilitada',
                        'Limite diário'          => $dailyLimit ?? 'Sem limite',
                        'Limite mensal'          => $monthlyLimit ?? 'Sem limite',
                        'Geradas hoje'           => $todayCount,
                        'Geradas este mês'       => $monthCount,
                    ];
                @endphp

                @foreach($configItems as $k => $v)
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:.85rem;color:#64748b;">{{ $k }}</span>
                    <span style="font-size:.85rem;color:#374151;font-weight:500;">{{ $v }}</span>
                </div>
                @endforeach
            </div>

            {{-- Last error --}}
            @if($lastError)
            <div style="grid-column:1/-1;background:#fef2f2;border:1px solid #fca5a5;border-radius:.75rem;padding:1.25rem;">
                <h3 style="font-size:.9rem;font-weight:700;color:#991b1b;margin:0 0 .5rem;">Último erro registrado</h3>
                <p style="font-size:.85rem;color:#991b1b;margin:0;word-break:break-word;">{{ $lastError }}</p>
            </div>
            @endif

            {{-- Instructions --}}
            <div style="grid-column:1/-1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1rem;">Como configurar</h3>
                <ol style="margin:0;padding-left:1.2rem;color:#475569;font-size:.9rem;line-height:2;">
                    <li>Acesse <strong>Google AI Studio</strong> e obtenha sua API key</li>
                    <li>Adicione no .env: <code style="background:#f1f5f9;padding:.1rem .3rem;border-radius:.2rem;">GEMINI_API_KEY=sua_chave_aqui</code></li>
                    <li>Configure: <code style="background:#f1f5f9;padding:.1rem .3rem;border-radius:.2rem;">AI_PROVIDER=google</code></li>
                    <li>Habilite: <code style="background:#f1f5f9;padding:.1rem .3rem;border-radius:.2rem;">GEMINI_IMAGE_ENABLED=true</code></li>
                    <li>Configure modelo: <code style="background:#f1f5f9;padding:.1rem .3rem;border-radius:.2rem;">GEMINI_IMAGE_MODEL=imagen-3.0-generate-002</code></li>
                    <li>Rode: <code style="background:#f1f5f9;padding:.1rem .3rem;border-radius:.2rem;">php artisan optimize:clear</code></li>
                    <li>Clique em "Testar conexão Gemini" acima</li>
                </ol>
                <div style="margin-top:1rem;background:#fef9c3;border:1px solid #fde047;border-radius:.5rem;padding:.75rem;">
                    <p style="font-size:.82rem;color:#854d0e;margin:0;"><strong>Nota:</strong> Modelos Imagen (imagen-3.0, imagen-4.0) requerem plano pago no Google AI Studio. Se estiver no plano gratuito, use modelos Gemini com suporte a imagem como gemini-2.5-flash-image (quando disponível).</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
